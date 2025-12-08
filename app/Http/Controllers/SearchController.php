<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\TaskListItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;

class SearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Búsqueda semántica en notas y listas usando Groq o Deepseek
    public function semantic(Request $request)
    {
        $query = $request->input('q', '');
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $provider = $request->input('provider', 'groq');

        try {
            // Obtener notas del usuario
            $notes = Note::where('user_id', Auth::id())->get(['id', 'title', 'content', 'created_at']);
            
            // Obtener items de listas del usuario (con relación a la lista)
            $listItems = TaskListItem::whereHas('list', function ($q) {
                $q->where('user_id', Auth::id());
            })->with('list:id,title')->get(['id', 'name', 'task_list_id', 'created_at']);

            // Crear índice de documentos
            $documents = [];
            foreach ($notes as $note) {
                $documents[] = [
                    'id' => 'note-' . $note->id,
                    'type' => 'note',
                    'title' => $note->title,
                    'content' => $note->content,
                    'timestamp' => $note->created_at,
                ];
            }
            foreach ($listItems as $item) {
                $documents[] = [
                    'id' => 'item-' . $item->id,
                    'type' => 'item',
                    'title' => $item->name,
                    'list' => $item->list->title ?? 'Sin lista',
                    'content' => $item->name,
                    'timestamp' => $item->created_at,
                ];
            }

            if (empty($documents)) {
                return response()->json(['results' => []]);
            }

            // Llamar a IA para clasificar similitud
            $relevanceMap = $this->rankBySemanticSimilarity($query, $documents, $provider);

            // Ordenar documentos por relevancia y retornar top 10
            arsort($relevanceMap);
            $topDocs = array_slice(array_keys($relevanceMap), 0, 10);

            $results = [];
            foreach ($topDocs as $docId) {
                foreach ($documents as $doc) {
                    if ($doc['id'] === $docId) {
                        $results[] = [
                            'id' => $doc['id'],
                            'type' => $doc['type'],
                            'title' => $doc['title'],
                            'preview' => substr($doc['content'], 0, 100) . (strlen($doc['content']) > 100 ? '...' : ''),
                            'list' => $doc['list'] ?? null,
                        ];
                        break;
                    }
                }
            }

            return response()->json(['results' => $results, 'provider' => $provider]);
        } catch (\Exception $e) {
            \Log::error('[SearchController] Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error en búsqueda: ' . $e->getMessage()], 500);
        }
    }

    protected function rankBySemanticSimilarity($query, $documents, $provider)
    {
        // Crear prompt para que la IA rankee los documentos
        $docList = implode("\n", array_map(function ($doc, $idx) {
            return "$idx. [{$doc['type']}] {$doc['title']} - {$doc['content']}";
        }, $documents, array_keys($documents)));

        $prompt = "Dada la búsqueda: \"$query\"\n\nRankea estos documentos por relevancia semántica (devuelve JSON con indices ordenados):\n$docList\n\nRespuesta: {\"ranked\": [indices en orden de relevancia]}";

        try {
            if ($provider === 'deepseek') {
                return $this->rankWithDeepseek($prompt, count($documents));
            } else {
                return $this->rankWithGroq($prompt, count($documents));
            }
        } catch (\Exception $e) {
            // Fallback: simple keyword matching
            return $this->simpleKeywordRanking($query, $documents);
        }
    }

    protected function rankWithGroq($prompt, $docCount)
    {
        $apiKey = config('services.groq.key');
        $client = new Client();

        $response = $client->post('https://api.groq.com/openai/v1/chat/completions', [
            'headers' => [
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'mixtral-8x7b-32768',
                'messages' => [
                    ['role' => 'system', 'content' => 'Eres un buscador semántico. Responde SIEMPRE en JSON válido con formato: {"ranked": [0, 3, 1, ...]}'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.3,
                'max_tokens' => 300,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $content = $data['choices'][0]['message']['content'] ?? '{}';
        $parsed = json_decode($content, true);
        $ranked = $parsed['ranked'] ?? range(0, $docCount - 1);

        $result = [];
        foreach ($ranked as $idx => $docIdx) {
            $result['doc-' . $docIdx] = $docCount - $idx; // puntuación descendente
        }
        return $result;
    }

    protected function rankWithDeepseek($prompt, $docCount)
    {
        $apiKey = config('services.deepseek.key');
        $client = new Client();

        $response = $client->post('https://api.deepseek.com/chat/completions', [
            'headers' => [
                'Authorization' => "Bearer $apiKey",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'deepseek-chat',
                'messages' => [
                    ['role' => 'system', 'content' => 'Eres un buscador semántico. Responde SIEMPRE en JSON válido con formato: {"ranked": [0, 3, 1, ...]}'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.3,
                'max_tokens' => 300,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $content = $data['choices'][0]['message']['content'] ?? '{}';
        $parsed = json_decode($content, true);
        $ranked = $parsed['ranked'] ?? range(0, $docCount - 1);

        $result = [];
        foreach ($ranked as $idx => $docIdx) {
            $result['doc-' . $docIdx] = $docCount - $idx;
        }
        return $result;
    }

    protected function simpleKeywordRanking($query, $documents)
    {
        // Fallback simple: buscar palabras clave
        $keywords = str_word_count(strtolower($query), 1);
        $result = [];

        foreach ($documents as $idx => $doc) {
            $text = strtolower($doc['title'] . ' ' . $doc['content']);
            $matches = 0;
            foreach ($keywords as $kw) {
                if (strpos($text, $kw) !== false) {
                    $matches++;
                }
            }
            if ($matches > 0) {
                $result['doc-' . $idx] = $matches;
            }
        }

        return $result;
    }
}

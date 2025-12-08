<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;

class TestAiProviders extends Command
{
    protected $signature = 'test:ai-providers';
    protected $description = 'Test AI providers (Groq, Deepseek, Gemini) and show errors';

    public function handle()
    {
        $this->info("=== Testing AI Providers ===\n");

        // Check API keys
        $groqKey = config('services.groq.key');
        $deepseekKey = config('services.deepseek.key');
        $geminiKey = config('services.gemini.key');

        $this->line("API Keys Status:");
        $this->line("  GROQ: " . ($groqKey ? "✓ Loaded" : "✗ NOT SET"));
        $this->line("  DEEPSEEK: " . ($deepseekKey ? "✓ Loaded" : "✗ NOT SET"));
        $this->line("  GEMINI: " . ($geminiKey ? "✓ Loaded" : "✗ NOT SET"));
        $this->newLine();

        // Test each provider
        $this->testGroq($groqKey);
        $this->testDeepseek($deepseekKey);
        $this->testGemini($geminiKey);
    }

    private function testGroq($apiKey)
    {
        $this->line("Testing Groq...");
        if (!$apiKey) {
            $this->error("  ✗ API Key not configured");
            return;
        }

        try {
            $client = new Client();
            $response = $client->post('https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer $apiKey",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'mixtral-8x7b-32768',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are helpful.'],
                        ['role' => 'user', 'content' => 'Say hello'],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 50,
                ],
                'timeout' => 10,
            ]);

            $data = json_decode($response->getBody(), true);
            $this->info("  ✓ Groq OK: " . substr($data['choices'][0]['message']['content'] ?? '', 0, 50));
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            $this->error("  ✗ Groq Error: " . substr($body, 0, 200));
        } catch (\Exception $e) {
            $this->error("  ✗ Groq Exception: " . $e->getMessage());
        }
        $this->newLine();
    }

    private function testDeepseek($apiKey)
    {
        $this->line("Testing Deepseek...");
        if (!$apiKey) {
            $this->error("  ✗ API Key not configured");
            return;
        }

        try {
            $client = new Client();
            $response = $client->post('https://api.deepseek.com/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer $apiKey",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'deepseek-chat',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are helpful.'],
                        ['role' => 'user', 'content' => 'Say hello'],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 50,
                ],
                'timeout' => 10,
            ]);

            $data = json_decode($response->getBody(), true);
            $this->info("  ✓ Deepseek OK: " . substr($data['choices'][0]['message']['content'] ?? '', 0, 50));
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            $this->error("  ✗ Deepseek Error: " . substr($body, 0, 200));
        } catch (\Exception $e) {
            $this->error("  ✗ Deepseek Exception: " . $e->getMessage());
        }
        $this->newLine();
    }

    private function testGemini($apiKey)
    {
        $this->line("Testing Gemini...");
        if (!$apiKey) {
            $this->error("  ✗ API Key not configured");
            return;
        }

        try {
            $client = new Client();
            $response = $client->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=$apiKey", [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'contents' => [
                        ['parts' => [['text' => 'Say hello in 1 word']]],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 50,
                    ],
                ],
                'timeout' => 10,
            ]);

            $data = json_decode($response->getBody(), true);
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'N/A';
            $this->info("  ✓ Gemini OK: " . substr($text, 0, 50));
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            $this->error("  ✗ Gemini Error: " . substr($body, 0, 200));
        } catch (\Exception $e) {
            $this->error("  ✗ Gemini Exception: " . $e->getMessage());
        }
        $this->newLine();
    }
}

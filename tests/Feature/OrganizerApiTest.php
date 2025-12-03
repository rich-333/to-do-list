<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganizerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_note()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/v1/notes', [
                'titulo' => 'Prueba',
                'contenido' => 'Contenido de prueba',
                'etiquetas' => ['test','sample'],
                'color' => 'blue',
            ])
            ->assertStatus(201)
            ->assertJsonFragment(['titulo' => 'Prueba']);
    }

    public function test_can_create_task_from_note()
    {
        $user = User::factory()->create();

        $note = $user->notes()->create(['titulo' => 'Nota a tarea', 'contenido' => 'Convierte', 'etiquetas' => ['todo']]);

        $this->actingAs($user)
            ->postJson("/api/v1/notes/{$note->id}/to-task")
            ->assertStatus(201)
            ->assertJsonFragment(['titulo' => 'Nota a tarea']);
    }
}

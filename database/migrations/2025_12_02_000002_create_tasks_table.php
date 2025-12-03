<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->enum('prioridad', ['baja','media','alta'])->default('media');
            $table->dateTime('fecha_limite')->nullable();
            $table->enum('estado', ['pendiente','en_progreso','completada','cancelada'])->default('pendiente');
            $table->json('etiquetas')->nullable();
            $table->dateTime('fecha_completada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

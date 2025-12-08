<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('task_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_list_id')->constrained('task_lists')->onDelete('cascade');
            $table->string('name');
            $table->boolean('completed')->default(false);
            $table->integer('order')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_list_items');
    }
};

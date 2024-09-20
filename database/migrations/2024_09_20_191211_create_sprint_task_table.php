<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sprint_task', function (Blueprint $table) {
            $table->foreignId('sprint_id')->constrained('sprints')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade')->onUpdate('cascade');

            $table->primary(['sprint_id', 'task_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sprint_task');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * @return void
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignUuid('task_status_id')
                ->comment('Статус задачи')
                ->after('description')
                ->nullable()
                ->constrained('task_statuses')
                ->nullOnDelete();
        });
    }


    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('task_status_id');
        });
    }
};

<?php

namespace Modules\Task\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!app()->environment(['local', 'development', 'dev'])) {
            return;
        }

        $taskTypes = [
            ['name' => 'Ремонт'],
            ['name' => 'ТО'],
            ['name' => 'Покупка запчастей'],
            ['name' => 'Покупка расходников'],
        ];

        foreach ($taskTypes as $type) {
            DB::table('task_types')->updateOrInsert(
                $type
            );
        }
    }
}
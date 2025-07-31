<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SqlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sqlDirectory = database_path('seeders/sql');
        $maxChunkSize = 10;

        // Optionally disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Get all .sql files and run them
        foreach (File::files($sqlDirectory) as $file) {
            if ($file->getExtension() === 'sql') {
                $this->command->info("Processing {$file->getFilename()}");

                $sqlContent = file_get_contents($file->getRealPath());

                // Split SQL by semicolon + optional line breaks
                $statements = preg_split('/;\s*\n/', $sqlContent);

                $batch = [];
                $counter = 0;

                foreach ($statements as $statement) {
                    $statement = trim($statement);

                    if ($statement !== '') {
                        $batch[] = $statement;
                        $counter++;
                    }

                    // Execute batch
                    if ($counter >= $maxChunkSize) {
                        DB::unprepared(implode(";\n", $batch) . ';');
                        $batch = [];
                        $counter = 0;
                    }
                }

                // Execute leftover batch
                if (!empty($batch)) {
                    DB::unprepared(implode(";\n", $batch) . ';');
                }

                $this->command->info("Completed seeding {$file->getFilename()}");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    
    }
}

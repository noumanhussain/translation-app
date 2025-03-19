<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TranslationSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting seeding process...');

        // Disable foreign key checks
        Schema::disableForeignKeyConstraints();

        // Clean existing data
        $this->command->info('Cleaning existing data...');
        DB::table('tag_translation')->truncate();
        DB::table('translations')->truncate();
        DB::table('languages')->truncate();
        DB::table('tags')->truncate();

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();

        // Create languages
        $this->command->info('Creating languages...');
        Language::factory(15)->create();

        // Create tags
        $this->command->info('Creating tags...');
        $tags = Tag::factory(15)->create();

        // Create translations in chunks
        $totalTranslations = 100000; // 100k translations
        $chunkSize = 1000;
        $chunks = $totalTranslations / $chunkSize;

        $this->command->info("Creating {$totalTranslations} translations in {$chunks} chunks...");
        $bar = $this->command->getOutput()->createProgressBar($chunks);

        for ($i = 0; $i < $totalTranslations; $i += $chunkSize) {
            DB::transaction(function () use ($chunkSize, $tags) {
                // Create translations
                $translations = Translation::factory()
                    ->count($chunkSize)
                    ->create();

                // Attach random tags to translations
                foreach ($translations as $translation) {
                    // Attach 1-3 random tags to each translation
                    $randomTags = $tags->random(rand(1, 3))->pluck('id')->toArray();
                    $translation->tags()->attach($randomTags);
                }
            });

            // Clear memory
            if ($i % 5000 === 0) {
                gc_collect_cycles();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->info("\nSeeding completed successfully!");
    }
}

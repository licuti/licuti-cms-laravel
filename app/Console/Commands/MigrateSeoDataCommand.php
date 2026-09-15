<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateSeoDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:migrate-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing SEO metadata from translation tables to the new seo_metadata table.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting SEO data migration...');

        // 1. Migrate Post Translations
        if (DB::getSchemaBuilder()->hasTable('post_translations')) {
            $this->info('Migrating Post translations...');
            DB::statement("
                INSERT IGNORE INTO seo_metadata (seoable_type, seoable_id, locale, meta_title, meta_description, meta_keywords, created_at, updated_at)
                SELECT 'App\\\\Models\\\\Post', post_id, locale, meta_title, meta_description, meta_keywords, created_at, updated_at 
                FROM post_translations
                WHERE meta_title IS NOT NULL OR meta_description IS NOT NULL OR meta_keywords IS NOT NULL
            ");
            $this->info('Post SEO data migrated.');
        }

        // 2. Migrate Category Translations
        if (DB::getSchemaBuilder()->hasTable('category_translations')) {
            $this->info('Migrating Category translations...');
            DB::statement("
                INSERT IGNORE INTO seo_metadata (seoable_type, seoable_id, locale, meta_title, meta_description, meta_keywords, created_at, updated_at)
                SELECT 'App\\\\Models\\\\Category', category_id, locale, meta_title, meta_description, meta_keywords, created_at, updated_at 
                FROM category_translations
                WHERE meta_title IS NOT NULL OR meta_description IS NOT NULL OR meta_keywords IS NOT NULL
            ");
            $this->info('Category SEO data migrated.');
        }

        // 3. Migrate Post Category Translations
        if (DB::getSchemaBuilder()->hasTable('post_category_translations')) {
            $this->info('Migrating Post Category translations...');
            DB::statement("
                INSERT IGNORE INTO seo_metadata (seoable_type, seoable_id, locale, meta_title, meta_description, meta_keywords, created_at, updated_at)
                SELECT 'App\\\\Models\\\\PostCategory', post_category_id, locale, meta_title, meta_description, meta_keywords, created_at, updated_at 
                FROM post_category_translations
                WHERE meta_title IS NOT NULL OR meta_description IS NOT NULL OR meta_keywords IS NOT NULL
            ");
            $this->info('Post Category SEO data migrated.');
        }

        $this->info('SEO data migration completed successfully!');
        $this->warn('NOTE: After verifying the data, you can drop the meta_title, meta_description, and meta_keywords columns from the old translation tables.');
    }
}

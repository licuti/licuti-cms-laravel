<?php

namespace Tests\Feature\Admin;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugCategoryFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_create_form(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Language::create([
            'code' => 'vi', 'name' => 'Tiếng Việt', 'native_name' => 'Tiếng Việt',
            'is_default' => true, 'is_active' => true,
        ]);

        $langs = app(\App\Repositories\Interfaces\LanguageRepositoryInterface::class)->getActiveLanguages();
        $raw = \Illuminate\Support\Facades\DB::table('languages')->get();
        fwrite(STDERR, "\nRAW ROWS: ".$raw->count()." | ".json_encode($raw->pluck('code'))."\n");
        $activeRaw = \Illuminate\Support\Facades\DB::table('languages')->where('is_active', 1)->get();
        fwrite(STDERR, "RAW ACTIVE: ".$activeRaw->count()."\n");
        fwrite(STDERR, "LANGS COUNT: ".$langs->count()." codes=".$langs->pluck('code')->implode(',')."\n");

        $response = $this->actingAs($admin)->get(route('admin.categories.create'));
        fwrite(STDERR, "STATUS: ".$response->status()."\n");
        file_put_contents(storage_path('e2e/dbg_cat_create.html'), $response->content());
        $html = $response->content();
        fwrite(STDERR, "has translations[vi][name]: ".(strpos($html, 'translations[vi][name]') !== false ? 'YES' : 'NO')."\n");
        fwrite(STDERR, "has translations[en][name]: ".(strpos($html, 'translations[en][name]') !== false ? 'YES' : 'NO')."\n");
        fwrite(STDERR, "locale: ".app()->getLocale()."\n");
        $this->assertTrue(true);
    }
}

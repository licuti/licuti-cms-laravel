<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_attribute_value_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_value_id')->constrained('product_attribute_values')->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('value'); // The translated value
            $table->timestamps();

            $table->unique(['attribute_value_id', 'locale'], 'pat_val_trans_unique');
        });

        // Migrate existing data
        $values = DB::table('product_attribute_values')->get();
        $defaultLocale = config('app.fallback_locale', 'vi');
        
        foreach ($values as $val) {
            if (!empty($val->value)) {
                DB::table('product_attribute_value_translations')->insert([
                    'attribute_value_id' => $val->id,
                    'locale' => $defaultLocale,
                    'value' => $val->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Drop the `value` column from `product_attribute_values`
        Schema::table('product_attribute_values', function (Blueprint $table) {
            $table->dropColumn('value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_attribute_values', function (Blueprint $table) {
            $table->string('value')->nullable();
        });

        $translations = DB::table('product_attribute_value_translations')
            ->where('locale', config('app.fallback_locale', 'vi'))
            ->get();
            
        foreach ($translations as $trans) {
            DB::table('product_attribute_values')
                ->where('id', $trans->attribute_value_id)
                ->update(['value' => $trans->value]);
        }

        Schema::dropIfExists('product_attribute_value_translations');
    }
};

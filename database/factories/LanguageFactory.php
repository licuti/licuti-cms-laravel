<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Language>
 */
class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        return [
            'code'          => $this->faker->unique()->languageCode(),
            'name'          => $this->faker->name(),
            'native_name'   => $this->faker->name(),
            'flag'          => null,
            'is_default'    => false,
            'is_active'     => true,
            'display_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function vietnamese(): static
    {
        return $this->state(fn () => [
            'code'        => 'vi',
            'name'        => 'Vietnamese',
            'native_name' => 'Tiếng Việt',
            'is_default'  => true,
        ]);
    }

    public function english(): static
    {
        return $this->state(fn () => [
            'code'        => 'en',
            'name'        => 'English',
            'native_name' => 'English',
        ]);
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }
}
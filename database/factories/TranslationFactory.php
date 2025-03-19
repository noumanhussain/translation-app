<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translation>
 */
class TranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Translation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $baseKeys = [
            'welcome',
            'login',
            'signup',
            'forgot_password',
            'reset_password',
            'profile',
            'settings',
            'logout',
            'search',
            'submit',
            'cancel',
            'save',
            'delete',
            'edit',
            'create',
            'error_404',
            'error_500',
            'success_message',
            'error_message',
            'loading'
        ];

        static $sections = [
            'header',
            'footer',
            'sidebar',
            'menu',
            'dashboard',
            'form',
            'modal',
            'button',
            'alert',
            'notification',
            'page',
            'panel'
        ];

        static $groups = ['frontend', 'backend', 'emails', 'errors', 'notifications'];

        // Generate a more unique key by combining section and base key
        $section = $this->faker->randomElement($sections);
        $baseKey = $this->faker->randomElement($baseKeys);
        $key = $section . '.' . $baseKey . '.' . $this->faker->numberBetween(1, 1000);

        return [
            'key' => $key,
            'value' => $this->faker->realText(100), // Generate more realistic text
            'language_id' => Language::inRandomOrder()->first()->id ?? Language::factory(),
            'group' => $this->faker->randomElement($groups)
        ];
    }
}

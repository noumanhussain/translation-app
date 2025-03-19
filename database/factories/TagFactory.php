<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tag::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Common context tags for applications
        static $contextTags = [
            'mobile' => 'Mobile application content',
            'web' => 'Web application content',
            'desktop' => 'Desktop application content',
            'admin' => 'Administrative interface',
            'user' => 'User interface',
            'error' => 'Error messages',
            'success' => 'Success messages',
            'email' => 'Email templates',
            'notification' => 'Notification messages',
            'help' => 'Help and documentation',
            'marketing' => 'Marketing content',
            'legal' => 'Legal content',
            'checkout' => 'Checkout process',
            'onboarding' => 'User onboarding',
            'settings' => 'Application settings'
        ];

        // Get a random tag name and description
        $name = $this->faker->unique()->randomElement(array_keys($contextTags));
        $description = $contextTags[$name];

        return [
            'name' => $name,
            'description' => $description
        ];
    }
}

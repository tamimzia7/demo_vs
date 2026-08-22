<?php

namespace Database\Factories;

use App\Models\Offering;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offering>
 */
class OfferingFactory extends Factory
{
    protected $model = Offering::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'off' => sprintf('OFF-%s-%06d', $this->faker->year(), $this->faker->unique()->numberBetween(1, 999999)),
            'name' => $this->faker->words(3, true),
            'metadata' => null,
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}

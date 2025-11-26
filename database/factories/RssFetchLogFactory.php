<?php

namespace Database\Factories;

use App\Models\RssFetchLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RssFetchLog>
 */
class RssFetchLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RssFetchLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement([RssFetchLog::STATUS_SUCCESS, RssFetchLog::STATUS_FAILURE]);

        return [
            'fetched_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'status' => $status,
            'articles_count' => $status === RssFetchLog::STATUS_SUCCESS ? fake()->numberBetween(0, 50) : 0,
            'error_message' => $status === RssFetchLog::STATUS_FAILURE ? fake()->sentence() : null,
        ];
    }

    /**
     * 成功ログの状態
     */
    public function success(int $count = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RssFetchLog::STATUS_SUCCESS,
            'articles_count' => $count ?? fake()->numberBetween(1, 50),
            'error_message' => null,
        ]);
    }

    /**
     * 失敗ログの状態
     */
    public function failure(string $errorMessage = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RssFetchLog::STATUS_FAILURE,
            'articles_count' => 0,
            'error_message' => $errorMessage ?? fake()->sentence(),
        ]);
    }
}


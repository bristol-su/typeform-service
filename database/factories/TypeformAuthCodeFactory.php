<?php

namespace Database\TypeformService\Factories;

use BristolSU\Service\Typeform\Models\TypeformAuthCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class TypeformAuthCodeFactory extends Factory
{

    protected $model = TypeformAuthCode::class;

    public function definition()
    {
        return [
            'user_id' => fn() => \BristolSU\ControlDB\Models\User::factory()->create()->id(),
            'auth_code' => \Illuminate\Support\Str::random(15),
            'refresh_token' => \Illuminate\Support\Str::random(15),
            'expires_at' => $this->faker->dateTimeBetween('+1 minute', '+1 day')
        ];
    }
}

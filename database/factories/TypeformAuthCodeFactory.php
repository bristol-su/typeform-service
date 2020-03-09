<?php

$factory->define(\BristolSU\Service\Typeform\Models\TypeformAuthCode::class, function(\Faker\Generator $faker) {
    return [
        'user_id' => function() {
            return factory(\BristolSU\ControlDB\Models\User::class)->create()->id();
        },
        'auth_code' => \Illuminate\Support\Str::random(15),
        'refresh_token' => \Illuminate\Support\Str::random(15),
        'expires_at' => $faker->dateTimeBetween('+1 minute', '+1 day')
    ];
});
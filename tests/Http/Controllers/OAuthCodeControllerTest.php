<?php

namespace BristolSU\Service\Tests\Typeform\Http\Controllers;

use BristolSU\ControlDB\Models\User;
use BristolSU\Service\Tests\Typeform\TestCase;
use BristolSU\Service\Typeform\Models\TypeformAuthCode;
use BristolSU\Support\Testing\HandlesAuthentication;
use BristolSU\Support\Testing\HandlesAuthorization;
use Carbon\Carbon;

class OAuthCodeControllerTest extends TestCase
{
    use HandlesAuthentication;

    /** @test */
    public function it_returns_all_typeform_codes_from_the_last_10_minutes(){
        $user = User::factory()->create();
        $this->beUser($user);

        $valid = collect([
            TypeformAuthCode::factory()->create(['user_id' => $user->id(), 'created_at' => Carbon::now()->subMinutes(9)->subSeconds(55)]),
            TypeformAuthCode::factory()->create(['user_id' => $user->id(), 'created_at' => Carbon::now()->subMinute()]),
            TypeformAuthCode::factory()->create(['user_id' => $user->id(), 'created_at' => Carbon::now()])
        ]);
        $invalid = collect([
            TypeformAuthCode::factory()->create(['created_at' => Carbon::now()->subDay()]),
            TypeformAuthCode::factory()->create(['user_id' => $user->id(), 'created_at' => Carbon::now()->subMinutes(10)->subSeconds(2)]),
            TypeformAuthCode::factory()->create(['user_id' => $user->id(), 'created_at' => Carbon::now()->subMinutes(11)])
        ]);

        $response = $this->get('/api/_connector/typeform/code');

        $response->assertJsonCount(3);

        foreach($valid->sortByDesc('created_at') as $code) {
            $response->assertJsonFragment([
                'id' => $code->id,
                'user_id' => (string) $user->id,
                'updated_at' => $code->updated_at->format('Y-m-d H:i:s')
            ]);
        }
    }

}

<?php

namespace BristolSU\Service\Tests\Typeform\Models;

use BristolSU\ControlDB\Models\User;
use BristolSU\Service\Tests\Typeform\TestCase;
use BristolSU\Service\Typeform\Models\TypeformAuthCode;
use BristolSU\Support\Testing\HandlesAuthentication;
use BristolSU\Support\Testing\HandlesAuthorization;
use Carbon\Carbon;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class TypeformAuthCodeTest extends TestCase
{
    use HandlesAuthentication;

    /** @test */
    public function a_model_can_be_created(){
        $user = User::factory()->create();
        $expiresAt = Carbon::now()->addDay();

        $this->bypassEncryption();

        $authCode = TypeformAuthCode::factory()->create([
            'user_id' => $user->id(),
            'auth_code' => 'abcdefghijklmnop',
            'refresh_token' => '1234567890',
            'expires_at' => $expiresAt
        ]);

        $this->assertDatabaseHas('typeform_auth_codes', [
            'id' => $authCode->id,
            'user_id' => $user->id(),
            'auth_code' => 'abcdefghijklmnop',
            'refresh_token' => '1234567890',
            'expires_at' => $expiresAt->format('Y-m-d H:i:s')
        ]);
    }

    /** @test */
    public function the_auth_code_refresh_token_and_expires_at_are_all_hidden(){
        $authCode = TypeformAuthCode::factory()->create();

        $attributes = $authCode->toArray();

        $this->assertArrayNotHasKey('auth_code', $attributes);
        $this->assertArrayNotHasKey('refresh_token', $attributes);
        $this->assertArrayNotHasKey('expires_at', $attributes);
    }

    /** @test */
    public function it_encrypts_and_decrypts_an_auth_code_and_refresh_token(){
        $encrypter = $this->prophesize(Encrypter::class);
        $encrypter->encrypt('AuthCode1')->shouldBeCalled()->willReturn('AuthCode123');
        $encrypter->decrypt('AuthCode123')->shouldBeCalled()->willReturn('AuthCode1');
        $encrypter->encrypt('RefreshToken1')->shouldBeCalled()->willReturn('RefreshToken123');
        $encrypter->decrypt('RefreshToken123')->shouldBeCalled()->willReturn('RefreshToken1');
        Crypt::swap($encrypter->reveal());

        $authCode = TypeformAuthCode::factory()->create(['auth_code' => 'AuthCode1', 'refresh_token' => 'RefreshToken1']);
        $this->assertDatabaseHas('typeform_auth_codes', [
            'auth_code' => 'AuthCode123',
            'id' => $authCode->id,
            'refresh_token' => 'RefreshToken123'
        ]);

        $this->assertEquals('AuthCode1', $authCode->auth_code);
        $this->assertEquals('RefreshToken1', $authCode->refresh_token);
    }

    /** @test */
    public function isValid_returns_true_if_expires_at_is_in_the_future(){
        $authCode = TypeformAuthCode::factory()->create([
            'expires_at' => Carbon::now()->addDay()
        ]);

        $this->assertTrue($authCode->isValid());
    }

    /** @test */
    public function isValid_returns_true_if_expires_at_is_in_the_past(){
        $authCode = TypeformAuthCode::factory()->create([
            'expires_at' => Carbon::now()->subDay()
        ]);

        $this->assertFalse($authCode->isValid());
    }

    /** @test */
    public function scopeValid_only_returns_auth_codes_which_have_been_created_in_the_last_10_minutes_and_belong_to_the_user(){

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

        $codes = TypeformAuthCode::valid()->get();
        $this->assertCount(3, $codes);
        $this->assertContainsOnlyInstancesOf(TypeformAuthCode::class, $codes);

        foreach($valid->sortByDesc('created_at') as $code) {
            $this->assertModelEquals($code, $codes->shift());
        }
    }


}

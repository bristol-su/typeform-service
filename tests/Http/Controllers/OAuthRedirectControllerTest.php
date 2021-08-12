<?php

namespace BristolSU\Service\Tests\Typeform\Http\Controllers;

use BristolSU\Service\Tests\Typeform\TestCase;
use BristolSU\Support\Testing\HandlesAuthentication;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Config\Repository;

class OAuthRedirectControllerTest extends TestCase
{
    use HandlesAuthentication;

    /** @test */
    public function it_makes_a_post_request_to_typeform(){
        $this->bypassEncryption();

        $user = $this->newUser();
        $this->beUser($user);
        app(Repository::class)->set('typeform_service.urlAccessToken', 'https://api.typeform.com/authorize_access_token_test');
        app(Repository::class)->set('typeform_service.client_id', 'MyClientId');
        app(Repository::class)->set('typeform_service.client_secret', 'MyClientSecret');

        $client = $this->prophesize(Client::class);
        $client->post('https://api.typeform.com/authorize_access_token_test', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => 'some_input_code',
                'client_id' => 'MyClientId',
                'client_secret' => 'MyClientSecret',
                'redirect_uri' => 'http://localhost/_connector/typeform/redirect'
            ]]
        )->shouldBeCalled()->willReturn(new Response(200, [], json_encode([
            'access_token' => 'AccessToken',
            'refresh_token' => 'RefreshToken',
            'expires_in' => 1000
        ])));
        $this->instance(Client::class, $client->reveal());

        $response = $this->get('_connector/typeform/redirect?code=some_input_code');
    }

    /** @test */
    public function it_creates_a_new_auth_code_row(){
        $this->bypassEncryption();

        $user = $this->newUser();
        $this->beUser($user);
        app(Repository::class)->set('typeform_service.urlAccessToken', 'https://api.typeform.com/authorize_access_token_test');
        app(Repository::class)->set('typeform_service.client_id', 'MyClientId');
        app(Repository::class)->set('typeform_service.client_secret', 'MyClientSecret');
        $now = Carbon::now();
        Carbon::setTestNow($now);
        $client = $this->prophesize(Client::class);
        $client->post('https://api.typeform.com/authorize_access_token_test', [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'code' => 'some_input_code',
                    'client_id' => 'MyClientId',
                    'client_secret' => 'MyClientSecret',
                    'redirect_uri' => 'http://localhost/_connector/typeform/redirect'
                ]]
        )->shouldBeCalled()->willReturn(new Response(200, [], json_encode([
            'access_token' => 'AccessToken',
            'refresh_token' => 'RefreshToken',
            'expires_in' => 1000
        ])));
        $this->instance(Client::class, $client->reveal());

        $response = $this->get('_connector/typeform/redirect?code=some_input_code');

        $this->assertDatabaseHas('typeform_auth_codes', [
            'auth_code' => 'AccessToken',
            'refresh_token' => 'RefreshToken',
            'expires_at' => $now->addSeconds(1000)->format('Y-m-d H:i:s')
        ]);

    }

    /** @test */
    public function a_view_is_returned(){
        $user = $this->newUser();
        $this->beUser($user);
        
        app(Repository::class)->set('typeform_service.urlAccessToken', 'https://api.typeform.com/authorize_access_token_test');
        app(Repository::class)->set('typeform_service.client_id', 'MyClientId');
        app(Repository::class)->set('typeform_service.client_secret', 'MyClientSecret');

        $client = $this->prophesize(Client::class);
        $client->post('https://api.typeform.com/authorize_access_token_test', [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'code' => 'some_input_code',
                    'client_id' => 'MyClientId',
                    'client_secret' => 'MyClientSecret',
                    'redirect_uri' => 'http://localhost/_connector/typeform/redirect'
                ]]
        )->shouldBeCalled()->willReturn(new Response(200, [], json_encode([
            'access_token' => 'AccessToken',
            'refresh_token' => 'RefreshToken',
            'expires_in' => 1000
        ])));
        $this->instance(Client::class, $client->reveal());

        $response = $this->get('_connector/typeform/redirect?code=some_input_code');
        $response->assertViewIs('typeformservice::close_window');
    }

}

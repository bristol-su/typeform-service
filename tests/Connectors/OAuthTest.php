<?php

namespace BristolSU\Service\Tests\Typeform\Connectors;

use BristolSU\Service\Tests\Typeform\TestCase;
use BristolSU\Service\Typeform\Connectors\OAuth;
use BristolSU\Service\Typeform\Models\TypeformAuthCode;
use BristolSU\Support\Connection\Contracts\Client\Client;
use Carbon\Carbon;
use FormSchema\Schema\Form;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Config\Repository;
use Prophecy\Argument;

class OAuthTest extends TestCase
{

    /** @test */
    public function settingsSchema_returns_a_form_schema(){
        $this->assertInstanceOf(Form::class, OAuth::settingsSchema());
    }

    /** @test */
    public function request_adds_the_correct_authentication_options_if_headers_already_given(){
        $client = $this->prophesize(Client::class);
        $method = 'GET';
        $uri = '/tests';
        $options = ['headers' => ['test' => 'abc']];
        app(Repository::class)->set('typeform_service.base_uri', 'https://example.com');

        $client->request($method, $uri, [
            'headers' => ['test' => 'abc', 'Authorization' => 'Bearer abc123'], 'base_uri' => 'https://example.com'
        ])->shouldBeCalled();

        $authCode = TypeformAuthCode::factory()->create([
            'auth_code' => 'abc123',
            'expires_at' => Carbon::now()->addDay()
        ]);

        $oAuth = new OAuth($client->reveal());
        $oAuth->setSettings([
            'auth_code_id' => $authCode->id
        ]);
        $oAuth->request($method, $uri, $options);
    }

    /** @test */
    public function request_adds_the_correct_authentication_options_if_headers_not_already_given(){
        $client = $this->prophesize(Client::class);
        $method = 'GET';
        $uri = '/tests';
        app(Repository::class)->set('typeform_service.base_uri', 'https://example.com');

        $client->request($method, $uri, [
            'headers' => ['Authorization' => 'Bearer abc123'], 'base_uri' => 'https://example.com'
        ])->shouldBeCalled();

        $authCode = TypeformAuthCode::factory()->create([
            'auth_code' => 'abc123',
            'expires_at' => Carbon::now()->addDay()
        ]);

        $oAuth = new OAuth($client->reveal());
        $oAuth->setSettings([
            'auth_code_id' => $authCode->id
        ]);
        $oAuth->request($method, $uri, []);
    }

    /** @test */
    public function request_refreshes_the_auth_code_if_expired(){
        $this->bypassEncryption();

        $client = $this->prophesize(Client::class);
        $method = 'GET';
        $uri = '/tests';
        app(Repository::class)->set('typeform_service.base_uri', 'https://example.com');
        app(Repository::class)->set('typeform_service.urlAccessToken', 'https://test.com/bristol');
        app(Repository::class)->set('typeform_service.client_id', 'TypeformClientId');
        app(Repository::class)->set('typeform_service.client_secret', 'TypeformClientSecret');

        $now = Carbon::now();
        Carbon::setTestNow($now);

        $authCode = TypeformAuthCode::factory()->create([
            'auth_code' => 'abc123',
            'refresh_token' => '123abcd',
            'expires_at' => Carbon::now()->subDay()
        ]);

        $client->request($method, $uri, [
            'headers' => ['Authorization' => 'Bearer newAccessToken'], 'base_uri' => 'https://example.com'
        ])->shouldBeCalled();
        $client->request('post', 'https://test.com/bristol', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => '123abcd',
                'client_id' => 'TypeformClientId',
                'client_secret' => 'TypeformClientSecret',
                'scope' => 'offline accounts:read responses:read webhooks:read webhooks:write forms:read'
            ]
        ])->shouldBeCalled()->willReturn(new Response(200, [], json_encode([
            'access_token' => 'newAccessToken', 'refresh_token' => 'newRefreshToken', 'expires_in' => 1000
        ])));



        $oAuth = new OAuth($client->reveal());
        $oAuth->setSettings([
            'auth_code_id' => $authCode->id
        ]);
        $oAuth->request($method, $uri, []);

        $this->assertDatabaseHas('typeform_auth_codes', [
            'id' => $authCode->id,
            'auth_code' => 'newAccessToken',
            'refresh_token' => 'newRefreshToken',
            'expires_at' => $now->addSeconds(1000)->format('Y-m-d H:i:s')
        ]);
    }

    /** @test */
    public function request_throws_an_error_if_token_cannot_be_refreshed(){
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Access token could not be refreshed');
        $client = $this->prophesize(Client::class);
        $method = 'GET';
        $uri = '/tests';
        app(Repository::class)->set('typeform_service.base_uri', 'https://example.com');
        app(Repository::class)->set('typeform_service.urlAccessToken', 'https://test.com/bristol');
        app(Repository::class)->set('typeform_service.client_id', 'TypeformClientId');
        app(Repository::class)->set('typeform_service.client_secret', 'TypeformClientSecret');

        $now = Carbon::now();
        Carbon::setTestNow($now);

        $authCode = TypeformAuthCode::factory()->create([
            'auth_code' => 'abc123',
            'refresh_token' => '123abcd',
            'expires_at' => Carbon::now()->subDay()
        ]);

        $client->request('post', 'https://test.com/bristol', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => '123abcd',
                'client_id' => 'TypeformClientId',
                'client_secret' => 'TypeformClientSecret',
                'scope' => 'offline accounts:read responses:read webhooks:read webhooks:write forms:read'
            ]
        ])->shouldBeCalled()->willThrow($this->prophesize(ClientException::class)->reveal());


        $oAuth = new OAuth($client->reveal());
        $oAuth->setSettings([
            'auth_code_id' => $authCode->id
        ]);
        $oAuth->request($method, $uri, []);
    }

    /** @test */
    public function test_makes_a_get_request_to_the_me_endpoint_and_returns_true_if_no_exception_thrown(){
        $client = $this->prophesize(Client::class);
        $client->request('get', '/me', Argument::type('array'))->shouldBeCalled()->willReturn(['user' => []]);
        $authCode = TypeformAuthCode::factory()->create([
            'expires_at' => Carbon::now()->addDay()
        ]);

        $oAuth = new OAuth($client->reveal());
        $oAuth->setSettings(['auth_code_id' => $authCode->id]);
        $this->assertTrue(
            $oAuth->test()
        );
    }

    /** @test */
    public function test_makes_a_get_request_to_the_me_endpoint_and_returns_false_if_an_exception_is_thrown(){
        $client = $this->prophesize(Client::class);
        $exception = $this->prophesize(ClientException::class);
        $client->request('get', '/me', Argument::type('array'))->shouldBeCalled()->willThrow($exception->reveal());
        $authCode = TypeformAuthCode::factory()->create([
            'expires_at' => Carbon::now()->addDay()
        ]);
        $oAuth = new OAuth($client->reveal());
        $oAuth->setSettings(['auth_code_id' => $authCode->id]);
        $this->assertFalse(
            $oAuth->test()
        );
    }

}

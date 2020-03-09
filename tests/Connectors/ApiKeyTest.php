<?php

namespace BristolSU\Service\Tests\Typeform\Connectors;

use BristolSU\Service\Tests\Typeform\TestCase;
use BristolSU\Service\Typeform\Connectors\ApiKey;
use BristolSU\Support\Connection\Contracts\Client\Client;
use FormSchema\Schema\Form;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Config\Repository;
use Prophecy\Argument;

class ApiKeyTest extends TestCase
{

    /** @test */
    public function settingsSchema_returns_a_form_schema(){
        $this->assertInstanceOf(Form::class, ApiKey::settingsSchema());
    }
    
    /** @test */
    public function request_adds_the_correct_authentication_options_if_headers_already_given(){
        $client = $this->prophesize(Client::class);
        $method = 'GET';
        $uri = '/tests';
        $options = ['headers' => ['test' => 'abc']];
        app(Repository::class)->set('typeform_service.base_uri', 'https://example.com');
        
        $client->request($method, $uri, [
            'headers' => ['test' => 'abc', 'Authorization' => 'Bearer MyApiKey1'], 'base_uri' => 'https://example.com'
        ])->shouldBeCalled();
        
        $apiKey = new ApiKey($client->reveal());
        $apiKey->setSettings([
            'api_key' => 'MyApiKey1'
        ]);
        $apiKey->request($method, $uri, $options);
    }

    /** @test */
    public function request_adds_the_correct_authentication_options_if_headers_not_already_given(){
        $client = $this->prophesize(Client::class);
        $method = 'GET';
        $uri = '/tests';
        app(Repository::class)->set('typeform_service.base_uri', 'https://example.com');

        $client->request($method, $uri, [
            'headers' => ['Authorization' => 'Bearer MyApiKey1'], 'base_uri' => 'https://example.com'
        ])->shouldBeCalled();

        $apiKey = new ApiKey($client->reveal());
        $apiKey->setSettings([
            'api_key' => 'MyApiKey1'
        ]);
        $apiKey->request($method, $uri, []);
    }
    
    /** @test */
    public function test_makes_a_get_request_to_the_me_endpoint_and_returns_true_if_no_exception_thrown(){
        $client = $this->prophesize(Client::class);
        $client->request('get', '/me', Argument::type('array'))->shouldBeCalled()->willReturn(['user' => []]);
        
        $apiKey = new ApiKey($client->reveal());
        $this->assertTrue(
            $apiKey->test()
        );
    }

    /** @test */
    public function test_makes_a_get_request_to_the_me_endpoint_and_returns_false_if_an_exception_is_thrown(){
        $client = $this->prophesize(Client::class);
        $exception = $this->prophesize(ClientException::class);
        $client->request('get', '/me', Argument::type('array'))->shouldBeCalled()->willThrow($exception->reveal());

        $apiKey = new ApiKey($client->reveal());
        $this->assertFalse(
            $apiKey->test()
        );
    }
    
}
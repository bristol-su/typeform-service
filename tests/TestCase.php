<?php

namespace BristolSU\Service\Tests\Typeform;

use BristolSU\Service\Typeform\TypeformServiceProvider;
use BristolSU\Support\Testing\AssertsEloquentModels;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use Prophecy\Argument;

class TestCase extends \BristolSU\Support\Testing\TestCase
{
    
    use AssertsEloquentModels;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->withFactories(__DIR__  . '/../database/factories');
    }

    public function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            TypeformServiceProvider::class
        ]);
    }

    public function bypassEncryption()
    {
        $encrypter = $this->prophesize(Encrypter::class);
        $encrypter->encrypt(Argument::any(), Argument::any())->will(function($arg) {
            return $arg[0];
        });
        $encrypter->decrypt(Argument::any(), Argument::any())->will(function($arg) {
            return $arg[0];
        });
        Crypt::swap($encrypter->reveal());
    }
    
}
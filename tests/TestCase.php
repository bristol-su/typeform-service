<?php

namespace BristolSU\Service\Tests\Typeform;

use BristolSU\Service\Typeform\TypeformServiceProvider;
use BristolSU\Support\Testing\AssertsEloquentModels;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class TestCase extends \BristolSU\Support\Testing\TestCase
{
    use AssertsEloquentModels, ProphecyTrait;

    public function setUp(): void
    {
        parent::setUp();
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

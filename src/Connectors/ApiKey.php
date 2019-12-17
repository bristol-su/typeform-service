<?php

namespace BristolSU\Service\Typeform\Connectors;

use BristolSU\Support\Connection\Contracts\Connector;
use FormSchema\Generator\Field;
use FormSchema\Schema\Form;
use GuzzleHttp\Exception\GuzzleException;

class ApiKey extends Connector
{

    /**
     * @inheritDoc
     */
    public function request($method, $uri, array $options = [])
    {
        $options['base_uri'] = config('typeform.api.base_uri');
        $headers = ((isset($options['headers']) && is_array($options['headers']))?$options['headers']:[]);
        $headers['Authorization'] = 'Bearer ' . $this->getSetting('api_key');
        $options['headers'] = $headers;
        return $this->client->request($method, $uri, $options);
    }

    /**
     * @inheritDoc
     */
    public function test(): bool
    {
        try {
            $this->request('get', '/me');
            return true;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    static public function settingsSchema(): Form
    {
        return \FormSchema\Generator\Form::make()->withField(
            Field::input('api_key')->inputType('text')->label('API Key')
                ->description('You should be able to find this on Typeform')->required(true)
        )->getSchema();
    }
}
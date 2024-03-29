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
        $options['base_uri'] = config('typeform_service.base_uri');
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
            Field::textInput('api_key')->setLabel('API Key')
                ->setHint('You should be able to find this on Typeform')->setRequired(true)
        )->getSchema();
    }
}

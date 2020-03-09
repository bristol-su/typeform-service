<?php

namespace BristolSU\Service\Typeform\Connectors;

use BristolSU\Service\Typeform\Fields\TypeformAuthCode;
use BristolSU\Service\Typeform\Models\TypeformAuthCode as AuthModel;
use BristolSU\Support\Connection\Contracts\Connector;
use Carbon\Carbon;
use FormSchema\Generator\Field;
use FormSchema\Schema\Form;
use GuzzleHttp\Exception\GuzzleException;

class OAuth extends Connector
{

    /**
     * @inheritDoc
     */
    public function request($method, $uri, array $options = [])
    {
        $options['base_uri'] = config('typeform_service.base_uri');
        $headers = ((isset($options['headers']) && is_array($options['headers']))?$options['headers']:[]);
        $headers['Authorization'] = 'Bearer ' . $this->getAccessToken();
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
            Field::make(TypeformAuthCode::class, 'auth_code_id')->label('Log Into Typeform')->required(true)
        )->getSchema();
    }

    private function getAccessToken($refreshable = true): string
    {
        $authCode = AuthModel::findOrFail($this->getSetting('auth_code_id'));
        if($authCode->isValid()) {
            return $authCode->auth_code;
        }
        if($refreshable && $this->refreshAccessToken($authCode)) {
            return $this->getAccessToken(false);
        } else {
            // TODO Throw special error to send email to people
            throw new \Exception('Access token could not be refreshed');
        }
    }

    private function refreshAccessToken(AuthModel $authCode)
    {
        try {
            $response = $this->client->request('post', config('typeform_service.urlAccessToken'), [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $authCode->refresh_token,
                    'client_id' => config('typeform_service.client_id'),
                    'client_secret' => config('typeform_service.client_secret'),
                    'scope' => 'offline accounts:read responses:read webhooks:read webhooks:write forms:read'
                ],
            ]);
        } catch (GuzzleException $e) {
            return false;
        }
        $token = json_decode($response->getBody()->getContents(), true);
        $authCode->auth_code = $token['access_token'];
        $authCode->refresh_token = $token['refresh_token'];
        $authCode->expires_at = Carbon::now()->addSeconds($token['expires_in']);
        return $authCode->save();
    }
}

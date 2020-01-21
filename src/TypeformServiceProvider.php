<?php

namespace BristolSU\Service\Typeform;

use BristolSU\Service\Typeform\Connectors\ApiKey;
use BristolSU\Service\Typeform\Connectors\OAuth;
use BristolSU\Support\Connection\Contracts\ConnectorStore;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laracasts\Utilities\JavaScript\JavaScriptFacade;

class TypeformServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->publishes([__DIR__ . '/../config/config.php' => config_path('typeform_service.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php', 'typeform_service'
        );

        $this->publishes([__DIR__ . '/../public/services/typeform' => public_path('services/typeform')]);
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/typeformservice'),
        ], 'views');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'typeformservice');

    }

    public function boot()
    {
        $connectorStore = app(ConnectorStore::class);
        $connectorStore->register(
            'Typeform API Key', 'Connect to Typeform using an API Key',
            'typeform_api_key', 'typeform',
            ApiKey::class);

        $connectorStore->register(
            'Typeform OAuth (recommended)', 'Use the oAuth specification to connect to typeform',
            'typeform_oauth', 'typeform',
            OAuth::class);

        $this->registerGlobalScript('services/typeform/js/components.js');

        Route::prefix($this->app['config']->get('typeform_service.url_prefix'))
            ->middleware(['web', 'auth', 'verified'])
            ->namespace('BristolSU\Service\Typeform\Http\Controllers')
            ->group(__DIR__ . '/../routes/web.php');

        Route::prefix('api')->middleware(['api', 'auth', 'verified'])->group(function () {
            Route::prefix($this->app['config']->get('typeform_service.url_prefix'))
                ->namespace('BristolSU\Service\Typeform\Http\Controllers')
                ->group(__DIR__ . '/../routes/api.php');
        });
        
        JavaScriptFacade::put([
            'typeform_client_id' => config('typeform_service.client_id')
        ]);

    }

    public function registerGlobalScript($path)
    {
        View::composer('bristolsu::base', function (\Illuminate\View\View $view) use ($path) {
            $scripts = ($view->offsetExists('globalScripts') ? $view->offsetGet('globalScripts') : []);
            $scripts[] = asset($path);
            $view->with('globalScripts', $scripts);
        });
    }

}
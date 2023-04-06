<?php

namespace Githen\IflytekPhpSdk\Support\Laravel\ServiceProvider;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

/**
 * 自动注册为服务
 */
class Iflytek extends LaravelServiceProvider
{
    /**
     * 启动服务
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/config.php' => config_path('iflytek.php')]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('iflytek', function ($app) {
            $client = new \Githen\IflytekPhpSdk\Client([
                'app_id' => $app['config']->get('iflytek.app_id'),
                'api_secret' => $app['config']->get('iflytek.api_secret'),
                'api_key' => $app['config']->get('iflytek.api_key'),
            ]);
            return $client;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('iflytek');
    }

}
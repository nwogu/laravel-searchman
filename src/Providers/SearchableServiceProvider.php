<?php

namespace Nwogu\SearchMan\Providers;

use Laravel\Scout\EngineManager;
use Illuminate\Support\ServiceProvider;
use Nwogu\SearchMan\Console\MakeIndex;
use Nwogu\SearchMan\Engines\MySqlEngine;

class SearchableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeIndex::class,
            ]);
        }

        $this->publishes([
            __DIR__ .'/config/searchman.php' => config_path('searchman.php')
        ], 'config');

        resolve(EngineManager::class)->extend('mysql', function () {
            return new MySqlEngine;
        });
        
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

}
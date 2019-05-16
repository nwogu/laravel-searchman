<?php

namespace Nwogu\SearchMan\Providers;

use Laravel\Scout\Builder;
use Laravel\Scout\EngineManager;
use Nwogu\SearchMan\Console\MakeIndex;
use Illuminate\Support\ServiceProvider;
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

        Builder::macro('count', function () {
            return $this->engine()->getTotalCount(
                $this->engine()->search($this)
            );
        });

        $this->publishes([
            base_path() . '/vendor/nwogu/laravel-searchman/config/searchman.php' => config_path('searchman.php')
        ], 'searchman-config');

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
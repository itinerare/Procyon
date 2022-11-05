<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register() {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot() {
        // As these are concerned with application correctness,
        // leave them enabled all the time.
        Model::preventAccessingMissingAttributes();
        Model::preventSilentlyDiscardingAttributes();

        // Since this is a performance concern only, don't halt
        // production for violations.
        Model::preventLazyLoading(!$this->app->isProduction());

        Paginator::useBootstrapFour();
    }
}

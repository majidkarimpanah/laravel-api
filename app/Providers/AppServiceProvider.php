<?php

namespace App\Providers;

use App\Services\Admin\GetAnalyticsHeaderData;
use App\Services\AppBootstrapData;
use App\Services\Data\Contracts\NewsProviderInterface;
use App\Services\Data\News\ImdbNewsProvider;
use App\Services\UrlGenerator;
use Common\Admin\Analytics\Actions\GetAnalyticsHeaderDataAction;
use Common\Core\Bootstrap\BootstrapData;
use Common\Core\Contracts\AppUrlGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(
            BootstrapData::class,
            AppBootstrapData::class
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // bind analytics
        $this->app->bind(
            GetAnalyticsHeaderDataAction::class,
            GetAnalyticsHeaderData::class
        );

        // bind news provider
        $this->app->bind(
            NewsProviderInterface::class,
            ImdbNewsProvider::class
        );

        $this->app->bind(
            AppUrlGenerator::class,
            UrlGenerator::class
        );
    }
}

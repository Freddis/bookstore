<?php

namespace App\Providers;

use App\Jobs\ParseCatalogueXmlJob;
use App\Service\ImportService;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ImportService::class,function(){
           return new ImportService();
        });

        $this->app->bindMethod(ParseCatalogueXmlJob::class.'@handle', function ($job, $app) {
            return $job->handle($app->make(ImportService::class));
        });

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(190);

        Queue::after(function (JobProcessed $event) {
            app()->make(ImportService::class)->jobCompleted($event->job,null);
        });
        Queue::failing(function (JobFailed $event) {
            app()->make(ImportService::class)->jobCompleted($event->job,$event->exception);
        });

    }
}

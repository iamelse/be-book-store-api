<?php

namespace App\Providers;

use App\Repositories\PaymentRepository;
use App\Services\PaymentGateway\MidtransGateway;
use App\Services\PaymentGateway\XenditGateway;
use App\Services\PaymentService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService(
                $app->make(PaymentRepository::class),
                [
                    'midtrans' => $app->make(MidtransGateway::class),
                    'xendit'  => $app->make(XenditGateway::class),
                ]
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        // استخدام Pagination من Bootstrap
        Paginator::useBootstrapFive();
        
        // إعداد مسار ملف الاعتماد لـ Firebase الديناميكي الذي تم رفعه من الداشبورد
        $firebaseCredentialsPath = storage_path('app/private/firebase/firebase-credentials.json');
        if (file_exists($firebaseCredentialsPath)) {
            config(['firebase.projects.app.credentials' => $firebaseCredentialsPath]);
        }
    }
}

<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class ScrambleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // السماح بالوصول لصفحة التوثيق /docs/api في أي وقت لاختبار كافة الراوتات بسهولة
        Gate::define('viewApiDocs', function ($user = null) {
            return true;
        });

        // قصر التوثيق على راوتات الـ API فقط لضمان دقة ونظافة التوثيق
        Scramble::routes(function (Route $route) {
            return Str::startsWith($route->uri, 'api/');
        });

        // إدراج توثيق المصادقة Bearer Token حتى يتمكن المطور من تجربة الواجهات مباشرة من Swagger UI
        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer', 'JWT')
            );
        });
    }
}

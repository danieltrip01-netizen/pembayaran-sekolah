<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
{
    // Paksa public path ke lokasi public_html
    $this->app->bind('path.public', function() {
        return '/home/esck4946/public_html';
    });
}
    public function boot(): void
    {
        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID', 'id_ID.utf8');
    }
}
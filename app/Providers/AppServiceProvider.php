<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

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
        
        DB::listen(function ($query) {
            if ($query->time > 1000) {
              Log::info('SQL Query', [
                  'sql' => $query->sql,
                  'bindings' => $query->bindings,
                  'time' => $query->time,
              ]);
            $currentRoute = optional(Route::current())->uri(); // Or ->getName()
            Log::info('SQL Query', [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
                'route' => $currentRoute ?? 'N/A',
                'url' => Request::fullUrl(),
                'method' => Request::method(),
                ]);
            }
      });
    }
}

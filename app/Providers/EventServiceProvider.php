<?php

namespace App\Providers;

use Illuminate\Auth\Events\Logout;
use App\Listeners\LogSuccessfulLogout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{


    public function boot(): void
    {
        //
    }
    
    public function discoverEventsWithin(): array
    {
        return []; // Return empty array to disable auto-discovery
    }
}
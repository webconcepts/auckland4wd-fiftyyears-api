<?php

namespace App\Providers;

use App\Events\PhotoSaved;
use App\Listeners\UpdatePhotoOrder;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        PhotoSaved::class => [
            UpdatePhotoOrder::class,
        ],
    ];
}

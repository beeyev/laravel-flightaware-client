<?php


namespace Beeyev\LaravelFlightAwareClient;

use Illuminate\Support\Facades\Facade;

class FlightAwareFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'flightaware';
    }
}

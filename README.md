# Laravel FlightAware API Client

This package is for interacting with the public FlightAware Api (FlightXML 2.0).

## Installation

Install the package using Composer:

```
composer require beeyev/laravel-flightaware-client:dev-master
```

## Configuration

Publish the config file:
```
php artisan vendor:publish --provider="Beeyev\LaravelFlightAwareClient\FlightAwareServiceProvider"
```
Set your FlightAware account's `username` and `api_key` in the published config file `config/flightaware.php` 

__[Request free FlightAware FlightXML2 key ](https://flightaware.com/commercial/flightxml/key)__
## Usage

Use `FlightAware` facade to make calls to the FlightAware API. 

## Supported endpoints

Methods use the same arguments as the API reference lists. For more information about see __[the official documentation](https://flightaware.com/commercial/flightxml/explorer/)__.

- `FlightAware::aircraftType(string $aircraftType);`
- `FlightAware::airlineInfo(string $airlineCode);`
- `FlightAware::airportInfo(string $airportCode);`
- `FlightAware::allAirlines();`
- `FlightAware::allAirports();`
- `FlightAware::arrived(string $airportCode, int $howMany = null, string $filter = null, int $offset = 0);`
- `FlightAware::countAirportOperations(string $airportCode);`
- `FlightAware::countAllEnrouteAirlineOperations();`
- `FlightAware::departed(string $airportCode, int $howMany = null, string $filter = null, int $offset = 0);`
- `FlightAware::enroute(string $airportCode, int $howMany = null, string $filter = null, int $offset = 0);`
- `FlightAware::fleetArrived(string $fleet, int $howMany = null, int $offset = 0);`
- `FlightAware::fleetScheduled(string $fleet, int $howMany = null, int $offset = 0);`
- `FlightAware::flightInfo(string $ident, int $howMany = null);`
- `FlightAware::getLastTrack(string $ident);`
- `FlightAware::inFlightInfo(string $ident);`
- `FlightAware::latLongsToDistance(float $lat1, float $lon1, float $lat2, float $lon2);`
- `FlightAware::latLongsToHeading(float $lat1, float $lon1, float $lat2, float $lon2);`
- `FlightAware::metarEx(string $airportCode, int $howMany = null, string $filter = null, int $offset = 0);`
- `FlightAware::scheduled(string $airportCode, int $howMany = null, string $filter = null, int $offset = 0);`

## Note

You need a registered FlightAware account to access the Api.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

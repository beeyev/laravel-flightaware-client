<?php


namespace Beeyev\LaravelFlightAwareClient;

use Beeyev\LaravelFlightAwareClient\Exceptions\FlightAwareClientException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;


class FlightAware
{

    protected const BASE_URI = 'http://flightxml.flightaware.com/json/FlightXML2/';

    protected $options;

    protected $client;

    /**
     * Instantiate the FlightAware with its config.
     *
     * @param array $config The client config array
     */
    public function __construct(array $config = [])
    {
        $this->options = $config;
    }

    /**
     * Given an aircraft type string such as GALX, AircraftType returns information about that type, comprising the manufacturer (for instance, "IAI"), type (for instance, "Gulfstream G200"), and description (like "twin-jet").
     *
     * @param  string $aircraftType Aircraft type ID (Example: GALX)
     *
     * Example of a returned array:
     * @return array [
     *      'manufacturer'  => 'IAI',
     *      'type'          => 'Gulfstream G200',
     *      'description'   => 'twin-jet',
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_AircraftType
     */
    public function aircraftType(string $aircraftType): array
    {
        $queryParams['type'] = $aircraftType;
        return $this->makeRequest('AircraftType', $queryParams);
    }

    /**
     * AirlineInfo returns information about a commercial airline/carrier given an ICAO airline code.
     *
     * @param  string $airlineCode the ICAO airline ID (Example: ASA)
     *
     * Example of a returned array:
     * @return array [
     *      'name'  => 'Aeroflot - Russian International Airlines',
     *      'shortname' => 'Aeroflot',
     *      'callsign'  => 'Aeroflot',
     *      'location'  => 'Russian Federation',
     *      'country'   => 'Russian Federation',
     *      'url'       => 'http://www.aeroflot.ru/',
     *      'phone'     => '+1-888-340-6400',
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_AirlineInfo
     */
    public function airlineInfo(string $airlineCode): array
    {
        $queryParams['airlineCode'] = $airlineCode;
        return $this->makeRequest('AirlineInfo', $queryParams);
    }


    /**
     * AirportInfo returns information about an airport given an ICAO airport code such as KLAX, KSFO, KORD, KIAH, O07, etc.
     *
     * @param  string $airportCode the ICAO airport ID (Example: KLAX)
     *
     * Example of a returned array:
     * @return array [
     *      'name'      => 'Groningen Eelde',
     *      'location'  => 'Eelde',
     *      'longitude' => 6.579444,
     *      'latitude'  => 53.11972,
     *      'timezone'  => ':Europe/Amsterdam',
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_AirportInfo
     */
    public function airportInfo(string $airportCode): array
    {
        $queryParams['airportCode'] = $airportCode;
        return $this->makeRequest('AirportInfo', $queryParams);
    }

    /**
     * AllAirlines returns the ICAO identifiers of all known commercial airlines/carriers.
     *
     * Example of a returned array:
     * @return array [
     *      0 => "AAA",
     *      1 => "AAB",
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_AllAirlines
     */
    public function allAirlines(): array
    {
        return $this->makeRequest('AllAirlines')['data'];
    }

    /**
     * AllAirports returns the ICAO identifiers of all known airports. For airports that do not have an ICAO identifier, the FAA LID identifier will be used.
     *
     * Example of a returned array:
     * @return array [
     *      0 => "KTDO",
     *      1 => "59NY",
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_AllAirports
     */
    public function allAirports(): array
    {
        return $this->makeRequest('AllAirports')['data'];
    }

    /**
     * Arrived returns information about flights that have recently arrived for the specified airport and maximum number of flights to be returned.
     * Flights are returned from most to least recent. Only flights that arrived within the last 24 hours are considered.
     *
     * @param  string $airportCode the ICAO airport ID (Example: KLAX)
     * @param  int|null $howMany determines the number of results. Must be a positive integer value less than or equal to 15, unless maximum number will be set.
     * @param  string|null $filter ga|airline|null "ga" to show only general aviation traffic, "airline" to only show airline traffic, or null/empty to show all traffic.
     * @param  int|null $offset must be an integer value of the offset row count you want the search to start at. Most requests should be 0.
     *
     * Example of a returned array:
     * @return array [
     *      'next_offset' => 15,
     *      'arrivals'  => [
     *          0 => [
     *              'ident'                 => 'UAL2779',
     *              'aircrafttype'          => 'B77W',
     *              'actualdeparturetime'   => 1593247775,
     *              'actualarrivaltime'     => 1593286320,
     *              'origin'                => 'EDDF',
     *              'destination'           => 'KSFO',
     *              'originName'            => 'Frankfurt Intl',
     *              'originCity'            => 'Frankfurt am Main',
     *              'destinationName'       => 'San Francisco Intl',
     *              'destinationCity'       => 'San Francisco, CA',
     *          ]
     *      ]
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_Arrived
     */
    public function arrived(string $airportCode, int $howMany = null, string $filter = null, int $offset = 0): array
    {
        $queryParams = [
            'airport' => $airportCode,
            'howMany' => $howMany,
            'filter'  => $filter,
            'offset'  => $offset,
        ];
        return $this->makeRequest('Arrived', $queryParams);
    }


    /**
     * BlockIdentCheck Given an aircraft identification, returns 1 if the aircraft is blocked from public tracking, 0 if it is not.
     *
     * @param  string $ident requested tail number (Example: UAL1111)
     *
     * Example of a returned array:
     * @return bool
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_BlockIdentCheck
     */
    public function blockIdentCheck(string $ident): bool
    {
        $queryParams['ident'] = $ident;
        return (bool) $this->makeRequest('BlockIdentCheck', $queryParams);
    }

    /**
     * Given an airport, CountAirportOperations returns integer values on the number of aircraft scheduled or actually en route or departing from the airport.
     * Scheduled arrival is a non-airborne flight that is scheduled to the airport in question.
     *
     * @param  string $airportCode the ICAO airport ID (Example: KLAX)
     *
     * Example of a returned array:
     * @return array [
     *      'enroute'   => 64,
     *      'departed'  => 19,
     *      'scheduled_departures'  => 811,
     *      'scheduled_arrivals'    => 748,
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_CountAirportOperations
     */
    public function countAirportOperations(string $airportCode): array
    {
        $queryParams['airport'] = $airportCode;
        return $this->makeRequest('CountAirportOperations', $queryParams);
    }

    /**
     * returns an array of airlines and how many flights each currently has enroute.
     *
     * Example of a returned array:
     * @return array [
     *      0 => [
     *          'icao'      => 'SWA',
     *          'name'      => 'Southwest',
     *          'enroute'   => 317,
     *      ]
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_CountAllEnrouteAirlineOperations
     */
    public function countAllEnrouteAirlineOperations(): array
    {
        return $this->makeRequest('CountAllEnrouteAirlineOperations')['data'];
    }

    /**
     * Departed returns information about already departed flights for a specified airport and maximum number of flights to be returned.
     * Departed flights are returned in order from most recently to least recently departed.
     * Only flights that have departed within the last 24 hours are considered.
     *
     * Times returned are seconds since 1970 (UNIX epoch seconds).
     *
     * @param  string $airportCode the ICAO airport ID (Example: KLAX)
     * @param  int|null $howMany determines the number of results. Must be a positive integer value less than or equal to 15, unless maximum number will be set.
     * @param  string|null $filter ga|airline|null "ga" to show only general aviation traffic, "airline" to only show airline traffic, or null/empty to show all traffic.
     * @param  int|null $offset must be an integer value of the offset row count you want the search to start at. Most requests should be 0.
     *
     * Example of a returned array:
     * @return array [
     *      'next_offset' => 15,
     *      'departures'  => [
     *          0 => [
     *              'ident'                 => 'SWA1796',
     *              'aircrafttype'          => 'B737',
     *              'actualdeparturetime'   => 1593287830,
     *              'estimatedarrivaltime'  => 1593295680,
     *              'actualarrivaltime'     => 0,
     *              'origin'                => 'KHOU',
     *              'destination'           => 'KMKE',
     *              'originName'            => 'William P Hobby',
     *              'originCity'            => 'Houston, TX',
     *              'destinationName'       => 'Milwaukee Mitchell Intl Airport',
     *              'destinationCity'       => 'Milwaukee, WI',
     *          ]
     *      ]
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_Departed
     */
    public function departed(string $airportCode, int $howMany = null, string $filter = null, int $offset = 0): array
    {
        $queryParams = [
            'airport' => $airportCode,
            'howMany' => $howMany,
            'filter'  => $filter,
            'offset'  => $offset,
        ];
        return $this->makeRequest('Departed', $queryParams);
    }

    /**
     * returns information about flights already in the air heading towards the specified airport and also flights scheduled to arrive at the specified airport.
     * Enroute flights are returned from soonest estimated arrival to least soon estimated arrival.
     * The howMany argument specifies the maximum number of flights to be returned.
     *
     *
     * @param  string $airportCode the ICAO airport ID (Example: KLAX)
     * @param  int|null $howMany determines the number of results. Must be a positive integer value less than or equal to 15, unless maximum number will be set.
     * @param  string|null $filter ga|airline|null "ga" to show only general aviation traffic, "airline" to only show airline traffic, or null/empty to show all traffic.
     * @param  int|null $offset must be an integer value of the offset row count you want the search to start at. Most requests should be 0.
     *
     * Example of a returned array:
     * @return array [
     *      'next_offset' => 15,
     *      'enroute'  => [
     *          0 => [
     *              'ident'                 => 'SWA1826',
     *              'aircrafttype'          => 'B738',
     *              'actualdeparturetime'   => 1593285503,
     *              'estimatedarrivaltime'  => 1593288600,
     *              'filed_departuretime'   => 1593285600,
     *              'origin'                => 'KPHX',
     *              'destination'           => 'KLAX',
     *              'originName'            => 'Phoenix Sky Harbor Intl',
     *              'originCity'            => 'Phoenix, AZ',
     *              'destinationName'       => 'Los Angeles Intl',
     *              'destinationCity'       => 'Los Angeles, CA',
     *          ]
     *      ]
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_Enroute
     */
    public function enroute(string $airportCode, int $howMany = null, string $filter = null, int $offset = 0): array
    {
        $queryParams = [
            'airport' => $airportCode,
            'howMany' => $howMany,
            'filter'  => $filter,
            'offset'  => $offset,
        ];
        return $this->makeRequest('Enroute', $queryParams);
    }

    /**
     * returns information about recently arrived flights belonging to an aircraft fleet.
     * Only flights that have arrived within the last 24 hours are considered. Codeshares and alternate idents are NOT considered.
     *
     * The next_offset value returned advises an application of the next offset to use (if more data is available).
     *
     * @param  string $fleet must be an ICAO prefix (Example: COA)
     * @param  int|null $howMany determines the number of results. Must be a positive integer value less than or equal to 15, unless maximum number will be set.
     * @param  int|null $offset must be an integer value of the offset row count you want the search to start at. Most requests should be 0.
     *
     * Example of a returned array:
     * @return array [
     *      'next_offset' => 15,
     *      'arrivals'  => [
     *          0 => [
     *              'ident'                 => 'AFL2361',
     *              'aircrafttype'          => 'A321',
     *              'actualdeparturetime'   => 1593273283,
     *              'actualarrivaltime'     => 1593284940,
     *              'origin'                => 'LFMN',
     *              'destination'           => 'UUEE',
     *              'originName'            => 'Nice Cote d'Azur',
     *              'originCity'            => 'Nice',
     *              'destinationName'       => 'Sheremetyevo Int'l',
     *              'destinationCity'       => 'Moscow',
     *          ]
     *      ]
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_FleetArrived
     */
    public function fleetArrived(string $fleet, int $howMany = null, int $offset = 0): array
    {
        $queryParams = [
            'fleet' => $fleet,
            'howMany' => $howMany,
            'offset'  => $offset,
        ];
        return $this->makeRequest('FleetArrived', $queryParams);
    }

    /**
     * returns information about scheduled flights belonging to an aircraft fleet. Scheduled flights are returned from soonest to furthest in the future to depart.
     * Only flights that have not actually departed, and have a scheduled departure time between 2 hours in the past and 24 hours in the future, are considered.
     * Codeshares and alternate idents are NOT considered.
     *
     * The next_offset value returned advises an application of the next offset to use (if more data is available).
     *
     *
     * @param  string $fleet must be an ICAO prefix (Example: COA)
     * @param  int|null $howMany determines the number of results. Must be a positive integer value less than or equal to 15, unless maximum number will be set.
     * @param  int|null $offset must be an integer value of the offset row count you want the search to start at. Most requests should be 0.
     *
     * Example of a returned array:
     * @return array [
     *      'next_offset' => 15,
     *      'scheduled'  => [
     *          0 => [
     *              'ident'                 => 'AFL1322',
     *              'aircrafttype'          => 'A320',
     *              'filed_departuretime'   => 1593289800,
     *              'estimatedarrivaltime'  => 1593298320,
     *              'origin'                => 'UUEE',
     *              'destination'           => 'ULMM',
     *              'originName'            => 'Sheremetyevo Int',
     *              'originCity'            => 'Moscow',
     *              'destinationName'       => 'Murmansk',
     *              'destinationCity'       => 'Murmansk',
     *          ]
     *      ]
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_FleetScheduled
     */
    public function fleetScheduled(string $fleet, int $howMany = null, int $offset = 0): array
    {
        $queryParams = [
            'fleet' => $fleet,
            'howMany' => $howMany,
            'offset'  => $offset,
        ];
        return $this->makeRequest('FleetScheduled', $queryParams);
    }

    /**
     * returns information about flights for a specific tail number (e.g., N12345), or ICAO airline code with flight number (e.g., SWA2558).
     * The howMany argument specifies the maximum number of flights to be returned. Flight information will be returned from newest to oldest.
     * The oldest flights searched by this function are about 2 weeks in the past.
     * Use of ICAO codes is strongly recommended instead of IATA
     * Codeshares and alternate idents are automatically searched, which may cause the actual identifier of the primary operator of the flight to be returned instead of the originally requested identifier.
     *
     * The next_offset value returned advises an application of the next offset to use (if more data is available).
     *
     *
     * @param  string $ident requested tail number, or airline with flight number (Example: AFL1322)
     * @param  int|null $howMany determines the number of results. Must be a positive integer value less than or equal to 15, unless maximum number will be set.
     *
     * Example of a returned array:
     * @return array [
     *      'next_offset' => -1,
     *      'flights'  => [
     *          0 => [
     *              'ident'                 => 'AFL1322',
     *              'aircrafttype'          => 'A320',
     *              'filed_ete'             => '02:24:00',
     *              'filed_time'            => 1592857484,
     *              'filed_departuretime'   => 1592857800,
     *              'filed_airspeed_kts'    => 361,
     *              'filed_airspeed_mach'   => 0,
     *              'filed_altitude'        => 0,
     *              'route'                 => 0,
     *              'actualdeparturetime'   => 1592857517,
     *              'estimatedarrivaltime'  => 1592865004,
     *              'actualarrivaltime'     => 1592865004,
     *              'diverted'              => 0,
     *              'origin'                => 'UUEE',
     *              'destination'           => 'ULMM',
     *              'originName'            => 'Sheremetyevo Int',
     *              'originCity'            => 'Moscow',
     *              'destinationName'       => 'Murmansk',
     *              'destinationCity'       => 'Murmansk',
     *          ]
     *      ]
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_FlightInfo
     */
    public function flightInfo(string $ident, int $howMany = null): array
    {
        $queryParams = [
            'ident'     => $ident,
            'howMany'   => $howMany,
        ];
        return $this->makeRequest('FlightInfo', $queryParams);
    }

    /**
     * GetLastTrack looks up a flight's track log by specific tail number (e.g., N12345) or ICAO airline and flight number (e.g., SWA2558).
     * It returns the track log from the current IFR flight or, if the aircraft is not airborne, the most recent IFR flight.
     * This function only returns tracks for recent flights within approximately the last 24 hours.
     *
     * @param  string $ident requested tail number (Example: AFL1322)
     *
     * Example of a returned array:
     * @return array [
     *      0 => [
     *          'timestamp'         => 1593289695,
     *          'latitude'          => 55.96866,
     *          'longitude'         => 37.3968,
     *          'groundspeed'       => 157,
     *          'altitude'          => 11,
     *          'altitudeStatus'    => '',
     *          'updateType'        => 'TA',
     *          'altitudeChange'    => '',
     *      ]
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_GetLastTrack
     */
    public function getLastTrack(string $ident): array
    {
        $queryParams['ident'] = $ident;
        return $this->makeRequest('GetLastTrack', $queryParams)['data'];
    }

    /**
     * InFlightInfo looks up a specific tail number (e.g., N12345) or ICAO airline and flight number (e.g., SWA2558)
     * and returns current position/direction/speed information. It is only useful for currently airborne flights within approximately the last 24 hours.
     * Codeshares and alternate idents are automatically searched.
     *
     * @param  string $ident requested tail number (Example: AFL1322)
     *
     * Example of a returned array:
     * @return array [
     *      'faFlightID' => 'AFL1322-1593060315-airline-0162',
     *      'ident' => 'AFL1322',
     *      'prefix' => '',
     *      'type' => 'A320',
     *      'suffix' => '',
     *      'origin' => 'UUEE',
     *      'destination' => 'ULMM',
     *      'timeout' => 'ok',
     *      'timestamp' => 1593290342,
     *      'departureTime' => 1593289695,
     *      'firstPositionTime' => 1593289695,
     *      'arrivalTime' => 0,
     *      'longitude' => 37.03075,
     *      'latitude' => 56.58119,
     *      'lowLongitude' => 36.93537,
     *      'lowLatitude' => 55.9157,
     *      'highLongitude' => 37.3968,
     *      'highLatitude' => 56.58119,
     *      'groundspeed' => 396,
     *      'altitude' => 245,
     *      'heading' => 0,
     *      'altitudeStatus' => '',
     *      'updateType' => 'TA',
     *      'altitudeChange' => 'C',
     *      'waypoints' => '64',
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_InFlightInfo
     */
    public function inFlightInfo(string $ident): array
    {
        $queryParams['ident'] = $ident;
        return $this->makeRequest('InFlightInfo', $queryParams);
    }

    /**
     * Given two latitudes and longitudes, lat1 lon1 lat2 and lon2, respectively, determine the great circle distance between those positions in miles.
     * The returned distance is rounded to the nearest whole mile.
     *
     * @param  float $lat1 Latitude of point 1 (Example: 14.34627)
     * @param  float $lon1 Longitude of point 1 (Example: -32.18799)
     * @param  float $lat2 Latitude of point 2 (Example: 15.23200)
     * @param  float $lon2 Longitude of point 2 (Example: -17.69096)
     *
     * Example of a returned array:
     * @return int
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_LatLongsToDistance
     */
    public function latLongsToDistance(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $queryParams = [
            'lat1' => $lat1,
            'lon1' => $lon1,
            'lat2' => $lat2,
            'lon2' => $lon2,
        ];
        return (int)$this->makeRequest('LatLongsToDistance', $queryParams);
    }

    /**
     * Given two latitudes and longitudes, lat1 lon1 lat2 and lon2, respectively, calculate and return the initial compass heading (where 360 is North) from position one to position two.
     * Quite accurate for relatively short distances but since it assumes the earth is a sphere rather than on irregular oblate sphereoid may be inaccurate for flights around a good chunk of the world, etc.
     *
     * @param  float $lat1 Latitude of point 1 (Example: 14.34627)
     * @param  float $lon1 Longitude of point 1 (Example: -32.18799)
     * @param  float $lat2 Latitude of point 2 (Example: 15.23200)
     * @param  float $lon2 Longitude of point 2 (Example: -17.69096)
     *
     * Example of a returned array:
     * @return int
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_LatLongsToHeading
     */
    public function latLongsToHeading(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $queryParams = [
            'lat1' => $lat1,
            'lon1' => $lon1,
            'lat2' => $lat2,
            'lon2' => $lon2,
        ];
        return (int)$this->makeRequest('LatLongsToHeading', $queryParams);
    }

    /**
     * Given an airport, return the METAR weather info as parsed, human-readable, and raw formats.
     * If no reports are available at the requested airport but are for a nearby airport, then the reports from that airport may be returned instead.
     * If a value greater than 1 is specified for howMany then multiple past reports will be returned, in order of increasing age.
     * Historical data is generally only available for the last 7 days.
     *
     * @param  string $airportCode the ICAO airport ID (Example: KLAX)
     * @param  int|null $howMany determines the number of results. Must be a positive integer value less than or equal to 15, unless maximum number will be set.
     * @param  string|null $filter ga|airline|null "ga" to show only general aviation traffic, "airline" to only show airline traffic, or null/empty to show all traffic.
     * @param  int|null $offset must be an integer value of the offset row count you want the search to start at. Most requests should be 0.
     *
     * Example of a returned array:
     * @return array [
     *      'next_offset' => 15,
     *      'metar' => [
     *          0 => [
     *              'airport'           => 'ULMM',
     *              'time'              => 1593289800,
     *              'cloud_friendly'    => 'Raining',
     *              'cloud_altitude'    => 2000,
     *              'cloud_type'        => 'BKN',
     *              'conditions'        => '-SHRA',
     *              'pressure'          => 1019,
     *              'temp_air'          => 7,
     *              'temp_dewpoint'     => 6,
     *              'temp_relhum'       => 94,
     *              'visibility'        => -1,
     *              'wind_friendly'     => 'Calm winds',
     *              'wind_direction'    => 340,
     *              'ident'             => 350,
     *              'wind_speed'        => 3,
     *              'wind_speed_gust'   => 0,
     *              'raw_data'          => 'ULMM 272030Z 35003MPS 9999 -SHRA BKN020CB 07/06 Q1019 R31/190065 NOSIG RMK QFE757',
     *          ]
     *      ]
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_MetarEx
     */
    public function metarEx(string $airportCode, int $howMany = null, string $filter = null, int $offset = 0): array
    {
        $queryParams = [
            'airport' => $airportCode,
            'howMany' => $howMany,
            'filter'  => $filter,
            'offset'  => $offset,
        ];

        return $this->makeRequest('MetarEx', $queryParams);
    }

    /**
     * Scheduled returns information about scheduled flights (technically, filed IFR flights) for a specified airport and a maximum number of flights to be returned.
     * Scheduled flights are returned from soonest to furthest in the future to depart.
     * Only flights that have not actually departed, and have a scheduled departure time between 2 hours in the past and 24 hours in the future, are considered.
     *
     * @param  string $airportCode the ICAO airport ID (Example: KLAX)
     * @param  int|null $howMany determines the number of results. Must be a positive integer value less than or equal to 15, unless maximum number will be set.
     * @param  string|null $filter ga|airline|null "ga" to show only general aviation traffic, "airline" to only show airline traffic, or null/empty to show all traffic.
     * @param  int|null $offset must be an integer value of the offset row count you want the search to start at. Most requests should be 0.
     *
     * Example of a returned array:
     * @return array [
     *      'next_offset' => -1,
     *      'scheduled' => [
     *          0 => [
     *              'ident'                 => 'AFL1323',
     *              'aircrafttype'          => 'A320',
     *              'filed_departuretime'   => 1593301800,
     *              'estimatedarrivaltime'  => 1593309480,
     *              'origin'                => 'ULMM',
     *              'destination'           => 'UUEE',
     *              'originName'            => 'Murmansk',
     *              'originCity'            => 'Murmansk',
     *              'destinationName'       => 'Sheremetyevo Int',
     *              'destinationCity'       => 'Moscow',
     *          ]
     *      ]
     * ]
     *
     * @see https://flightaware.com/commercial/flightxml/explorer/#op_Scheduled
     */
    public function scheduled(string $airportCode, int $howMany = null, string $filter = null, int $offset = 0): array
    {
        $queryParams = [
            'airport' => $airportCode,
            'howMany' => $howMany,
            'filter'  => $filter,
            'offset'  => $offset,
        ];

        return $this->makeRequest('Scheduled', $queryParams);
    }

    /**
     * Make a request to the API.
     *
     * @param  string $endpoint    The endpoint to make the request to
     * @param  array  $queryParams The array with query parameters
     * @param  string $key         The key used in the response from the API
     *                             (optional)
     * @return array               The reponse data from the API
     */
    protected function makeRequest($endpoint, $queryParams = [], $key = null)
    {
        try {
            $response = $this->getClient()->request('GET', $endpoint, [
                'query' => $queryParams,
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }

        return $this->parseResponse($response, $key);
    }


    /**
     * Get the configured HTTP client.
     *
     * @return Client
     */
    protected function getClient()
    {
        if ($this->client === null) {
            $this->client = new Client([
                'base_uri' => self::BASE_URI,
                'auth' => [
                    $this->options['username'],
                    $this->options['api_key'],
                ],
            ]);
        }

        return $this->client;
    }

    /**
     * Parse the response from the API.
     *
     * @param  ResponseInterface $response The response from the API
     * @param  string            $key      The key used in the response from the
     *                                     API (optional)
     * @return array                       The reponse data from the API
     */
    protected function parseResponse(ResponseInterface $response, $key = null)
    {
        $body = json_decode($response->getBody(), true);

        $statusCode = $response->getStatusCode();

        if ($statusCode != 200 ||
            array_key_exists('error', $body)
        ) {
            throw new FlightAwareClientException($body['error'], $statusCode);
        }

        reset($body);

        $firstKey = key($body);

        $data = $body[$firstKey];

        if ($key !== null) {
            return $data[$key];
        }

        return $data;
    }
}

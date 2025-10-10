<?php

namespace Tualo\Office\Termine;

use Tualo\Office\MSGraph\API;

use Microsoft\Graph\GraphRequestAdapter;
use Microsoft\Kiota\Abstractions\Authentication\BaseBearerTokenAuthenticationProvider;
use Microsoft\Graph\Generated\Models\Subscription;
use Microsoft\Kiota\Authentication\Cache\InMemoryAccessTokenCache;
use Microsoft\Graph\GraphServiceClient;
use Microsoft\Graph\Core\Authentication\GraphPhpLeagueAccessTokenProvider;
use Microsoft\Graph\Core\Authentication\GraphPhpLeagueAuthenticationProvider;
use Microsoft\Kiota\Authentication\Oauth\AuthorizationCodeContext;

use Ramsey\Uuid\Uuid;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use League\OAuth2\Client\Token\AccessToken;


use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\MSGraph\api\MissedTokenException;
use Tualo\Office\MSGraph\api\DeviceCodeTokenProvider;

class MSGraphTermine extends API
{

    public static function jsonDateTime(array $date): ?\DateTime
    {
        /*
        {
                    "dateTime": "2025-10-07T10:00:00.0000000",
                    "timeZone": "UTC"
                }
        */
        if (isset($date['dateTime'])) {
            try {
                $dt = new \DateTime($date['dateTime'], new \DateTimeZone($date['timeZone'] ?? 'UTC'));
                return $dt;
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    public static function getCalendars()
    {
        $tokenClient = self::getClient([
            'authorization' => 'Bearer ' . self::env('access_token'),
        ]);
        $url = self::graphURL() . '/me/calendars';

        $clientResponse = $tokenClient->get($url, [
            'http_errors' => false,
            'exceptions' => false
        ]);
        $statusCode = $clientResponse->getStatusCode();
        $response = json_decode($clientResponse->getBody()->getContents(), true);
        $response['statusCode'] = $statusCode;

        return $response;
    }

    public static function getEvents(string $select = 'subject,body,start,end,location')
    {
        $tokenClient = self::getClient([
            'authorization' => 'Bearer ' . self::env('access_token'),
        ]);
        $url = self::graphURL() . '/me/events?$select=' . $select;


        $clientResponse = $tokenClient->get($url, [
            'http_errors' => false,
            'exceptions' => false
        ]);
        $statusCode = $clientResponse->getStatusCode();
        $response = json_decode($clientResponse->getBody()->getContents(), true);
        $response['statusCode'] = $statusCode;

        return $response;
    }


    // calendarView?startDateTime={start_datetime}&endDateTime={end_datetime}

    public static function getCalendarEvents(
        string $calendarId,
        string $select = 'subject,body,start,end,location',
        string $startDateTime = '',
        string $endDateTime = ''
    ) {

        $tokenClient = self::getClient([
            'authorization' => 'Bearer ' . self::env('access_token'),
        ]);
        $url = self::graphURL() . '/me/calendars/' . $calendarId . '/events?$select=' . $select;
        //        /me/calendar/events/{id}/instances
        // $url = self::graphURL() . '/me/calendar/events/' . $calendarId . '/instances?$select=' . $select;

        // Filter by start and end datetime if provided
        // $filter=start/dateTime eq '2022-07-14T12:30:00Z'

        $filters = [];
        if ($startDateTime != '') {
            $filters[] = "start/dateTime ge '2025-10-08T12:30:00Z'";
        }
        if ($endDateTime != '') {
            $filters[] = "end/dateTime le '2025-12-27T12:30:00Z'";
        }
        if (count($filters) > 0) {
            $url .= '&$filter=' . urlencode(implode(' and ', $filters));
        }



        // startdatetime=2025-10-08T06:24:39.248Z&enddatetime=2025-10-15T06:24:39.248Z

        $clientResponse = $tokenClient->get($url, [
            'http_errors' => false,
            'exceptions' => false
        ]);
        $statusCode = $clientResponse->getStatusCode();
        $response = json_decode($clientResponse->getBody()->getContents(), true);
        $response['statusCode'] = $statusCode;

        return $response;
    }

    ///me/calendars/{id}/calendarView?startDateTime={start_datetime}&endDateTime={end_datetime}

    public static function getCalendarViewEvents(
        string $calendarId,
        string $select = 'subject,body,start,end,location',
        string $startDateTime = '',
        string $endDateTime = ''
    ) {
        $tokenClient = self::getClient([
            'authorization' => 'Bearer ' . self::env('access_token'),
        ]);
        $url = self::graphURL() . '/me/calendars/' . $calendarId . '/calendarView?startDateTime=' . $startDateTime . '&endDateTime=' . $endDateTime . '&$select=' . $select;

        $clientResponse = $tokenClient->get($url, [
            'http_errors' => false,
            'exceptions' => false
        ]);
        $statusCode = $clientResponse->getStatusCode();
        $response = json_decode($clientResponse->getBody()->getContents(), true);
        $response['statusCode'] = $statusCode;

        return $response;
    }


    /**
     * {
     *  "subject": "Let's go for lunch",
     *  "body": {
     *    "contentType": "HTML",
     *    "content": "Does noon work for you?"
     *  },
     *  "start": {
     *    "dateTime": "2017-04-15T12:00:00",
     *    "timeZone": "Pacific Standard Time"
     *  },
     *  "end": {
     *    "dateTime": "2017-04-15T14:00:00",
     *    "timeZone": "Pacific Standard Time"
     *  },
     *  "location":{
     *    "displayName":"Harry's Bar"
     *  },
     *  "attendees": [
     *      {
     *      "emailAddress": {
     *          "address":"samanthab@contoso.com",
     *          "name": "Samantha Booth"
     *      },
     *      "type": "required"
     *      }
     *  ],
     *  "allowNewTimeProposals": true,
     *  "transactionId":"7E163156-7762-4BEB-A1C6-729EA81755A7"
     *  }
     */
    public static function createEvent(string $calendarId, array $eventData)
    {
        $tokenClient = self::getClient([
            'authorization' => 'Bearer ' . self::env('access_token'),
            'Content-Type' => 'application/json',
        ]);
        $url = self::graphURL() . '/me/calendars/' . $calendarId . '/events';

        $clientResponse = $tokenClient->post($url, [
            'http_errors' => false,
            'exceptions' => false,
            RequestOptions::JSON => $eventData
        ]);

        $statusCode = $clientResponse->getStatusCode();
        $response = json_decode($clientResponse->getBody()->getContents(), true);
        $response['statusCode'] = $statusCode;

        return $response;
    }

    public static function updateEvent(string $calendarId, array $eventData)
    {
        $tokenClient = self::getClient([
            'authorization' => 'Bearer ' . self::env('access_token'),
            'Content-Type' => 'application/json',
        ]);
        $url = self::graphURL() . '/me/calendars/' . $calendarId . '/events/' . $eventData['id'];
        print_r($eventData);

        $clientResponse = $tokenClient->patch($url, [
            'http_errors' => false,
            'exceptions' => false,
            RequestOptions::JSON => $eventData
        ]);

        $statusCode = $clientResponse->getStatusCode();
        $response = json_decode($clientResponse->getBody()->getContents(), true);
        $response['statusCode'] = $statusCode;

        return $response;
    }
}

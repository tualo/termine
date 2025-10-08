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
use League\OAuth2\Client\Token\AccessToken;


use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\MSGraph\api\MissedTokenException;
use Tualo\Office\MSGraph\api\DeviceCodeTokenProvider;

class MSGraphTermine extends API
{


    public static function getCalendars()
    {
        $tokenClient = self::getClient([
            'authorization' => 'Bearer ' . self::env('access_token'),
        ]);
        $url = self::graphURL() . '/me/calendars';
        $response = json_decode($tokenClient->get($url)->getBody()->getContents(), true);
        return $response;
    }

    public static function getEvents(string $select = 'subject,body,start,end,location')
    {
        $tokenClient = self::getClient([
            'authorization' => 'Bearer ' . self::env('access_token'),
        ]);
        $url = self::graphURL() . '/me/events?$select=' . $select;
        $response = json_decode($tokenClient->get($url)->getBody()->getContents(), true);
        return $response;
    }
}

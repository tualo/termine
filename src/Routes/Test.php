<?php

namespace Tualo\Office\Termine\Routes;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\RouteSecurityHelper;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\Termine\MSGraphTermine;

class Test implements IRoute
{
    public static function register()
    {
        BasicRoute::add('/termine/test', function ($matches) {
            try {
                $response = MSGraphTermine::getCalendars();
                App::result('response', $response);
                if ($response['statusCode'] == 200) {
                    App::result('success', true);
                } else {
                    App::result('success', false);
                    App::result('error', $response['error']['message'] ?? 'Unknown error');
                }
            } catch (\Exception $e) {
                App::result('error', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['get'], true);

        BasicRoute::add('/termine/test', function ($matches) {
            try {
                $response = MSGraphTermine::getCalendars();
                App::result('response', $response);
                if ($response['statusCode'] == 200) {
                    App::result('success', true);
                } else {
                    App::result('success', false);
                    App::result('error', $response['error']['message'] ?? 'Unknown error');
                }
            } catch (\Exception $e) {
                App::result('error', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['get'], true);

        BasicRoute::add('/termine/events', function ($matches) {
            try {
                $response = MSGraphTermine::getCalendarEvents('AAMkADAwYjk0MWIwLTBmMjAtNGNkZi05YzhiLTk0ODM3YjQwN2Q3MABGAAAAAABrGi0jy8LgRp0mzZPcQ5ZaBwB2nJhOTHfnQZPu-Tyfpu-sAAAAAAEGAAB2nJhOTHfnQZPu-Tyfpu-sAAT_LQPRAAA=', '');
                App::result('response', $response);
                if ($response['statusCode'] == 200) {
                    App::result('success', true);
                } else {
                    App::result('success', false);
                    App::result('error', $response['error']['message'] ?? 'Unknown error');
                }
            } catch (\Exception $e) {
                App::result('error', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['get'], true);


        BasicRoute::add('/termine/calendar', function ($matches) {
            try {
                $response = MSGraphTermine::getEvents();
                App::result('response', $response);
                /*

                [{
    "id": 1,
    "title": "Personal",
    "eventStore": {
        "proxy": {
            "type": "ajax",
            "url": "events.php"
        }
    }
}]

                {
    "id": 1001,
    "calendarId": 1,
    "startDate": "2016-09-30T21:30:00.000Z",
    "endDate": "2016-09-30T22:30:00.000Z",
    "title": "Watch cartoons",
    "description": "Catch up with adventurers Finn and Jake"
}
                */
                if ($response['statusCode'] == 200) {
                    App::result('success', true);
                } else {
                    App::result('success', false);
                    App::result('error', $response['error']['message'] ?? 'Unknown error');
                }
            } catch (\Exception $e) {
                App::result('error', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['get'], true);
    }
}

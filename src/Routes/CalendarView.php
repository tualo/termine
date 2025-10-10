<?php

namespace Tualo\Office\Termine\Routes;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\RouteSecurityHelper;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\DS\DSTable;
use Tualo\Office\Termine\MSGraphTermine;

class CalendarView implements IRoute
{
    public static function register()
    {

        BasicRoute::add('/calendarview/calendars', function ($matches) {
            try {

                $list = [];
                MSGraphTermine::refreshAccessToken();
                $response = MSGraphTermine::getCalendars();
                App::result('response', $response);
                if ($response['statusCode'] == 200) {


                    foreach ($response['value'] as $k => $v) {
                        $list[] = [
                            'id' => $v['id'],
                            'calendarId' => $v['id'],
                            'title' => 'MS: ' . $v['name'],
                            'eventStore' => [
                                'proxy' => [
                                    'type' => 'ajax',
                                    'url' => './calendarview/events?calendarId=' . $v['id']
                                ]
                            ]
                        ];
                    }
                } else {
                    App::result('success', false);
                    App::result('error', $response['error']['message'] ?? 'Unknown error');
                }
            } catch (\Exception $e) {
                App::result('error', $e->getMessage());
            }



            try {
                $calendars = DSTable::instance('termine_calendars')->g();

                foreach ($calendars as $k => $v) {
                    $list[] = [
                        'id' => $v['id'],
                        'calendarId' => $v['id'],
                        'title' => $v['name'],
                        'eventStore' => [
                            'proxy' => [
                                'type' => 'ajax',
                                'url' => './termine/calendars/' . $v['id'] . '/events'
                            ]
                        ]
                    ];
                }
                App::result('list', $list);
                App::jsonReturnField('list');
            } catch (\Exception $e) {
                App::result('error', $e->getMessage());
            }

            App::result('list', $list);
            App::jsonReturnField('list');


            App::contenttype('application/json');
        }, ['get'], true);

        BasicRoute::add('/calendarview/events', function ($matches) {
            try {

                if (isset($_GET['calendarId'])) {
                    $response = MSGraphTermine::getCalendarViewEvents(
                        $_GET['calendarId'],
                        'subject,body,start,end,location,isAllDay',
                        $_GET['startDate'] ?? '',
                        $_GET['endDate'] ?? ''
                    );
                    if ($response['statusCode'] == 200) {

                        $list = [];
                        foreach ($response['value'] as $k => $v) {
                            $list[] = [
                                'id' => $v['id'],
                                'calendarId' => $v['id'],
                                'allDay' => $v['isAllDay'] ?? false,
                                'title' => $v['subject'] ?? '',
                                'startDate' => MSGraphTermine::jsonDateTime($v['start'])->setTimezone(new \DateTimeZone("UTC"))->format('Y-m-d\TH:i:s.v\Z'),
                                'endDate' => MSGraphTermine::jsonDateTime($v['end'])->setTimezone(new \DateTimeZone("UTC"))->format('Y-m-d\TH:i:s.v\Z'),
                                'description' => $v['body']['content'] ?? '',
                            ];
                        }
                        App::result('list', $list);
                        App::jsonReturnField('list');
                    } else {
                        App::result('success', false);
                        App::result('error', $response['error']['message'] ?? 'Unknown error');
                    }
                } else {
                    throw new \Exception('no calendarId given');
                }
                /*

    
                {
    "id": 1001,
    "calendarId": 1,
    "startDate": "2016-09-30T21:30:00.000Z",
    "endDate": "2016-09-30T22:30:00.000Z",
    "title": "Watch cartoons",
    "description": "Catch up with adventurers Finn and Jake"
}

{
                "@odata.etag": "W/\"dpyYTkx350GT7v08n6bv7AAH1+ECuQ==\"",
                "id": "AAMkADAwYjk0MWIwLTBmMjAtNGNkZi05YzhiLTk0ODM3YjQwN2Q3MABGAAAAAABrGi0jy8LgRp0mzZPcQ5ZaBwB2nJhOTHfnQZPu-Tyfpu-sAAAAAAENAAB2nJhOTHfnQZPu-Tyfpu-sAAfY5TgOAAA=",
                "subject": "Sondertest",
                "body": {
                    "contentType": "html",
                    "content": "<html>\r\n<head>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\r\n</head>\r\n<body>\r\n<div style=\"font-family:Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif; font-size:12pt; color:rgb(0,0,0)\">\r\nTralalalaa</div>\r\n</body>\r\n</html>\r\n"
                },
                "start": {
                    "dateTime": "2025-10-07T10:00:00.0000000",
                    "timeZone": "UTC"
                },
                "end": {
                    "dateTime": "2025-10-07T10:30:00.0000000",
                    "timeZone": "UTC"
                },
                "location": {
                    "displayName": "",
                    "locationType": "default",
                    "uniqueIdType": "unknown",
                    "address": [],
                    "coordinates": []
                }
            },
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

<?php

namespace Tualo\Office\Termine\Routes;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\RouteSecurityHelper;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\DS\DSTable;
use Tualo\Office\Termine\MSGraphTermine;

class Termine extends \Tualo\Office\Basic\RouteWrapper
{
    public static function register()
    {
        BasicRoute::add('/termine/calendars', function ($matches) {

            App::contenttype('application/json');
            try {
                $calendars = DSTable::instance('termine_calendars')->g();

                $list = [];
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
        }, ['get'], true);

        BasicRoute::add('/termine/calendars/(?P<calendarId>[\w_\-\.]+)/events', function ($matches) {

            App::contenttype('application/json');
            try {

                $events = DSTable::instance('view_calendar_termine')->f('calendar_id', '=', $matches['calendarId'])->g();

                $list = [];
                foreach ($events as $k => $v) {
                    $list[] = [
                        'id' => $v['id'],
                        'calendarId' => $v['calendar_id'],
                        'allDay' => (bool)($v['is_all_day'] ?? false),
                        'title' => $v['subject'] ?? '',
                        'startDate' => new \DateTime($v['begin_date'] . ' ' . $v['begin_time'])->setTimezone(new \DateTimeZone("UTC"))->format('Y-m-d\TH:i:s.v\Z'),
                        'endDate' => new \DateTime($v['end_date'] . ' ' . $v['end_time'])->setTimezone(new \DateTimeZone("UTC"))->format('Y-m-d\TH:i:s.v\Z'),
                        'description' => $v['body'] ?? '',
                    ];
                }
                App::result('list', $list);

                App::jsonReturnField('list');
            } catch (\Exception $e) {
                App::result('error', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['get'], true);
    }
}

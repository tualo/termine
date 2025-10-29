<?php

namespace Tualo\Office\Termine\Routes;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\RouteSecurityHelper;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\Termine\MSGraphTermine;

class Cron extends \Tualo\Office\Basic\RouteWrapper
{

    public static function register()
    {
        BasicRoute::add('/termine/cron/msgraph/calendars', function ($matches) {
            try {

                $list = [];
                MSGraphTermine::refreshAccessToken();
                $response = MSGraphTermine::getCalendars();
                $table = \Tualo\Office\DS\DSTable::instance('msgraph_calendars');
                App::result('response', $response);
                if ($response['statusCode'] == 200) {


                    foreach ($response['value'] as $k => $v) {

                        $item = [
                            'name' => $v['name'],
                            'msgraph_calendars_id' => $v['id'],
                            'raw' => json_encode($v)
                        ];

                        $id = $table->f('msgraph_calendars_id', '=', $v['id'])->getSingleValue('id');
                        if ($id !== false) {
                            $item['id'] = $id;
                        }


                        $list[] = $item;
                    }
                    if (count($list)) {
                        $table = \Tualo\Office\DS\DSTable::instance('msgraph_calendars');
                        $table->g('msgraph_calendars_id');
                        $table->insert($list, ['type' => 'insert', 'update' => true]);
                    }
                }
                App::result('success', true);
            } catch (\Exception $e) {
                App::result('error', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['get'], true);

        BasicRoute::add('/termine/cron/msgraph/events', function ($matches) {
            try {
                MSGraphTermine::refreshAccessToken();
                $db = App::get('session')->getDB();
                $table = \Tualo\Office\DS\DSTable::instance('msgraph_calendars');
                $calendars = $table->f('active', '=', 1)->g();
                App::result('calendars', $calendars);
                foreach ($calendars as $k => $v) {

                    $eventList = $db->direct('
                        select 
                            * 
                        from 
                            view_sync_msgraph_termine 
                        where 
                            msgraph_calendars_id={msgraph_calendars_id} 
                            and __type in ("new","push")
                        order by 
                            last_sync desc 
                        limit 1', [
                        'msgraph_calendars_id' => $v['msgraph_calendars_id']
                    ]);

                    foreach ($eventList as $ek => $ev) {
                        $object = null;
                        $event = json_decode($ev['msgraph_event'], true);


                        if ($ev['__type'] == 'push') {
                            $event['id'] = $ev['msgraph_event_id'];
                            $object = MSGraphTermine::updateEvent($ev['msgraph_calendars_id'], $event);
                        }
                        if ($ev['__type'] == 'new') {
                            $object = MSGraphTermine::createEvent($ev['msgraph_calendars_id'], $event);
                        }
                        if (!is_null($object)) {
                            if ($object['statusCode'] != 201 && $object['statusCode'] != 200) {
                                App::result('object', $object);
                                App::result('error', $object['message']);
                                continue;
                            }
                            $record = [
                                'termin_id' => $ev['termin_id'],
                                'msgraph_calendars_id' => $ev['msgraph_calendars_id'],
                                'update_uuid' => $ev['update_uuid'],
                                'msgraph_event_id' => $object['id'],
                                'etag' => $object['@odata.etag'],
                                'iCalUId' => $object['iCalUId'],
                                'raw' => json_encode($object)
                            ];

                            $table = \Tualo\Office\DS\DSTable::instance('msgraph_termine');
                            if ($ev['__type'] == 'new') {
                                $table->insert($record, ['type' => 'insert', 'update' => true]);
                            } else {
                                if ($ev['__type'] == 'push') {
                                    $record['id'] = $ev['msgraph_termine_id'];
                                    $record['__id'] = $ev['msgraph_termine_id'];
                                }
                                $x = $table->update($record, ['update' => true]);

                                $db = App::get('session')->getDB();
                            }
                            App::result('e', $table->errorMessage());
                            App::result('ev', $ev);
                            App::result('record', $record);
                        }
                    }
                }
                App::result('success', true);
            } catch (\Exception $e) {
                App::result('error', $e->getMessage());
            }
            App::contenttype('application/json');
        }, ['get'], true);
    }
}

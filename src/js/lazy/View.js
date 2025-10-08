Ext.define('Tualo.Termine.lazy.View', {
    extend: 'Ext.panel.Panel',
    requires: [
        'Tualo.Termine.lazy.models.View',
        'Tualo.Termine.lazy.controller.View'
    ],
    alias: 'widget.termine_view',
    controller: 'termine_view',

    viewModel: {
        type: 'termine_view'
    },
    listeners: {
        boxReady: 'onBoxReady'
    },

    layout: 'fit',
    /*
    bind:{
        title: '{ftitle}'
    },
    */
    items: [

        {
            xtype: 'calendar',
            views: {
                day: {
                    startTime: 6,
                    endTime: 22
                },
                month: {
                    xtype: 'calendar-month',
                    titleTpl: '{start:date("M Y")}',
                    label: 'Monat',
                    weight: 10,
                    showHeader: true,
                    showWeekNumbers: true,
                    firstDayOfWeek: 1,
                    visibleDays: 7,
                    // compactView: true
                },
                workweek: {
                    xtype: 'calendar-week',
                    titleTpl: '{start:date("j M")} - {end:date("j M")}',
                    label: 'Woche',
                    weight: 15,
                    dayHeaderFormat: 'D d',
                    firstDayOfWeek: 1,
                    visibleDays: 5
                }
            },
            store: {
                autoLoad: true,
                proxy: {
                    type: 'ajax',
                    url: './calendarview/calendars',
                    yxurl: './termine/calendars',
                }
            }
        }
    ],

});

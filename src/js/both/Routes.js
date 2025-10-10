Ext.define('Tualo.routes.Termine.View', {
    statics: {
        load: async function () {
            return [
                {
                    name: 'termine/view',
                    path: '#termine/view'
                }
            ]
        }
    },
    url: 'termine/view',
    handler: {
        action: function () {

            let mainView = Ext.getApplication().getMainView(),
                stage = mainView.getComponent('dashboard_dashboard').getComponent('stage'),
                component = null,
                cmp_id = 'msgraph_termine_view';
            component = stage.down(cmp_id);
            if (component) {
                stage.setActiveItem(component);
            } else {
                Ext.getApplication().addView('Tualo.Termine.lazy.View', {

                });
            }


        },
        before: function (action) {

            action.resume();
        }
    }
});
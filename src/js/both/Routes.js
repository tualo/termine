Ext.define('Tualo.routes.Termine.Setup', {
    statics: {
        load: async function () {
            return [
                {
                    name: 'termine/setup',
                    path: '#termine/setup'
                }
            ]
        }
    },
    url: 'termine/setup',
    handler: {
        action: function () {

            let mainView = Ext.getApplication().getMainView(),
                stage = mainView.getComponent('dashboard_dashboard').getComponent('stage'),
                component = null,
                cmp_id = 'msgraph_setup';
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
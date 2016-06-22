var Miniflux = {};

/**
* @define {boolean}
*/
var COMPILED = false;

Miniflux.App = (function() {

    return {
        Log: function(message) {
            if (! COMPILED) {
               console.log(message);
            }
        },
        Run: function() {
            Miniflux.Event.ListenKeyboardEvents();
            Miniflux.Event.ListenMouseEvents();
            Miniflux.Event.ListenVisibilityEvents();
            this.FrontendUpdateCheck();
        },
        FrontendUpdateCheck: function() {
            var request = new XMLHttpRequest();
            request.onload = function() {
                var response = JSON.parse(this.responseText);

                if (response['frontend_updatecheck_interval'] > 0) {
                    Miniflux.App.Log('Frontend updatecheck interval in minutes: ' + response['frontend_updatecheck_interval']);
                    Miniflux.Item.CheckForUpdates();
                    setInterval(function(){ Miniflux.Item.CheckForUpdates(); }, response['frontend_updatecheck_interval']*60*1000);
                }
                else {
                    Miniflux.App.Log('Frontend updatecheck disabled');
                }
            };

            request.open("POST", "?action=get-config", true);
            request.send(JSON.stringify(['frontend_updatecheck_interval']));
        }
    };

})();

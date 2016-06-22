Miniflux.Event = (function() {

    var queue = [];

    function isEventIgnored(e)
    {
        if (e.keyCode !== 63 && e.which !== 63 && (e.ctrlKey || e.shiftKey || e.altKey || e.metaKey)) {
            return true;
        }

        // Do not handle events when there is a focus in form fields
        var target = e.target || e.srcElement;
        if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') {
            return true;
        }

        return false;
    }

    return {
        lastEventType: "",
        ListenMouseEvents: function() {

            document.onclick = function(e) {
                if (e.target.hasAttribute("data-action") && e.target.className !== 'original') {
                    e.preventDefault();
                }
            };

            document.onmouseup = function(e) {

                // ignore right mouse button (context menu)
                if (e.button === 2) {
                    return;
                }

                // Auto-select input content

                if (e.target.nodeName === "INPUT" && e.target.className === "auto-select") {
                    e.target.select();
                    return;
                }

                // Application actions

                var action = e.target.getAttribute("data-action");

                if (action) {

                    Miniflux.Event.lastEventType = "mouse";

                    var currentItem = function () {
                        element = e.target;

                        while (element && element.parentNode) {
                            element = element.parentNode;
                            if (element.tagName && element.tagName.toLowerCase() === 'article') {
                                return element;
                            }
                        }

                        return;
                    }();

                    switch (action) {
                        case 'refresh-all':
                            Miniflux.Feed.UpdateAll(e.target.getAttribute("data-concurrent-requests"));
                            break;
                        case 'refresh-feed':
                            currentItem && Miniflux.Feed.Update(currentItem);
                            break;
                        case 'mark-read':
                            currentItem && Miniflux.Item.MarkAsRead(currentItem);
                            break;
                        case 'mark-unread':
                            currentItem && Miniflux.Item.MarkAsUnread(currentItem);
                            break;
                        case 'mark-removed':
                            currentItem && Miniflux.Item.MarkAsRemoved(currentItem);
                            break;
                        case 'bookmark':
                            currentItem && Miniflux.Item.SwitchBookmark(currentItem);
                            break;
                        case 'download-item':
                            currentItem && Miniflux.Item.DownloadContent(currentItem);
                            break;
                        case 'mark-feed-read':
                            var feed_id = document.getElementById('listing').getAttribute('data-feed-id');

                            Miniflux.Item.MarkFeedAsRead(feed_id);
                            break;
                    }
                }
            };
        },
        ListenKeyboardEvents: function() {

            document.onkeypress = function(e) {

                if (isEventIgnored(e)) {
                    return;
                }

                Miniflux.Event.lastEventType = "keyboard";

                queue.push(e.key || e.which);

                if (queue[0] === 'g' || queue[0] === 103) {

                    switch (queue[1]) {
                        case undefined:
                            break;
                        case 'u':
                        case 117:
                            window.location.href = "?action=unread";
                            queue = [];
                            break;
                        case 'b':
                        case 98:
                            window.location.href = "?action=bookmarks";
                            queue = [];
                            break;
                        case 'h':
                        case 104:
                            window.location.href = "?action=history";
                            queue = [];
                            break;
                        case 's':
                        case 115:
                            window.location.href = "?action=feeds";
                            queue = [];
                            break;
                        case 'p':
                        case 112:
                            window.location.href = "?action=config";
                            queue = [];
                            break;
                        default:
                            queue = [];
                            break;
                    }
                }
                else {

                    queue = [];

                    var currentItem = function () {
                        return document.getElementById("current-item");
                    }();

                    switch (e.key || e.which) {
                        case 'd':
                        case 100:
                            currentItem && Miniflux.Item.DownloadContent(currentItem);
                            break;
                        case 'p':
                        case 112:
                        case 'k':
                        case 107:
                            Miniflux.Nav.SelectPreviousItem();
                            break;
                        case 'n':
                        case 110:
                        case 'j':
                        case 106:
                            Miniflux.Nav.SelectNextItem();
                            break;
                        case 'v':
                        case 118:
                            currentItem && Miniflux.Item.OpenOriginal(currentItem);
                            break;
                        case 'o':
                        case 111:
                            currentItem && Miniflux.Item.Show(currentItem);
                            break;
                        case 'm':
                        case 109:
                            currentItem && Miniflux.Item.SwitchStatus(currentItem);
                            break;
                        case 'f':
                        case 102:
                            currentItem && Miniflux.Item.SwitchBookmark(currentItem);
                            break;
                        case 'h':
                        case 104:
                            Miniflux.Nav.OpenPreviousPage();
                            break
                        case 'l':
                        case 108:
                            Miniflux.Nav.OpenNextPage();
                            break;
                        case 'r':
                        case 114:
                            Miniflux.Feed.UpdateAll();
                            break;
                        case '?':
                        case 63:
                            Miniflux.Nav.ShowHelp();
                            break;
                        case 'z':
                        case 122:
                            Miniflux.Item.ToggleRTLMode();
                            break;
                    }
                }
            };

            document.onkeydown = function(e) {

                if (isEventIgnored(e)) {
                    return;
                }

                Miniflux.Event.lastEventType = "keyboard";

                switch (e.key || e.which) {
                    case "ArrowLeft":
                    case "Left":
                    case 37:
                        Miniflux.Nav.SelectPreviousItem();
                        break;
                    case "ArrowRight":
                    case "Right":
                    case 39:
                        Miniflux.Nav.SelectNextItem();
                        break;
                }
            };
        },
        ListenVisibilityEvents: function() {
            document.addEventListener('visibilitychange', function() {
                Miniflux.App.Log('document.visibilityState: ' + document.visibilityState);

                if (!document.hidden && Miniflux.Item.hasNewUnread()) {
                    Miniflux.App.Log('Need to update the unread counter with fresh values from the database');
                    Miniflux.Item.CheckForUpdates();
                }
            });
        }
    };
})();

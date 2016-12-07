<?PHP
define('MAINTENANCE', false);
define('CAPTURE_RESPONDER', false);
define('DEV_COOKIE_NAME', 'JesliCzegosNieWieszToZapytaj');
define('DEV_COOKIE_VALUE', 'ZaTresureChomikow');

if (
    (CAPTURE_RESPONDER && $_SERVER['REQUEST_URI']=='/responder') ||
    (!empty($_COOKIE[DEV_COOKIE_NAME]) && $_COOKIE[DEV_COOKIE_NAME] == DEV_COOKIE_VALUE)
) {
    require 'app_dev.php';
} else if (!MAINTENANCE) {
    require 'app.php';
} else {
    ?>
    <!doctype html>
    <html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <title>Maintenance</title>
        <script src="//cdn.dcsaas.net/js/appstore-sdk.js"></script>
        <script>
            var events = [];
            var app = new ShopApp(function (app) {
                app.init(null, function (params, app) {
                    for (var x = 0; x < params.styles.length; ++x) {
                        var el = document.createElement('link');
                        el.rel = 'stylesheet';
                        el.type = 'text/css';
                        el.href = params.styles[x];
                        document.getElementsByTagName('head')[0].appendChild(el);
                    }

                    app.show(null, function () {
                        app.adjustIframeSize();
                        for (var i in events) {
                            events[i](app);
                        }
                    });
                }, function (errmsg, app) {
                    alert(errmsg);
                });
            }, true);
        </script>
    </head>
    <body>
    <main class="rwd-layout-width rwd-layout-container">
        <section class="rwd-layout-col-12">
            <div class="edition-form">
                Application upgrade in progress. Stay tuned.
            </div>
        </section>
    </main>
    </body>
    </html>
    <?PHP
} 
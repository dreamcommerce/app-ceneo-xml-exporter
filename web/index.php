<?PHP
define('CHECKED', true);
if(strpos($_SERVER['REQUEST_URI'], 'responder')!==false || (!empty($_COOKIE['JesliCzegosNieWieszToZapytaj']) && $_COOKIE['JesliCzegosNieWieszToZapytaj'] == 'ZaTresureChomikow')){
  require 'app_dev.php';
}else{
  ?>
  <!doctype html>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">
        <title></title>
        <script src="//cdn.dcsaas.net/js/appstore-sdk.js"></script>
        <script>
            var events = [];
            var app = new ShopApp(function (app) {
                app.init(null, function (params, app) {
                    for(var x = 0; x < params.styles.length; ++x) {
                        var el = document.createElement('link');
                        el.rel = 'stylesheet';
                        el.type = 'text/css';
                        el.href = params.styles[x];
                        document.getElementsByTagName('head')[0].appendChild(el);
                    }

                    app.show(null ,function () {
                        app.adjustIframeSize();
                        for(var i in events){
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
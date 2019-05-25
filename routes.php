<?php

return $routes = function (FastRoute\RouteCollector $r) {

    $r->addRoute(['GET', 'POST'], '/sso/{sort_name}/redirect', function (array $vars) {
        $objPage = new LC_Page_Sso_AuthorizationCodeFlow();
        $objPage->init();
        $objPage->process();
    });

    $r->addRoute(['GET'], '/phpinfo', function(array $var) {
        phpinfo();
    });
};


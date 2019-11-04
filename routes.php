<?php

return $routes = function (FastRoute\RouteCollector $r) {

    $r->addRoute(['GET', 'POST'], '/sso/{short_name}/redirect', function (array $vars) {
        $objPage = new LC_Page_Sso_AuthorizationCodeFlow();
        $objPage->setVars($vars);
        $objPage->init();
        $objPage->process();
    });

    $r->addRoute(['GET', 'POST'], '/sso/{short_name}/register', function (array $vars) {
        $objPage = new LC_Page_Sso_CustomerRegister();
        $objPage->setVars($vars);
        $objPage->init();
        $objPage->process();
    });

    $r->addRoute(['GET'], '/sso/{short_name}/complete', function (array $vars) {
        $objPage = new LC_Page_Sso_CustomerRegister_Complete();
        $objPage->setVars($vars);
        $objPage->init();
        $objPage->process();
    });

    if ('cli-server' === php_sapi_name()) {
        $r->addRoute(['POST'], '/sso/{short_name}/token', function (array $vars) {
            echo json_encode(
                [
                    'access_token' => 'token',
                    'refresh_token' => 'refresh',
                    'expires_in' => 3600,
                    'token_type' => 'Bearer',
                    'scope' => 'profile'
                ]);
        });
        $r->addRoute(['GET', 'POST'], '/sso/{short_name}/userinfo', function (array $vars) {
            /** @var Faker\Generator $faker */
            $faker = Faker\Factory::create('ja_JP');
            echo json_encode(
                [
                    'user_id' => $faker->uuid,
                    'name' => $faker->name,
                    'email' => microtime(true).'.'.$faker->safeEmail,
                    'postal_code' => $faker->postcode
                ]);
        });
    }
    $r->addRoute(['GET'], '/phpinfo', function(array $vars) {
        phpinfo();
    });
};


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
        $r->addRoute(['GET'], '/sso/login', function (array $vars) {
            $objCustomer = new SC_Customer_Ex();
            if ($objCustomer->isLoginSuccess()) {
                header('Location: /');
            } else {
                echo file_get_contents(__DIR__.'/templates/tests/login.html');
            }
        });
        $r->addRoute(['GET'], '/sso/complete', function (array $vars) {
            $objCustomer = new SC_Customer_Ex();
            if (!$objCustomer->isLoginSuccess()) {
                header('Location: /');
            } else {
                echo file_get_contents(__DIR__.'/templates/tests/complete.html');
            }
        });
        $r->addRoute(['POST'], '/sso/{short_name}/token', function (array $vars) {
            $json = file_get_contents('php://input');
            $errors = json_encode(
                [
                    'error' => 'invalid_grant',
                    'error_description' => 'invalid'
                ]
            );
            if ($json !== false) {
                $arrJson = json_decode($json, true);
                if ($arrJson !== false && 'not_refresh' !== $arrJson['refresh_token']) {
                    echo json_encode(
                        [
                            'access_token' => isset($arrJson['code']) ? $arrJson['code'] : 'access_token',
                            'refresh_token' => 'refresh',
                            'expires_in' => 3600,
                            'token_type' => 'Bearer',
                            'scope' => 'profile'
                        ]);
                } else {
                    http_response_code(400);
                    echo $errors;
                }
            }
        });
        $r->addRoute(['GET', 'POST'], '/sso/{short_name}/userinfo', function (array $vars) {
            $user_id = $faker->uuid;
            if (isset($_SERVER['HTTP_AUTHORIZATION']))  {
                $user_id = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
            }
            /** @var Faker\Generator $faker */
            $faker = Faker\Factory::create('ja_JP');
            echo json_encode(
                [
                    'user_id' => $user_id,
                    'name' => $faker->name,
                    'email' => microtime(true).'.'.$faker->safeEmail,
                    'postal_code' => $faker->postcode
                ]);
        });
        $r->addRoute(['GET'], '/phpinfo', function(array $vars) {
            phpinfo();
        });
    }
};

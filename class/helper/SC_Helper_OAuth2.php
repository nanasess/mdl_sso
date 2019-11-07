<?php

class SC_Helper_OAuth2
{
    /**
     * 略称を使用して OAuth2Client を取得します.
     *
     * @param string $short_name
     * @return OAuth2Client
     */
    public static function getOAuth2Client($short_name)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrClient = $objQuery->getRow('*', 'dtb_oauth2_client', 'short_name = ?', [$short_name]);
        if (SC_Utils_Ex::isBlank($arrClient)) {
            trigger_error('OAuth2.0 Client not found', E_USER_ERROR);
        }
        return new OAuth2Client($arrClient);
    }

    /**
     * 略称の妥当性を検証します.
     *
     * @param string $short_name
     * @return bool
     */
    public static function validateShortName($short_name)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrShortNames = $objQuery->getCol('short_name', 'dtb_oauth2_client', 'del_flg = 0');
        return in_array($short_name, $arrShortNames);
    }

    /**
     * Redirect uri を返します.
     *
     * @param string $short_name
     * @return string
     */
    public static function getRedirectUri($short_name)
    {
        return HTTPS_URL.'sso/'.$short_name.'/redirect';
    }

    /**
     * Authorization Request URI を返します.
     *
     * @param OAuth2Client $objClient
     * @param string $state
     * @return string
     */
    public static function getAuthorizationRequestUri(OAuth2Client $objClient, $state = null)
    {
        $url = new Net_URL($objClient->authorize_endpoint);
        $url->addQueryString('response_type', 'code');
        $url->addQueryString('client_id', $objClient->client_id);
        $url->addQueryString('redirect_uri', self::getRedirectUri($objClient->short_name));
        if ($objClient->scope) {
            $url->addQueryString('scope', $objClient->scope);
        }
        if ($state !== null) {
            $url->addQueryString('state', $state);
        }
        return $url->getURL();
    }

    /**
     * Access token を取得する.
     *
     * @param GuzzleHttp\Client $httpClient
     * @param OAuth2Client $objClient
     * @param string $code
     * @param string $state
     * @return array
     */
    public static function getAccessToken(GuzzleHttp\Client $httpClient, OAuth2Client $objClient, $code, $state = null)
    {
        // $client = new GuzzleHttp\Client([
        //     'verify' => Composer\CaBundle\CaBundle::getSystemCaRootBundlePath(),
        // ]);
        $params = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => self::getRedirectUri($objClient->short_name)
        ];
        if ($state !== null) {
            $params['state'] = $state;
        }
        if ($objClient->short_name != OAuth2Client::YAHOOJAPAN) {
            $params['client_id'] = $objClient->client_id;
            $params['client_secret'] = $objClient->client_secret;
        }

        try {
            $headers = [];
            if ($objClient->short_name == OAuth2Client::YAHOOJAPAN) {
                $headers = [
                    'Authorization' => 'Basic '.base64_encode($objClient->client_id.':'.$objClient->client_secret)
                ];
            }
            GC_Utils_Ex::gfPrintLog($objClient->token_endpoint.' にPOSTします '.var_export($headers, true).var_export($params, true));
            $response = $httpClient->request(
                'POST',
                $objClient->token_endpoint,
                [
                    'headers' => $headers,
                    'json' => $params,
                    'timeout' => 5,
                    'connect_timeout' => 5
                ]
            );

            return json_decode($response->getBody(), true);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * UserInfo を取得する.
     *
     * @param GuzzleHttp\Client $httpClient
     * @param OAuth2Client $objClient
     * @param string $access_token
     * @return array
     */
    public static function getUserInfo(GuzzleHttp\Client $httpClient, OAuth2Client $objClient, $access_token)
    {
        $headers = [
            'Authorization' => 'Bearer '.$access_token
        ];

        $response = $httpClient->request('GET', $objClient->userinfo_endpoint,
                                 [
                                     'headers' => $headers,
                                     'timeout' => 5,
                                     'connect_timeout' => 5
                                 ]);

        $userinfo = json_decode($response->getBody(), true);
        GC_Utils_Ex::gfPrintLog($objClient->userinfo_endpoint.': '.var_export($userinfo, true));
        return $userinfo;
    }

    
    public static function registerToken(array $arrToken)
    {
    }

    public static function registerUserInfo(array $arrUserInfo)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $arrExistUserInfo = $objQuery->getRow('*', 'dtb_oauth2_openid_userinfo', 'oauth2_client_id = ? AND customer_id = ?', [$arrUserInfo['oauth2_client_id'], $arrUserInfo['customer_id']]);
        $arrUserInfo['updated_at'] = 'CURRENT_TIMESTAMP';
        if (SC_Utils_Ex::isBlank($arrExistUserInfo)) {
            $r = $objQuery->insert('dtb_oauth2_openid_userinfo', $objQuery->extractOnlyColsOf('dtb_oauth2_openid_userinfo', $arrUserInfo));
            $objQuery->insert('dtb_oauth2_openid_userinfo_address',
                              [
                                  'oauth2_client_id' => $arrUserInfo['oauth2_client_id'],
                                  'customer_id' => $arrUserInfo['customer_id']
                              ]
            );
        } else {
            $objQuery->update('dtb_oauth2_openid_userinfo', $arrUserInfo, 'oauth2_client_id = ? AND customer_id = ?', [$arrUserInfo['oauth2_client_id'], $arrUserInfo['customer_id']]);
        }
        return $objQuery->getRow('*', 'dtb_oauth2_openid_userinfo', 'oauth2_client_id = ? AND customer_id = ?', [$arrUserInfo['oauth2_client_id'], $arrUserInfo['customer_id']]);
    }
}

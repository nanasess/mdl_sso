<?php

class LC_Page_Sso_AuthorizationCodeFlow extends LC_Page_AbstractSso
{

    /**
     * @var GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * @var OAuth2Client
     */
    protected $objClient;

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        if (!is_object($this->httpClient)) {
            $this->httpClient = new GuzzleHttp\Client([
                'verify' => Composer\CaBundle\CaBundle::getSystemCaRootBundlePath()
            ]);
        }
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
        parent::process();
    }

    /**
     * TODO リファクタリング
     */
    public function action()
    {
        parent::action();

        $this->objClient = SC_Helper_OAuth2::getOAuth2Client($this->short_name);

        if (SC_Utils_Ex::isBlank($_REQUEST['code'])) {
            GC_Utils_Ex::gfPrintLog('Authorization code が見つかりませんでした. code='.$_REQUEST['code'].' client='.$this->short_name);
            SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', false, 'Authorization code が見つかりませんでした');
            SC_Response_Ex::sendHttpStatus(404);
            SC_Response_Ex::actionExit();
        } else {
            $code = htmlspecialchars($_REQUEST['code'], ENT_QUOTES);
            GC_Utils_Ex::gfPrintLog('Authorization code が見つかったので、 Authorization code フローを開始します. code='.$code.' client='.$this->short_name);
            if (isset($_REQUEST['state'])) {
                if ($_SESSION['state'] != $_REQUEST['state']) {
                    $message = 'state が異なります SESSION[state]='.$_SESSION['state'].' REQUEST[state]='.$_REQUEST['state'].'client='.$this->short_name;
                    GC_Utils_Ex::gfPrintLog($message);
                    SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', false, $message);
                    SC_Response_Ex::sendHttpStatus(400);
                    SC_Response_Ex::actionExit();
                }
            }

            try {
                $token = SC_Helper_OAuth2::getAccessToken($this->httpClient, $this->objClient, $code, $_SESSION['state']);
                $arrToken = [
                    'oauth2_client_id' => $this->objClient->oauth2_client_id,
                    'customer_id' => null,
                    'access_token' => $token['access_token'],
                    'refresh_token' => $token['refresh_token'],
                    'expires_in' => $token['expires_in'],
                    'token_type' => $token['token_type'],
                    'id_token' => $token['id_token'],
                    'scope' => $token['scope'],
                    'create_date' => 'CURRENT_TIMESTAMP',
                    'update_date' => 'CURRENT_TIMESTAMP'
                ];
                GC_Utils_Ex::gfPrintLog('アクセストークンを取得しました '.print_r($arrToken, true));

                // TODO check id_token
                //$objQuery->insert('dtb_oauth2_token', $arrToken);
                // $userInfo = $client->post($arrClient['token_endpoint'], array(), $params)->json();
                $userInfo = SC_Helper_OAuth2::getUserInfo($this->httpClient, $this->objClient, $token['access_token']);
                $arrUserInfo = [];
                switch ($this->short_name) {
                    case 'AMZN':
                        $arrUserInfo = [
                            'sub' => $userInfo['user_id'],
                            'name' => $userInfo['name'],
                            'email' => $userInfo['email'],
                            'postal_code' => $userInfo['postal_code']
                        ];
                        break;
                    case 'FB':
                        // TODO 書き換えたい https://developers.facebook.com/docs/php/howto/example_facebook_login
                        $fb = new Facebook\Facebook(
                            [
                                'app_id' => $arrClient['client_id'],
                                'app_secret' => $arrClient['client_secret'],
                                'default_graph_version' => 'v2.8',
                            ]
                        );
                        $response = $fb->get('/me?fields=id,name,email', $token['access_token']);
                        $userInfo = $response->getGraphUser();

                        $arrUserInfo = [
                            'sub' => $userInfo->getId(),
                            'name' => $userInfo->getName(),
                            'email' => $userInfo->getEmail()
                        ];
                        break;
                    case 'L':
                        $arrUserInfo = [
                            'sub' => $userInfo['userId'],
                            'name' => $userInfo['displayName'],
                            'picture' => $userInfo['pictureUrl']
                        ];
                        break;
                    case 'G':
                        $arrUserInfo = [
                            'sub' => $userInfo['sub'],
                            'name' => $userInfo['name'],
                            'email' => $userInfo['email']
                        ];
                        break;
                    case 'YJ':
                        $arrUserInfo = [
                            'sub' => $userInfo['user_id'],
                            'name' => $userInfo['name'],
                            'email' => $userInfo['email']
                        ];
                        break;
                    case 'DUMMY': // testing only
                        $arrUserInfo = [
                            'sub' => $userInfo['user_id'],
                            'name' => $userInfo['name'],
                            'email' => $userInfo['email'],
                            'postal_code' => $userInfo['postal_code']
                        ];

                        break;
                    default:
                        GC_Utils_Ex::gfPrintLog('SSO not found. '.$this->short_name);
                        SC_Response_Ex::actionExit();
                }
                GC_Utils_Ex::gfPrintLog('UserInfo を取得しました '.print_r($arrUserInfo, true));
                $objQuery = SC_Query_Ex::getSingletonInstance();
                GC_Utils_Ex::gfPrintLog('oauth2_client_id: '.$this->objClient->oauth2_client_id.' sub:'.$arrUserInfo['sub']);
                // $userInfo = $objQuery->getRow('*', 'dtb_oauth2_openid_userinfo', 'oauth2_client_id = ? AND sub = ?',
                //                               [$this->objClient->oauth2_client_id, $arrUserInfo['sub']]);
                // GC_Utils_Ex::gfPrintLog(print_r($userInfo, true));

                $arrUserInfo['oauth2_client_id'] = $this->objClient->oauth2_client_id;
                $arrCustomer = $objQuery->getRow('*', 'dtb_customer',
                                                 'customer_id = (SELECT customer_id FROM dtb_oauth2_openid_userinfo WHERE oauth2_client_id = ? AND sub = ?)',
                                                 [$this->objClient->oauth2_client_id, $arrUserInfo['sub']]);
                if (!SC_Utils_Ex::isBlank($arrCustomer)) {
                    GC_Utils_Ex::gfPrintLog('Customer が存在するためログインします customer_id='.$arrCustomer['customer_id']);
                    // login
                    $objCustomer = new SC_Customer_Ex();
                    $objCustomer->setLogin($arrCustomer['email']);
                    $objCustomer->setOAuth2ClientId($this->objClient->oauth2_client_id);
                    $arrUserInfo['customer_id'] = $arrCustomer['customer_id'];
                    $_SESSION['token']['customer_id'] = $arrCustomer['customer_id'];
                    SC_Helper_OAuth2::registerToken($_SESSION['token']);
                    unset($_SESSION['state']);
                    unset($_SESSION['token']);
                    SC_Helper_OAuth2::registerUserInfo($arrUserInfo);

                    SC_Response_Ex::sendRedirect('/');
                    SC_Response_Ex::actionExit();
                } else {
                    GC_Utils_Ex::gfPrintLog('Customer が存在しないため、登録画面に遷移します '.print_r($arrToken, true));
                    // register Customer
                    $_SESSION['token'] = $arrToken;
                    $_SESSION['userinfo'] = $arrUserInfo; // SESSION に保存しておいてリダイレクト後に登録する
                    unset($_SESSION['state']);
                    SC_Response_Ex::sendRedirectFromUrlPath('sso/'.$this->objClient->short_name.'/register');
                    SC_Response_Ex::actionExit();
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
                GC_Utils_Ex::gfPrintLog($message);
                SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', false, $message);
                SC_Response_Ex::actionExit();
            }
        }
    }

    public function setHttpClient(GuzzleHttp\Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }
}

<?php

class SC_Customer_Sso_Ex extends SC_Customer
{
    /** @var GuzzleHttp\Client */
    private $httpClient;

    public function isLoginSuccess($dont_check_email_mobile = false)
    {
        $oauth2_client_id = $this->getOAuth2ClientId();
        $customer_id = $this->getValue('customer_id');
        if (SC_Utils_Ex::isBlank($oauth2_client_id)) {
            return parent::isLoginSuccess($dont_check_email_mobile);
        }
        $objToken = SC_Helper_OAuth2::getStoredToken($oauth2_client_id, $customer_id);
        if (is_object($objToken)) {
            if ($objToken->isExpired()) {
                GC_Utils_Ex::gfPrintLog('access_token is expired: customer_id '.$customer_id);
                if ($objToken->refresh_token !== null) {
                    $objClient = SC_Helper_OAuth2::getOAuth2ClientById($oauth2_client_id);
                    try {
                        GC_Utils_Ex::gfPrintLog('refresh access_token: customer_id '.$customer_id);
                        $token = SC_Helper_OAuth2::refreshAccessToken($this->getHttpClient(), $objClient, $objToken->refresh_token);
                        $token['oauth2_client_id'] = $oauth2_client_id;
                        $token['customer_id'] = $customer_id;
                        SC_Helper_OAuth2::registerToken($token);

                        return parent::isLoginSuccess($dont_check_email_mobile);
                    } catch (Exception $e) {
                        return false;
                    }
                }
            } else {
                return parent::isLoginSuccess($dont_check_email_mobile);
            }
        }

        return false;
    }

    /**
     * @param int $oauth2_client_id
     */
    public function setOAuth2ClientId($oauth2_client_id)
    {
        $this->setValue('oauth2_client_id', $oauth2_client_id);
    }

    /**
     * @return int
     */
    public function getOAuth2ClientId()
    {
        return $this->getValue('oauth2_client_id');
    }

    /**
     * @param GuzzleHttp\Client $httpClient
     */
    public function setHttpClient(GuzzleHttp\Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return GuzzleHttp\Client
     */
    public function getHttpClient()
    {
        if (!is_object($this->httpClient)) {
            $this->httpClient = new GuzzleHttp\Client([
                'verify' => Composer\CaBundle\CaBundle::getSystemCaRootBundlePath()
            ]);
        }

        return $this->httpClient;
    }
}

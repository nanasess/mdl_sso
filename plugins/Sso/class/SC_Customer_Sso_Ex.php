<?php

class SC_Customer_Sso_Ex extends SC_Customer
{
    public function isLoginSuccess($dont_check_email_mobile = false)
    {
        $oauth2_client_id = $this->getOAuth2ClientId();
        if (SC_Utils_Ex::isBlank($oauth2_client_id)) {
            return parent::isLoginSuccess($dont_check_email_mobile);
        }
        $objToken = SC_Helper_OAuth2::getStoredToken($oauth2_client_id, $this->getValue('customer_id'));
        if (is_object($objToken)) {
            if ($objToken->isExpired()) {
                // TODO refresh を試みる
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
}

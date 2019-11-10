<?php

class SC_Customer_Sso_Ex extends SC_Customer
{
    public function isLoginSuccess($dont_check_email_mobile = false)
    {
        return false;
    }
}

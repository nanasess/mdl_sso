<?php

class LC_Page_Sso_CustomerRegister_Complete extends LC_Page_AbstractSso
{

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->httpCacheControl('nocache');
        $this->setTemplate(realpath(__DIR__.'/../../templates/default/sso/complete.tpl'));
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
    public function action()
    {
        unset($_SESSION['registered_customer_id']);
        unset($_SESSION['userinfo']);
    }
}

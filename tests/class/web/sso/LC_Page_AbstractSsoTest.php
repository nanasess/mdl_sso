<?php

class LC_Page_AbstractSsoTest extends Common_TestCase
{
    /**
     * @var LC_Page_AbstractSso
     */
    protected $objPage;

    public function testGetInstance()
    {
        $this->objPage = new LC_Page_Sso_Dummy();
        $this->objPage->init();
        $this->assertTrue(is_object($this->objPage));
    }
}

class LC_Page_Sso_Dummy extends LC_Page_AbstractSso
{
    
}

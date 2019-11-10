<?php

class SC_Customer_Sso_ExTest extends Common_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testGetInstance()
    {
        $objCustomer = new SC_Customer_Ex();
        $this->assertFalse($objCustomer->isLoginSuccess());
    }
}

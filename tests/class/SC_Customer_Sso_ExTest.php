<?php

class SC_Customer_Sso_ExTest extends Common_TestCase
{
    /** @var Faker\Generator $faker */
    protected $faker;
    /** @var FixtureGenerator */
    protected $objGenerator;
    /**
     * @var OAuth2Client
     */
    private $objClient;

    public function setUp()
    {
        parent::setUp();
        $this->faker = Faker\Factory::create('ja_JP');
        $this->objGenerator = new FixtureGenerator();

        $arrClient = [
            'oauth2_client_id' => PHP_INT_MAX,
            'short_name' => 'DUMMY',
            'client_id' => 'oauth2_client',
            'client_secret' => 'secret',
            'app_name' => 'dummy apps',
            'authorize_endpoint' => 'http://localhost:8085/sso/DUMMY/authorize',
            'token_endpoint' => 'http://localhost:8085/sso/DUMMY/token',
            'userinfo_endpoint' => 'http://localhost:8085/sso/DUMMY/userinfo',
            'scope' => 'profile'
        ];
        $this->objQuery->insert('dtb_oauth2_client', $arrClient);
        $this->objClient = new OAuth2Client($arrClient);
    }

    public function testIsLoginLocalCustomer()
    {
        $email = $this->faker->safeEmail;
        $customer_id = $this->objGenerator->createCustomer($email);
        $objCustomer = new SC_Customer_Ex();
        $objCustomer->setLogin($email);
        $this->assertTrue($objCustomer->isLoginSuccess());
    }

    public function testIsLoginOAuth2CustomerWithTokenNotFound()
    {
        $email = $this->faker->safeEmail;
        $customer_id = $this->objGenerator->createCustomer($email);
        $objCustomer = new SC_Customer_Ex();
        $objCustomer->setOAuth2ClientId($this->objClient->oauth2_client_id);

        $this->assertFalse($objCustomer->isLoginSuccess());
    }

    public function testIsLoginOAuth2Customer()
    {
        $email = $this->faker->safeEmail;
        $customer_id = $this->objGenerator->createCustomer($email);
        $objCustomer = new SC_Customer_Ex();
        $objCustomer->setLogin($email);
        $objCustomer->setOAuth2ClientId($this->objClient->oauth2_client_id);

        $arrToken = [
            'oauth2_client_id' => $this->objClient->oauth2_client_id,
            'customer_id' => $customer_id,
            'access_token' => $this->faker->uuid,
            'refresh_token' => $this->faker->uuid,
            'token_type' => 'Bearer',
            'expires_in' => 60 * 60 * 24, // XXX timezone
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
            'scope' => 'profile openid'
        ];
        SC_Helper_OAuth2::registerToken($arrToken);

        $this->assertTrue($objCustomer->isLoginSuccess());
    }
}

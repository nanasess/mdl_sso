<?php

class SC_Helper_OAuth2Test extends Common_TestCase
{
    /**
     * @var OAuth2Client
     */
    private $objClient;

    private $faker;

    protected function setUp()
    {
        $this->markTestIncomplete('Not implemented');
        parent::setUp();
        $arrClient = [
            'oauth2_client_id' => PHP_INT_SIZE,
            'short_name' => 'DUMMY',
            'client_id' => 'oauth2_client',
            'client_secret' => 'secret',
            'app_name' => 'dummy apps',
            'authorize_endpoint' => 'http://localhost:8085/test/sso/endpoint.php?type=authorize',
            'token_endpoint' => 'http://localhost:8085/test/sso/endpoint.php?type=token',
            'userinfo_endpoint' => 'http://localhost:8085/test/sso/endpoint.php?type=userinfo',
            'scope' => 'profile'
        ];
        $this->objClient = new OAuth2Client($arrClient);

        $this->faker = Faker\Factory::create('ja_JP');
    }

    public function testValidateShortName()
    {
        $actual = SC_Helper_OAuth2::validateShortName(OAuth2Client::AMAZON);
        $this->assertTrue($actual);
    }

    public function testValidateShortNameWithNotFound()
    {
        $actual = SC_Helper_OAuth2::validateShortName('AAA');
        $this->assertFalse($actual);
    }

    public function testGetOAuth2Client()
    {
        $actual = SC_Helper_OAuth2::getOAuth2Client(OAuth2Client::AMAZON);
        $this->assertInstanceOf('OAuth2Client', $actual);
    }

    public function testGetRedirectUri()
    {
        $expected = HTTPS_URL.'sso/'.OAuth2Client::AMAZON.'/redirect';
        $actual = SC_Helper_OAuth2::getRedirectUri(OAuth2Client::AMAZON);
        $this->assertEquals($expected, $actual);
    }

    public function testGetAuthorizationRequestUri()
    {
        $state = 'state';
        $expected = 'https?://.*/*.response_type=code&client_id=.*&redirect_uri=.*&scope=.*&state='.$state;
        $actual = SC_Helper_OAuth2::getAuthorizationRequestUri($this->objClient, $state);
        $this->assertRegExp('@'.$expected.'@', $actual);
    }

    public function testGetAccessToken()
    {
        $access_token = json_encode(
                    [
                        'access_token' => 'token',
                        'refresh_token' => 'refresh',
                        'expires_in' => 3600,
                        'token_type' => 'Bearer',
                        'scope' => $this->objClient->scope
                    ]);
        $body = GuzzleHttp\Stream\Stream::factory($access_token);
        $mock = new GuzzleHttp\Subscriber\Mock(
            [
                new GuzzleHttp\Message\Response(200, ['Content-Type' => 'application/json'], $body)
            ]
        );
        $mockClient = new GuzzleHttp\Client([
            'verify' => Composer\CaBundle\CaBundle::getSystemCaRootBundlePath(),
        ]);
        $mockClient->getEmitter()->attach($mock);

        $expected = json_decode($access_token, true);
        $actual = SC_Helper_OAuth2::getAccessToken($mockClient, $this->objClient, 'authorization_code', 'state');
        $this->assertEquals($expected, $actual);
    }

    public function testGetUserInfo()
    {
        $userinfo = json_encode(
            [
                'user_id' => 'user_id',
                'name' => 'name',
                'email' => 'email@example.com',
                'postal_code' => '9993333'
            ]
        );
        $access_token = 'access_token';
        $body = GuzzleHttp\Stream\Stream::factory($userinfo);
        $mock = new GuzzleHttp\Subscriber\Mock(
            [
                new GuzzleHttp\Message\Response(
                    200,
                    [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer '.$access_token
                    ],
                    $body
                )
            ]
        );
        $mockClient = new GuzzleHttp\Client([
            'verify' => Composer\CaBundle\CaBundle::getSystemCaRootBundlePath(),
        ]);
        $mockClient->getEmitter()->attach($mock);

        $expected = json_decode($userinfo, true);
        $actual = SC_Helper_OAuth2::getUserInfo($mockClient, $this->objClient, $access_token);
        $this->assertEquals($expected, $actual);
    }

    public function testRegisterUserInfo()
    {
        $this->markTestIncomplete();
        $objClient = SC_Helper_OAuth2::getOAuth2Client('DUMMY');
        $arrUserInfo = [
            'oauth2_client_id' => $objClient->oauth2_client_id,
            'customer_id' => $this->faker->randomNumber(),
            'sub' => 'user_id',
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'postal_code' => $this->faker->postcode
        ];

        $actual = SC_Helper_OAuth2::registerUserInfo($arrUserInfo);
        $this->assertNotNull($actual['updated_at']);
    }

    public function registerCustomer()
    {}
}

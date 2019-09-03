<?php

class SC_Helper_OAuth2Test extends Common_TestCase
{
    /**
     * @var OAuth2Client
     */
    private $objClient;

    /** @var Faker\Generator */
    private $faker;

    /** @var FixtureGenerator */
    private $objGenerator;

    /** @var string */
    const CLIENT_NAME = 'DUMMY';

    protected function setUp()
    {
        parent::setUp();
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

        $this->faker = Faker\Factory::create('ja_JP');
        $this->objGenerator = new FixtureGenerator($this->objQuery);
    }

    public function testValidateShortName()
    {
        $actual = SC_Helper_OAuth2::validateShortName(self::CLIENT_NAME);
        $this->assertTrue($actual);
    }

    public function testValidateShortNameWithNotFound()
    {
        $actual = SC_Helper_OAuth2::validateShortName('AAA');
        $this->assertFalse($actual);
    }

    public function testGetOAuth2Client()
    {
        $actual = SC_Helper_OAuth2::getOAuth2Client(self::CLIENT_NAME);
        $this->assertInstanceOf('OAuth2Client', $actual);
    }

    public function testGetRedirectUri()
    {
        $expected = HTTPS_URL.'sso/'.self::CLIENT_NAME.'/redirect';
        $actual = SC_Helper_OAuth2::getRedirectUri(self::CLIENT_NAME);
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
        $mock = new GuzzleHttp\Handler\MockHandler([
            new GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], $access_token)
        ]);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        $mockClient = new GuzzleHttp\Client(['handler' => $handler]);

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
        $mock = new GuzzleHttp\Handler\MockHandler([
            new GuzzleHttp\Psr7\Response(
                200,
                [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$access_token
                ], $userinfo)
        ]);
        $handler = GuzzleHttp\HandlerStack::create($mock);
        $mockClient = new GuzzleHttp\Client(['handler' => $handler]);

        $expected = json_decode($userinfo, true);
        $actual = SC_Helper_OAuth2::getUserInfo($mockClient, $this->objClient, $access_token);
        $this->assertEquals($expected, $actual);
    }

    public function testGetAddressByZipCode()
    {
        $httpClient = new GuzzleHttp\Client([
            'verify' => Composer\CaBundle\CaBundle::getSystemCaRootBundlePath()
        ]);
        $this->expected = [
            'pref_id' => '23',
            'pref' => '愛知県',
            'addr01' => '西尾市',
            'addr02' => '一色町治明'
        ];
        $this->actual = SC_Helper_OAuth2::getAddressByZipcode($httpClient, '444', '0426');
        $this->verify();
    }

    public function testGetAddressByZipCodeWithEmpty()
    {
        $httpClient = new GuzzleHttp\Client([
            'verify' => Composer\CaBundle\CaBundle::getSystemCaRootBundlePath()
        ]);
        $this->expected = [];
        $this->actual = SC_Helper_OAuth2::getAddressByZipcode($httpClient, '', '0426');
        $this->verify();
    }

    public function testRegisterUserInfo()
    {
        $customer_id = $this->objGenerator->createCustomer();
        $arrCustomer = $this->objQuery->getRow('*', 'dtb_customer', 'del_flg = 0 AND status = 2 AND customer_id = ?', [$customer_id]);
        $arrUserInfo = [
            'oauth2_client_id' => '999999',
            'customer_id' => $customer_id,
            'sub' => $this->faker->word,
            'name' => $arrCustomer['name01'],
            'email' => $arrCustomer['email']
        ];
        $this->actual = SC_Helper_OAuth2::registerUserInfo($arrUserInfo);
        $this->expected = $arrUserInfo;
        foreach (array_keys($arrUserInfo) as $key) {
            $this->assertEquals($this->expected[$key], $this->actual[$key]);
        }
    }
}

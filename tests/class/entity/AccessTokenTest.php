<?php

class AccessTokenTest extends Common_TestCase
{
    /** @var array */
    protected $token = [];
    /** @var Faker\Generator */
    private $faker;

    public function setUp()
    {
        parent::setUp();
        $this->faker = Faker\Factory::create('ja_JP');
        $this->token = [
            'oauth2_client_id' => $this->faker->randomNumber(),
            'customer_id' => $this->faker->randomNumber(),
            'access_token' => $this->faker->uuid,
            'refresh_token' => $this->faker->uuid,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'create_date' => date('Y-m-d H:i:s'),
            'update_date' => date('Y-m-d H:i:s'),
            'scope' => 'profile openid'
        ];
    }

    public function testCreateInstance()
    {
        $objToken = new AccessToken($this->token);

        $this->assertEquals($this->token['create_date'], $objToken->getCreateDate()->format('Y-m-d H:i:s'));
    }

    public function testGetExpire()
    {
        $this->token['expires_in'] = 100;
        $now = new DateTime();
        $this->token['update_date'] = $now->format('Y-m-d H:i:s');
        $expected = $now->add(new DateInterval('PT100S'));
        $objToken = new AccessToken($this->token);

        $actual = $objToken->getExpire();
        $this->assertEquals($expected->format('Y-m-d H:i:s'), $actual->format('Y-m-d H:i:s'));
    }

    public function testGetExpireWithNull()
    {
        $this->token['create_date'] = null;
        $this->token['update_date'] = null;
        $objToken = new AccessToken($this->token);

        $actual = $objToken->getExpire();
        $this->assertNull($objToken->getCreateDate());
        $this->assertNull($objToken->getUpdateDate());
        $this->assertEquals('0', $actual->format('U'));
    }

    public function testIsExpired()
    {
        $this->token['expires_in'] = 10;
        $this->token['update_date'] = new DateTime('-1 minutes');
        $objToken = new AccessToken($this->token);
        $this->assertTrue($objToken->isExpired());
    }

    public function testIsNotExpired()
    {
        $this->token['expires_in'] = 10;
        $this->token['update_date'] = new DateTime();
        $objToken = new AccessToken($this->token);
        $this->assertFalse($objToken->isExpired());
    }
}

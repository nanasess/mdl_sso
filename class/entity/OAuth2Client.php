<?php

class OAuth2Client extends AbstractEntity
{
    const AMAZON = 'AMZN';
    const FACEBOOK = 'FB';
    const LINE = 'L';
    const GOOGLE = 'G';
    const YAHOOJAPAN = 'YJ';
    const TWITTER = 'T';

    /**
     * @var int
     */
    public $oauth2_client_id;

    /**
     * @var string
     */
    public $short_name;

    /**
     * @var string
     */
    public $client_id;

    /**
     * @var string
     */
    public $client_secret;

    /**
     * @var string
     */
    public $app_name;

    /**
     * @var string
     */
    public $authorize_endpoint;

    /**
     * @var string
     */
    public $token_endpoint;

    /**
     * @var string
     */
    public $userinfo_endpoint;

    /**
     * @var DateTime
     */
    public $create_date;

    /**
     * @var DateTime
     */
    public $update_date;

    /**
     * @var int
     */
    public $del_flg;

    /**
     * @var string
     */
    public $scope;

    public function __construct(array $properties = [])
    {
        self::setPropertiesFromArray($properties);
    }
}

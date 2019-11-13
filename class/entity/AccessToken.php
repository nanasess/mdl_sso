<?php

class AccessToken extends AbstractEntity
{
    /** @var int */
    public $oauth2_client_id;
    /** @var int */
    public $customer_id;
    /** @var string */
    public $access_token;
    /** @var string */
    public $refresh_token;
    /** @var string */
    public $token_type;
    /** @var string */
    public $id_token;
    /** @var int */
    public $expires_in;
    /**
     * @var DateTime
     */
    private $create_date;
    /**
     * @var DateTime
     */
    private $update_date;
    /** @var string */
    public $scope;

    public function __construct(array $properties = [])
    {
        self::setPropertiesFromArray($properties);
        if (!$this->create_date instanceof DateTime && strtotime($this->create_date) !== false) {
            $this->setCreateDate(new DateTime($this->create_date));
        }
        if (!$this->update_date instanceof DateTime && strtotime($this->update_date) !== false) {
            $this->setUpdateDate(new DateTime($this->update_date));
        }
    }

    public function setCreateDate(DateTime $create_date)
    {
        $this->create_date = $create_date;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    public function setUpdateDate(DateTime $update_date)
    {
        $this->update_date = $update_date;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

    /**
     * @return DateTime
     */
    public function getExpire()
    {
        if (!$this->getUpdateDate() instanceof DateTime) {
            return new DateTime(date('Y-m-d H:i:s', 0));
        }
        return $this->getUpdateDate()->add(new DateInterval('PT'.$this->expires_in.'S'));
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        return (new DateTime() > $this->getExpire());
    }
}

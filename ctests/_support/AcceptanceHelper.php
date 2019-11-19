<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class AcceptanceHelper extends \Codeception\Module
{
    public function deleteCustomer($email)
    {
        $Db = $this->getModule('Db');
        /** @var \PDO $dbh */
        $dbh = $Db->dbh;
        $stmt = $dbh->prepare('DELETE FROM dtb_customer WHERE email = :email');
        $stmt->bindValue(':email', $email, \PDO::PARAM_STR);
        return $stmt->execute();
    }
}

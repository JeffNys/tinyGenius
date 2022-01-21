<?php

namespace App\Model;

class UserManager extends AbstractManager
{
    /**
     *
     */
    const TABLE = 'user';

    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }
    
    public function selectAllOrdered(): array
    {
        $sql = "SELECT *
        FROM $this->table
        ORDER BY lastname DESC";
        return $this->pdo->query($sql)->fetchAll();
    }
}

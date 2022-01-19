<?php

namespace App\Model;

class LevelManager extends AbstractManager
{
    /**
     *
     */
    const TABLE = 'level';

    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }
    
}

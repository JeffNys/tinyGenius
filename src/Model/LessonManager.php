<?php

namespace App\Model;

class LessonManager extends AbstractManager
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

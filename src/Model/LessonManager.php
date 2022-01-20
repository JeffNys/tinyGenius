<?php

namespace App\Model;

class LessonManager extends AbstractManager
{
    /**
     *
     */
    const TABLE = 'lesson';

    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }
    
    public function findAllWithLevel(): array
    {
        $sql = "SELECT `lesson`.`id`, `lesson`.`name`, `lesson`.`description`, `level`.`level`, `lesson`.`logo`
        FROM $this->table
        JOIN `level` ON `lesson`.`level_id` = `level`.`id`";
        return $this->pdo->query($sql)->fetchAll();
    }
}

<?php

namespace App\Model;

class TeacherManager extends AbstractManager
{
    /**
     *
     */
    const TABLE = 'teacher';

    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }
    
    public function findAllWithUser(): array
    {
        $sql = "SELECT `teacher`.`id`, `teacher`.`title`, `teacher`.`description`, `user`.`firstname`, `user`.`lastname`, `user`.`role`, `teacher`.`image`
        FROM $this->table
        JOIN `user` ON `teacher`.`user_id` = `user`.`id`";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function findOneWithUser(int $id): array
    {
        $sql = "SELECT `teacher`.`id`, `teacher`.`title`, `teacher`.`description`, `user`.`firstname`, `user`.`lastname`, `user`.`role`, `teacher`.`image`, `user`.`birthday`, `user`.`email`, `user`.`validate`, `user`.`phone` 
        FROM $this->table
        JOIN `user` ON `teacher`.`user_id` = `user`.`id`
        WHERE `teacher`.`id` = :id";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch();
    }
}

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
        $sql = "SELECT `teacher`.`id`, `teacher`.`title`, `teacher`.`description`, `user`.`firstname`, `user`.`lastname`, `user`.`email`, `user`.`role`, `teacher`.`image`
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

    public function findLessonsforTeacher(int $id): array
    {
        $sql = "SELECT `t`.`id` ref, `o`.`id` offer_id, `l`.`id`, `l`.`name`, `l`.`description`, `l`.`logo`, `level`.`level`
        FROM `teacher` `t`
        JOIN `offer` `o` ON `o`.`teacher_id` = `t`.`id`
        JOIN `lesson` `l` ON `o`.`lesson_id` = `l`.`id`
        JOIN `level` ON `l`.`level_id` = `level`.`id`
        WHERE `t`.`id` = :id
        ORDER BY `level`.`level` ASC";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}

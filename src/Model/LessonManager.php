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

    public function findOneWithLevel(int $id): array
    {
        $sql = "SELECT `lesson`.`id`, `lesson`.`name`, `lesson`.`description`, `level`.`level`, `lesson`.`logo`
        FROM $this->table
        JOIN `level` ON `lesson`.`level_id` = `level`.`id`
        WHERE `lesson`.`id` = :id";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch();
    }

    public function findTeachersforLesson(int $id): array
    {
        $sql = "SELECT `t`.`id`, `t`.`title`, `u`.`firstname`, `u`.`lastname`, `u`.`email`, `o`.`id` offer_id, `l`.`id` ref, `l`.`name`, `l`.`description`, `l`.`logo`, `level`.`level`
        FROM `teacher` `t`
        JOIN `user` `u` ON `t`.`user_id` = `u`.`id`
        JOIN `offer` `o` ON `o`.`teacher_id` = `t`.`id`
        JOIN `lesson` `l` ON `o`.`lesson_id` = `l`.`id`
        JOIN `level` ON `l`.`level_id` = `level`.`id`
        WHERE `l`.`id` = :id
        ORDER BY `level`.`level` ASC";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}

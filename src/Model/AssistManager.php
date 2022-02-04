<?php

namespace App\Model;

class AssistManager extends AbstractManager
{
    /**
     *
     */
    const TABLE = 'assist';

    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public function findAllBetween(string $startDate, string $endDate): array
    {
        $sql = "SELECT *
        FROM `$this->table`
        WHERE `meet` BETWEEN :startDate AND :endDate
        ORDER BY `meet` DESC";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':startDate', $startDate, \PDO::PARAM_STR);
        $statement->bindValue(':endDate', $endDate, \PDO::PARAM_STR);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function findAllForTeacher(int $id): array
    {
        $sql = "SELECT `t_id`.`id` `teacher_user_id`, `u`.`id`, `t`.`id` `teacher_id`, `l`.`name` `lesson_name`, `level`.`level`, DATE_FORMAT(`a`.`meet`, '%d/%m/%Y')  `date_meet`, DATE_FORMAT(`a`.`meet`, '%H:%i') `hour_meet`, `u`.`firstname`, `u`.`lastname`, `u`.`birthday`, `u`.`email`, `u`.`phone`
        FROM `assist` `a`
        JOIN `offer` `o` ON `a`.`offer_id` = `o`.`id`
        JOIN `lesson` `l` ON `o`.`lesson_id` = `l`.`id`
        JOIN `level` ON `l`.`level_id` = `level`.`id`
        JOIN `teacher` `t` ON `o`.`teacher_id` = `t`.`id`
        JOIN `user` `t_id` ON `t`.`user_id` = `t_id`.`id`
        JOIN `user` `u` ON `a`.`user_id` = `u`.`id`
        WHERE `t_id`.`id` = :id AND `a`.`meet` >= DATE( NOW() )
        ORDER BY `a`.`meet` ASC";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function findAllForTeacherArchive(int $id): array
    {
        $sql = "SELECT `t_id`.`id` `teacher_user_id`, `u`.`id`, `t`.`id` `teacher_id`, `l`.`name` `lesson_name`, `level`.`level`, DATE_FORMAT(`a`.`meet`, '%d/%m/%Y')  `date_meet`, DATE_FORMAT(`a`.`meet`, '%H:%i') `hour_meet`, `u`.`firstname`, `u`.`lastname`, `u`.`birthday`, `u`.`email`, `u`.`phone`
        FROM `assist` `a`
        JOIN `offer` `o` ON `a`.`offer_id` = `o`.`id`
        JOIN `lesson` `l` ON `o`.`lesson_id` = `l`.`id`
        JOIN `level` ON `l`.`level_id` = `level`.`id`
        JOIN `teacher` `t` ON `o`.`teacher_id` = `t`.`id`
        JOIN `user` `t_id` ON `t`.`user_id` = `t_id`.`id`
        JOIN `user` `u` ON `a`.`user_id` = `u`.`id`
        WHERE `t_id`.`id` = :id AND `a`.`meet` <= DATE( NOW() )
        ORDER BY `a`.`meet` DESC";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function findAllForUser(int $id): array
    {
        $sql = "SELECT `t_id`.`id` `teacher_user_id`, `u`.`id`, `t`.`id` `teacher_id`, `l`.`name` `lesson_name`, `level`.`level`, DATE_FORMAT(`a`.`meet`, '%d/%m/%Y')  `date_meet`, DATE_FORMAT(`a`.`meet`, '%H:%i') `hour_meet`, `t`.`title`
        FROM `assist` `a`
        JOIN `offer` `o` ON `a`.`offer_id` = `o`.`id`
        JOIN `lesson` `l` ON `o`.`lesson_id` = `l`.`id`
        JOIN `level` ON `l`.`level_id` = `level`.`id`
        JOIN `teacher` `t` ON `o`.`teacher_id` = `t`.`id`
        JOIN `user` `t_id` ON `t`.`user_id` = `t_id`.`id`
        JOIN `user` `u` ON `a`.`user_id` = `u`.`id`
        WHERE `u`.`id` = :id AND `a`.`meet` >= DATE( NOW() )
        ORDER BY `a`.`meet` ASC";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function findAllForUserArchive(int $id): array
    {
        $sql = "SELECT `t_id`.`id` `teacher_user_id`, `u`.`id`, `t`.`id` `teacher_id`, `l`.`name` `lesson_name`, `level`.`level`, DATE_FORMAT(`a`.`meet`, '%d/%m/%Y')  `date_meet`, DATE_FORMAT(`a`.`meet`, '%H:%i') `hour_meet`,  `t`.`title`
        FROM `assist` `a`
        JOIN `offer` `o` ON `a`.`offer_id` = `o`.`id`
        JOIN `lesson` `l` ON `o`.`lesson_id` = `l`.`id`
        JOIN `level` ON `l`.`level_id` = `level`.`id`
        JOIN `teacher` `t` ON `o`.`teacher_id` = `t`.`id`
        JOIN `user` `t_id` ON `t`.`user_id` = `t_id`.`id`
        JOIN `user` `u` ON `a`.`user_id` = `u`.`id`
        WHERE `u`.`id` = :id AND `a`.`meet` <= DATE( NOW() )
        ORDER BY `a`.`meet` DESC";

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}

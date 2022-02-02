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
}

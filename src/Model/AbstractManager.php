<?php

namespace App\Model;

use App\Model\Connection;

/**
 * Abstract class handling default manager.
 */
abstract class AbstractManager
{
    /**
     * @var \PDO
     */
    protected $pdo; //connection variable

    /**
     * @var string
     */
    protected $table;
    /**
     * @var string
     */
    protected $className;


    /**
     * Initializes Manager Abstract class.
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
        $this->className = __NAMESPACE__ . '\\' . ucfirst($table);
        $this->pdo = (new Connection())->getPdoConnection();
    }

    /**
     * Get all row from database.
     *
     * @return array
     */
    public function selectAll(): array
    {
        return $this->pdo->query('SELECT * FROM ' . $this->table)->fetchAll();
    }

    /**
     * Get one row from database by ID.
     *
     * @param  int $id
     *
     * @return array
     */
    public function selectOneById(int $id)
    {
        // prepared request
        $statement = $this->pdo->prepare("SELECT * FROM $this->table WHERE id=:id");
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch();
    }

    /**
     * count all row from database
     * 
     * @return int
     */
    public function count(): int
    {
        $result = $this->pdo->query("SELECT COUNT(*) as count FROM $this->table")->fetch();
        return intval($result['count']);
    }

    public function findBy(array $criteriaArray): array
    {
        /**
         * goal is to find something with one or more criteria
         * for example $array = findBy(['id_level' => 1, 'name' => 'N*']);
         * to find all customer with name start by N
         */
        $fields = [];
        $values = [];

        // first step, we explode the table of criteria
        foreach ($criteriaArray as $field => $value) {
            // we look for create: SELECT * FROM table WHERE field1 = value1 AND field2 = value2 etc...
            $fields[] = "`$field` = ?";
            $values[] = $value;
        }
        // now, we transform the table in a string for SQL
        $criteriaList = implode(' AND ', $fields);

        // and now we can create the query for DataBase
        $sql = "SELECT * FROM `$this->table` WHERE $criteriaList";
        $query = $this->pdo->prepare($sql);
        // here we replace all the ? by all the values
        $query->execute($values);
        return $query->fetchAll();
    }

    /**
     * to create the object outside:
     * $modelTable = [
     *  "field1" => "value1",
     *  "field2" => "value2",
     * ];
     */
    public function create(array $modelTable): object
    {
        $fields = [];
        $inter = [];
        $values = [];

        // first step, we explode the table of criteria
        foreach ($modelTable as $field => $value) {
            // we look for create: (field1, field2, etc...) VALUES (value1, value2, etc...)
            if (null !== $value && '' != $value && 'db' != $field && 'table' != $field) {
                $fields[] = $field;
                $inter[] = "?";
                $values[] = $value;
            }
        }
        // now, we transform the table in some strings for SQL
        $fieldsList = implode(', ', $fields);
        $interList = implode(', ', $inter);
        // and now we can create the query for DataBase
        $sql = "INSERT INTO $this->table ($fieldsList) VALUES ($interList)";
        $query = $this->pdo->prepare($sql);
        // here we replace all the ? by all the values
        $query->execute($values);
        return $query;
    }

    public function edit(int $id, array $modelTable): object
    {
        $fields = [];
        $values = [];

        // first step, we explode the table of criteria
        foreach ($modelTable as $field => $value) {
            // we look for create: SET field1 = ?, field2 = ? etc... WHERE id = ?
            if (null !== $value && '' != $value && 'db' != $field && 'table' != $field) {
                $fields[] = "`$field` = ?";
                $values[] = $value;
            }
        }
        // the last ? must be the id
        $values[] = $id;
        // now, we transform the table in a string for SQL like that:
        // UPDATE service field1 = ?, field2 = ?, etc...
        $fieldsList = implode(', ', $fields);
        // and now we can create the query for DataBase
        $sql = "UPDATE `$this->table` SET $fieldsList WHERE `id` =  ?";
        $query = $this->pdo->prepare($sql);
        // here we replace all the ? by all the values
        $query->execute($values);
        return $query;
    }

    /**
     * it's in the name, it's for delete one row in a table with the id
     */
    public function delete(int $id)
    {
        // simple prepared query to delete one row, with the id
        $sql = "DELETE FROM `$this->table` WHERE `id` = ?";
        $query = $this->pdo->prepare($sql);
        $query->execute([$id]);
        return $query;
    }
}

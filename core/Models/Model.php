<?php

namespace Core\Models;

use Core\Database\Database;
use Exception;

class Model
{
    public array $protectedVars = ['table'];
    public string $table = '';
    public string $id = 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY';
    public string $created_at = 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP';
    public string $updated_at = 'DATETIME NULL ON UPDATE CURRENT_TIMESTAMP';

    protected function getTableName(): string
    {
        if ($this->table === '') {
            $class =  get_class($this);
            $class = explode('\\', $class);
            $class = end($class);
            $class = strtolower($class);
            $this->table = $class . 's';
        }

        return $this->table;
    }

    public function getColumns(): array
    {
        $columns = get_class_vars(get_class($this));
        unset($columns['protectedVars']);

        foreach ($columns as $key => $column) {
            if (in_array($key, $this->protectedVars)) {
                unset($columns[$key]);
            }
        }
        
        return $columns;
    }

    public function all(): bool|array|null
    {
        $exists = Database::tableExists($this->getTableName());
        if (!$exists) {
            Database::createTable($this->getTableName(), self::getColumns());
        }

        return Database::select($this->getTableName());
    }

    public function create(array $values = []): void
    {
        $exists = Database::tableExists($this->getTableName());
        if (!$exists) {
            Database::createTable($this->getTableName(), self::getColumns());
        }

        Database::insert($this->getTableName(), $values);
    }

    /**
     * @throws Exception
     */
    public function find(int $id, array $columns = ['*'])
    {
        $exists = Database::tableExists($this->getTableName());
        if (!$exists) {
            throw new Exception("Table {$this->getTableName()} does not exist");
        }

        return Database::find($id, $this->getTableName(), $columns);
    }

    public function update(int $id, array $values)
    {
        $exists = Database::tableExists($this->getTableName());
        if (!$exists) {
            throw new Exception("Table {$this->getTableName()} does not exist");
        }

        return Database::update($this->getTableName(), $values, $id);
    }

    public function delete(int $id = null)
    {
        // TODO: Implement delete() method.
    }

}
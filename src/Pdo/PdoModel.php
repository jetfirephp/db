<?php

namespace JetFire\Db\Pdo;


use JetFire\Db\ModelInterface;

class PdoModel extends PdoConstructor implements ModelInterface{

    public $table;

    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    public function repo()
    {
        // TODO: Implement repo() method.
    }

    public function em()
    {
        // TODO: Implement em() method.
    }

    public function query($query)
    {
        // TODO: Implement query() method.
    }

    public function queryBuilder()
    {
        // TODO: Implement queryBuilder() method.
    }

    public function all()
    {
        // TODO: Implement all() method.
    }

    public function find($id)
    {
        // TODO: Implement find() method.
    }

    public function sql($sql, $params = [])
    {
        // TODO: Implement sql() method.
    }

    public function select()
    {
        // TODO: Implement select() method.
    }

    public function where($key, $operator = null, $value = null, $boolean = "AND")
    {
        // TODO: Implement where() method.
    }

    public function orWhere($key, $operator = null, $value = null)
    {
        // TODO: Implement orWhere() method.
    }

    public function whereRaw($sql, $value = null)
    {
        // TODO: Implement whereRaw() method.
    }

    public function orderBy($value, $order = 'ASC')
    {
        // TODO: Implement orderBy() method.
    }

    public function take($value, $array = false)
    {
        // TODO: Implement take() method.
    }

    public function get($array = false)
    {
        // TODO: Implement get() method.
    }

    public function getArray($array = false)
    {
        // TODO: Implement getArray() method.
    }

    public function count()
    {
        // TODO: Implement count() method.
    }

    public function update($id = null, $contents = null)
    {
        // TODO: Implement update() method.
    }

    public function with($contents)
    {
        // TODO: Implement with() method.
    }

    public function set($contents)
    {
        // TODO: Implement set() method.
    }

    public function create($contents = null)
    {
        // TODO: Implement create() method.
    }

    public function save()
    {
        // TODO: Implement save() method.
    }

    public function watch($entity = null)
    {
        // TODO: Implement watch() method.
    }

    public function watchAndSave($entity = null)
    {
        // TODO: Implement watchAndSave() method.
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function remove($content)
    {
        // TODO: Implement remove() method.
    }

    public function destroy()
    {
        // TODO: Implement destroy() method.
    }

    public function callStatic($name, $args)
    {
        // TODO: Implement callStatic() method.
    }
}
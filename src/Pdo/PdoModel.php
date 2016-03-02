<?php

namespace JetFire\Db\Pdo;


use JetFire\Db\ModelInterface;

/**
 * Class PdoModel
 * @package JetFire\Db\Pdo
 */
class PdoModel extends PdoConstructor implements ModelInterface
{

    /**
     * @var
     */
    public $table;

    /**
     * @param $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     *
     */
    public function repo()
    {
        // TODO: Implement repo() method.
    }

    /**
     *
     */
    public function em()
    {
        // TODO: Implement em() method.
    }

    /**
     * @param $query
     */
    public function query($query)
    {
        // TODO: Implement query() method.
    }

    /**
     *
     */
    public function queryBuilder()
    {
        // TODO: Implement queryBuilder() method.
    }

    /**
     *
     */
    public function all()
    {
        // TODO: Implement all() method.
    }

    /**
     * @param $id
     */
    public function find($id)
    {
        // TODO: Implement find() method.
    }

    /**
     * @param $sql
     * @param array $params
     */
    public function sql($sql, $params = [])
    {
        // TODO: Implement sql() method.
    }

    /**
     *
     */
    public function select()
    {
        // TODO: Implement select() method.
    }

    /**
     * @param $key
     * @param null $operator
     * @param null $value
     * @param string $boolean
     */
    public function where($key, $operator = null, $value = null, $boolean = "AND")
    {
        // TODO: Implement where() method.
    }

    /**
     * @param $key
     * @param null $operator
     * @param null $value
     */
    public function orWhere($key, $operator = null, $value = null)
    {
        // TODO: Implement orWhere() method.
    }

    /**
     * @param $sql
     * @param null $value
     */
    public function whereRaw($sql, $value = null)
    {
        // TODO: Implement whereRaw() method.
    }

    /**
     * @param $value
     * @param string $order
     */
    public function orderBy($value, $order = 'ASC')
    {
        // TODO: Implement orderBy() method.
    }

    /**
     * @param $value
     * @param bool $array
     */
    public function take($value, $array = false)
    {
        // TODO: Implement take() method.
    }

    /**
     * @param bool $array
     */
    public function get($array = false)
    {
        // TODO: Implement get() method.
    }

    /**
     * @param bool $array
     */
    public function getArray($array = false)
    {
        // TODO: Implement getArray() method.
    }

    /**
     *
     */
    public function count()
    {
        // TODO: Implement count() method.
    }

    /**
     * @param null $id
     * @param null $contents
     */
    public function update($id = null, $contents = null)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param $contents
     */
    public function with($contents)
    {
        // TODO: Implement with() method.
    }

    /**
     * @param $contents
     */
    public function set($contents)
    {
        // TODO: Implement set() method.
    }

    /**
     * @param null $contents
     */
    public function create($contents = null)
    {
        // TODO: Implement create() method.
    }

    /**
     *
     */
    public function save()
    {
        // TODO: Implement save() method.
    }

    /**
     * @param null $entity
     */
    public function watch($entity = null)
    {
        // TODO: Implement watch() method.
    }

    /**
     * @param null $entity
     */
    public function watchAndSave($entity = null)
    {
        // TODO: Implement watchAndSave() method.
    }

    /**
     *
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param $content
     */
    public function remove($content)
    {
        // TODO: Implement remove() method.
    }

    /**
     *
     */
    public function destroy()
    {
        // TODO: Implement destroy() method.
    }

    /**
     * @param $name
     * @param $args
     */
    public function callStatic($name, $args)
    {
        // TODO: Implement callStatic() method.
    }

    /**
     * @return mixed
     */
    public function getOrm()
    {
        // TODO: Implement getOrm() method.
    }

}
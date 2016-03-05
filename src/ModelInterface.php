<?php

namespace JetFire\Db;


/**
 * Interface ModelInterface
 * @package JetFire\Db
 */
interface ModelInterface
{

    /**
     * @param $table
     * @return mixed
     */
    public function setTable($table);

    /**
     * @return mixed
     */
    public function getTable();

    /**
     * @return mixed
     */
    public function getOrm();

    /**
     * @return mixed
     */
    public function repo();

    /**
     * @param $sql
     * @param array $params
     * @return mixed
     */
    public function sql($sql, $params = []);

    /**
     * @return mixed
     */
    public function all();

    /**
     * @param $id
     * @return mixed
     */
    public function find($id);

    /**
     * @return mixed
     */
    public function select();

    /**
     * @param $key
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return mixed
     */
    public function where($key, $operator = null, $value = null, $boolean = "AND");

    /**
     * @param $key
     * @param null $operator
     * @param null $value
     * @return mixed
     */
    public function orWhere($key, $operator = null, $value = null);

    /**
     * @param $sql
     * @param null $value
     * @return mixed
     */
    public function whereRaw($sql, $value = null);

    /**
     * @param $value
     * @param string $order
     * @return mixed
     */
    public function orderBy($value, $order = 'ASC');

    /**
     * @param $limit
     * @param null $first
     * @param bool $single
     * @internal param bool $array
     * @return mixed
     */
    public function take($limit,$first = null,$single = false);

    /**
     * @param bool $single
     * @return mixed
     */
    public function get($single = false);

    /**
     * @return mixed
     */
    public function count();

    /**
     * @param int|string $id
     * @param null $contents
     * @return mixed
     */
    public function update($id, $contents = null);

    /**
     * @param $contents
     * @return mixed
     */
    public function with($contents);

    /**
     * @param $contents
     * @return mixed
     */
    public function set($contents);

    /**
     * @param null $contents
     * @return mixed
     */
    public function create($contents = null);

    /**
     * @return mixed
     */
    public function delete();

    /**
     * @return mixed
     */
    public function destroy();

    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    public function callStatic($name, $args);

} 
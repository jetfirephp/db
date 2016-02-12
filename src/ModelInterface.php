<?php

namespace JetFire\Dbal;


interface ModelInterface {

    public function setTable($table);

    public function repo();
    public function em();
    public function query($query);
    public function queryBuilder();

    public function all();
    public function find($id);
    public function sql($sql, $params = []);
    public function select();
    public function where($key, $operator = null, $value = null, $boolean = "AND");
    public function orWhere($key, $operator = null, $value = null);
    public function whereRaw($sql, $value = null);
    public function orderBy($value, $order = 'ASC');
    public function take($value,$array = false );
    public function get($array = false );
    public function getArray($array = false);
    public function count();

    public function update($id = null, $contents = null);
    public function with($contents);
    public function set($contents);

    public function create($contents = null );
    public function save();
    public function watch($entity = null);
    public function watchAndSave($entity = null);

    public function delete();
    public function remove($content);
    public function destroy();

    public function callStatic($name, $args);

} 
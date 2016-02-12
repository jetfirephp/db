<?php

namespace JetFire\Dbal;


class ModelTable {

    public static $orm;
    public static $instance = null;

    public static function init(ModelInterface $orm){
        self::$orm = $orm;
    }

    public static function table ($table = null)
    {
        if (self::$instance === null)
            self::$instance = new self;
        (!is_null($table))
            ? self::$orm->setTable($table)
            : self::$orm->setTable(get_called_class());
        return self::$instance;
    }

    public function repo()
    {
        return self::$orm->repo();
    }

    public function em()
    {
        return self::$orm->em();
    }

    public function query($query)
    {
        return self::$orm->query($query);
    }

    public function queryBuilder()
    {
        return self::$orm->queryBuilder();
    }


//|---------------------------------------------------------------------------------|
//| Reading method are managed here                                                 |
//|---------------------------------------------------------------------------------|

    public function all()
    {
        return self::$orm->all();
    }

    public function find($id)
    {
        return self::$orm->find($id);
    }

    public function sql($sql, $params = [])
    {
        return self::$orm->sql($sql, $params);
    }

    public function select()
    {
        call_user_func_array([self::$orm,'select'],func_get_args());
        return $this;
    }

    public function where($key, $operator = null, $value = null, $boolean = "AND")
    {
        self::$orm->where($key, $operator, $value, $boolean);
        return $this;
    }

    public function orWhere($key, $operator = null, $value = null)
    {
        self::$orm->orWhere($key, $operator, $value);
        return $this;
    }

    public function whereRaw($sql, $value = null)
    {
        self::$orm->whereRaw($sql, $value);
        return $this;
    }

    public function orderBy($value, $order = 'ASC'){
        return self::$orm->orderBy($value, $order );
    }

    public function take($value,$array = false){
        return self::$orm->take($value,$array);
    }


    public function get($array = false)
    {
        return self::$orm->get($array);
    }


    public function getArray($array = false)
    {
        return  self::$orm->getArray($array);
    }

    public function count(){
        return self::$orm->count();
    }

//|---------------------------------------------------------------------------------|
//| Update methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    public function update($id = null, $contents = null)
    {
        return  self::$orm->update($id,$contents);
    }

    public function with($contents)
    {
        return  self::$orm->with($contents);
    }

    public function set($contents)
    {
        return self::$orm->set($contents);
    }


//|---------------------------------------------------------------------------------|
//| Create methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    public function create($contents = null)
    {
        return self::$orm->create($contents);
    }

    public function save()
    {
        return self::$orm->save();
    }
    public function watch($entity = null)
    {
        return  self::$orm->watch($entity);
    }
    public function watchAndSave($entity = null)
    {
        return  self::$orm->watchAndSave($entity);
    }

//|---------------------------------------------------------------------------------|
//| Delete methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    public function delete()
    {
        return self::$orm->delete();
    }

    public function remove($content)
    {
        return self::$orm->remove($content);
    }

    public function destroy()
    {
        return  self::$orm->destroy();
    }
} 
<?php

namespace JetFire\Dbal;


class Model {

    public static $orm;
    public static $instance = null;

    public static function init(ModelInterface $orm){
        self::$orm = $orm;
    }

    public static function getInstance ()
    {
        if (self::$instance === null)
            self::$instance = new self;
        return self::$instance;
    }
    
    public static function repo()
    {
        self::$orm->setTable(get_called_class());
        return self::$orm->repo();
    }

    public static function em()
    {
        return self::$orm->em();
    }

    public static function query($query)
    {
        return self::$orm->query($query);
    }

    public static function queryBuilder()
    {
        return self::$orm->queryBuilder();
    }


//|---------------------------------------------------------------------------------|
//| Reading method are managed here                                                 |
//|---------------------------------------------------------------------------------|

    public static function all()
    {
        self::$orm->setTable(get_called_class());
        return self::$orm->all();
    }

    public static function find($id)
    {
        self::$orm->setTable(get_called_class());
        return self::$orm->find($id);
    }

    public static function sql($sql, $params = [])
    {
       return self::$orm->sql($sql, $params);
    }

    public static function select()
    {
        self::$orm->setTable(get_called_class());
        call_user_func_array([self::$orm,'select'],func_get_args());
        return self::getInstance();
    }

    public static function where($key, $operator = null, $value = null, $boolean = "AND")
    {
        self::$orm->setTable(get_called_class());
        self::$orm->where($key, $operator, $value, $boolean);
        return self::getInstance();
    }

    public static function orWhere($key, $operator = null, $value = null)
    {
        self::$orm->setTable(get_called_class());
        self::$orm->orWhere($key, $operator, $value);
        return self::getInstance();
    }

    public static function whereRaw($sql, $value = null)
    {
        self::$orm->whereRaw($sql, $value);
        return self::getInstance();
    }

    public static function orderBy($value, $order = 'ASC'){
        return self::$orm->orderBy($value, $order );
    }

    public static function take($value,$array = false){
        self::$orm->setTable(get_called_class());
        return self::$orm->take($value,$array);
    }


    public static function get($array = false)
    {
        self::$orm->setTable(get_called_class());
        return self::$orm->get($array);
    }


    public static function getArray($array = false)
    {
        self::$orm->setTable(get_called_class());
        return  self::$orm->getArray($array);
    }

    public static function count(){
        self::$orm->setTable(get_called_class());
        return self::$orm->count();
    }

//|---------------------------------------------------------------------------------|
//| Update methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    public static function update($id = null, $contents = null)
    {
        self::$orm->setTable(get_called_class());
        return  self::$orm->update($id,$contents);
    }

    public static function with($contents)
    {
        self::$orm->setTable(get_called_class());
        return  self::$orm->with($contents);
    }

    public static function set($contents)
    {
        self::$orm->setTable(get_called_class());
        return self::$orm->set($contents);
    }


//|---------------------------------------------------------------------------------|
//| Create methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    public static function create($contents = null)
    {
        self::$orm->setTable(get_called_class());
        return self::$orm->create($contents);
    }

    public static function save()
    {
        return self::$orm->save();
    }
    public static function watch($entity = null)
    {
        return  self::$orm->watch($entity);
    }
    public static function watchAndSave($entity = null)
    {
        return  self::$orm->watchAndSave($entity);
    }

//|---------------------------------------------------------------------------------|
//| Delete methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    public static function delete()
    {
        self::$orm->setTable(get_called_class());
        return self::$orm->delete();
    }

    public static function remove($content)
    {
        return self::$orm->remove($content);
    }

    public static function destroy()
    {
        self::$orm->setTable(get_called_class());
        return  self::$orm->destroy();
    }

} 
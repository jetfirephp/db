<?php

namespace JetFire\Db;


/**
 * Class Model
 * @package JetFire\Db
 */
class Model {

    /**
     * @var
     */
    public static $orm;
    /**
     * @var
     */
    public static $allOrm;
    /**
     * @var null
     */
    public static $instance = null;

    public static $provider = [];

    /**
     * @param ModelInterface $orm
     */
    public static function init(ModelInterface $orm){
        self::$orm = $orm;
    }

    public static function provide($provider = []){
        self::$provider = $provider;
    }

    public static function orm($name){
        if(!isset(self::$allOrm[$name]))
            self::$allOrm[$name] = call_user_func(self::$provider[$name]);
        self::$orm = self::$allOrm[$name];
        self::$orm->setTable(get_called_class());
        return self::getInstance();
    }

    /**
     * @return Model|null
     */
    public static function getInstance ()
    {
        if (self::$instance === null)
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @return mixed
     */
    public static function repo()
    {
        self::$orm->setTable(get_called_class());
        return self::$orm->repo();
    }

    /**
     * @return mixed
     */
    public static function em()
    {
        return self::$orm->em();
    }

    /**
     * @param $query
     * @return mixed
     */
    public static function query($query)
    {
        return self::$orm->query($query);
    }

    /**
     * @return mixed
     */
    public static function queryBuilder()
    {
        return self::$orm->queryBuilder();
    }


//|---------------------------------------------------------------------------------|
//| Reading method are managed here                                                 |
//|---------------------------------------------------------------------------------|

    /**
     * @return mixed
     */
    public static function all()
    {
        self::$orm->setTable(get_called_class());
        return self::$orm->all();
    }

    /**
     * @param $id
     * @return mixed
     */
    public static function find($id)
    {
        self::$orm->setTable(get_called_class());
        return self::$orm->find($id);
    }

    /**
     * @param $sql
     * @param array $params
     * @return mixed
     */
    public static function sql($sql, $params = [])
    {
       return self::$orm->sql($sql, $params);
    }

    /**
     * @return Model|null
     */
    public static function select()
    {
        self::$orm->setTable(get_called_class());
        call_user_func_array([self::$orm,'select'],func_get_args());
        return self::getInstance();
    }

    /**
     * @param $key
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return Model|null
     */
    public static function where($key, $operator = null, $value = null, $boolean = "AND")
    {
        self::$orm->setTable(get_called_class());
        self::$orm->where($key, $operator, $value, $boolean);
        return self::getInstance();
    }

    /**
     * @param $key
     * @param null $operator
     * @param null $value
     * @return Model|null
     */
    public static function orWhere($key, $operator = null, $value = null)
    {
        self::$orm->setTable(get_called_class());
        self::$orm->orWhere($key, $operator, $value);
        return self::getInstance();
    }

    /**
     * @param $sql
     * @param null $value
     * @return Model|null
     */
    public static function whereRaw($sql, $value = null)
    {
        self::$orm->whereRaw($sql, $value);
        return self::getInstance();
    }

    /**
     * @param $value
     * @param string $order
     * @return mixed
     */
    public static function orderBy($value, $order = 'ASC'){
        return self::$orm->orderBy($value, $order );
    }

    /**
     * @param $value
     * @param bool $array
     * @return mixed
     */
    public static function take($value,$array = false){
        self::$orm->setTable(get_called_class());
        return self::$orm->take($value,$array);
    }


    /**
     * @param bool $array
     * @return mixed
     */
    public static function get($array = false)
    {
        self::$orm->setTable(get_called_class());
        return self::$orm->get($array);
    }


    /**
     * @param bool $array
     * @return mixed
     */
    public static function getArray($array = false)
    {
        self::$orm->setTable(get_called_class());
        return  self::$orm->getArray($array);
    }

    /**
     * @return mixed
     */
    public static function count(){
        self::$orm->setTable(get_called_class());
        return self::$orm->count();
    }

//|---------------------------------------------------------------------------------|
//| Update methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    /**
     * @param null $id
     * @param null $contents
     * @return mixed
     */
    public static function update($id = null, $contents = null)
    {
        self::$orm->setTable(get_called_class());
        return  self::$orm->update($id,$contents);
    }

    /**
     * @param $contents
     * @return mixed
     */
    public static function with($contents)
    {
        self::$orm->setTable(get_called_class());
        return  self::$orm->with($contents);
    }

    /**
     * @param $contents
     * @return mixed
     */
    public static function set($contents)
    {
        self::$orm->setTable(get_called_class());
        return self::$orm->set($contents);
    }


//|---------------------------------------------------------------------------------|
//| Create methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    /**
     * @param null $contents
     * @return mixed
     */
    public static function create($contents = null)
    {
        self::$orm->setTable(get_called_class());
        return self::$orm->create($contents);
    }

    /**
     * @return mixed
     */
    public static function save()
    {
        return self::$orm->save();
    }

    /**
     * @param null $entity
     * @return mixed
     */
    public static function watch($entity = null)
    {
        return  self::$orm->watch($entity);
    }

    /**
     * @param null $entity
     * @return mixed
     */
    public static function watchAndSave($entity = null)
    {
        return  self::$orm->watchAndSave($entity);
    }

//|---------------------------------------------------------------------------------|
//| Delete methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    /**
     * @return mixed
     */
    public static function delete()
    {
        self::$orm->setTable(get_called_class());
        return self::$orm->delete();
    }

    /**
     * @param $content
     * @return mixed
     */
    public static function remove($content)
    {
        return self::$orm->remove($content);
    }

    /**
     * @return mixed
     */
    public static function destroy()
    {
        self::$orm->setTable(get_called_class());
        return self::$orm->destroy();
    }

    public static function __callStatic($name,$args){
        self::$orm->setTable(get_called_class());
        return self::$orm->callStatic($name,$args);
    }

} 
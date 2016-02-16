<?php

namespace JetFire\Db;


/**
 * Class ModelTable
 * @package JetFire\Db
 */
class ModelTable {

    /**
     * @var
     */
    public static $orm;
    /**
     * @var null
     */
    public static $instance = null;

    /**
     * @param ModelInterface $orm
     */
    public static function init(ModelInterface $orm){
        self::$orm = $orm;
    }

    /**
     * @param null $table
     * @return ModelTable|null
     */
    public static function table ($table = null)
    {
        if (self::$instance === null)
            self::$instance = new self;
        (!is_null($table))
            ? self::$orm->setTable($table)
            : self::$orm->setTable(get_called_class());
        return self::$instance;
    }

    /**
     * @return mixed
     */
    public function repo()
    {
        return self::$orm->repo();
    }

    /**
     * @return mixed
     */
    public function em()
    {
        return self::$orm->em();
    }

    /**
     * @param $query
     * @return mixed
     */
    public function query($query)
    {
        return self::$orm->query($query);
    }

    /**
     * @return mixed
     */
    public function queryBuilder()
    {
        return self::$orm->queryBuilder();
    }


//|---------------------------------------------------------------------------------|
//| Reading method are managed here                                                 |
//|---------------------------------------------------------------------------------|

    /**
     * @return mixed
     */
    public function all()
    {
        return self::$orm->all();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return self::$orm->find($id);
    }

    /**
     * @param $sql
     * @param array $params
     * @return mixed
     */
    public function sql($sql, $params = [])
    {
        return self::$orm->sql($sql, $params);
    }

    /**
     * @return $this
     */
    public function select()
    {
        call_user_func_array([self::$orm,'select'],func_get_args());
        return $this;
    }

    /**
     * @param $key
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return $this
     */
    public function where($key, $operator = null, $value = null, $boolean = "AND")
    {
        self::$orm->where($key, $operator, $value, $boolean);
        return $this;
    }

    /**
     * @param $key
     * @param null $operator
     * @param null $value
     * @return $this
     */
    public function orWhere($key, $operator = null, $value = null)
    {
        self::$orm->orWhere($key, $operator, $value);
        return $this;
    }

    /**
     * @param $sql
     * @param null $value
     * @return $this
     */
    public function whereRaw($sql, $value = null)
    {
        self::$orm->whereRaw($sql, $value);
        return $this;
    }

    /**
     * @param $value
     * @param string $order
     * @return mixed
     */
    public function orderBy($value, $order = 'ASC'){
        return self::$orm->orderBy($value, $order );
    }

    /**
     * @param $value
     * @param bool $array
     * @return mixed
     */
    public function take($value,$array = false){
        return self::$orm->take($value,$array);
    }


    /**
     * @param bool $array
     * @return mixed
     */
    public function get($array = false)
    {
        return self::$orm->get($array);
    }


    /**
     * @param bool $array
     * @return mixed
     */
    public function getArray($array = false)
    {
        return  self::$orm->getArray($array);
    }

    /**
     * @return mixed
     */
    public function count(){
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
    public function update($id = null, $contents = null)
    {
        return  self::$orm->update($id,$contents);
    }

    /**
     * @param $contents
     * @return mixed
     */
    public function with($contents)
    {
        return  self::$orm->with($contents);
    }

    /**
     * @param $contents
     * @return mixed
     */
    public function set($contents)
    {
        return self::$orm->set($contents);
    }


//|---------------------------------------------------------------------------------|
//| Create methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    /**
     * @param null $contents
     * @return mixed
     */
    public function create($contents = null)
    {
        return self::$orm->create($contents);
    }

    /**
     * @return mixed
     */
    public function save()
    {
        return self::$orm->save();
    }

    /**
     * @param null $entity
     * @return mixed
     */
    public function watch($entity = null)
    {
        return  self::$orm->watch($entity);
    }

    /**
     * @param null $entity
     * @return mixed
     */
    public function watchAndSave($entity = null)
    {
        return  self::$orm->watchAndSave($entity);
    }

//|---------------------------------------------------------------------------------|
//| Delete methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    /**
     * @return mixed
     */
    public function delete()
    {
        return self::$orm->delete();
    }

    /**
     * @param $content
     * @return mixed
     */
    public function remove($content)
    {
        return self::$orm->remove($content);
    }

    /**
     * @return mixed
     */
    public function destroy()
    {
        return  self::$orm->destroy();
    }
} 
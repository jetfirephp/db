<?php

namespace JetFire\Db;

/**
 * Class Model
 * @package JetFire\Db
 */
class Model
{

    /**
     * @var
     */
    public static $orm;
    /**
     * @var
     */
    public static $allOrm;
    /**
     * @var
     */
    public static $db;
    /**
     * @var
     */
    public static $default = [];
    /**
     * @var array
     */
    public static $provider = [];
    /**
     * @var
     */
    public static $class;
    /**
     * @var null
     */
    public static $instance = null;
    /**
     * @var
     */
    private static $keepLast;
    /**
     * @param ModelInterface $orm
     */
    public static function init(ModelInterface $orm)
    {
        self::$orm = $orm;
    }

    /**
     * @param array $provider
     * @param array $default
     * @param bool $keepLast
     */
    public static function provide($provider = [], $default = [],$keepLast = false)
    {
        self::$provider = $provider;
        reset($provider);
        self::$default['orm'] = (isset($default['orm'])) ? $default['orm'] : key($provider);
        self::$default['db'] = (isset($default['db'])) ? $default['db'] : 'default';
        self::$keepLast = $keepLast;
    }

    /**
     * @param $class
     * @return Model|null
     */
    public static function getInstance($class)
    {
        if (self::$class !== $class) {
            self::$class = $class;
            self::$instance = new self::$class;
        }
        return self::$instance;
    }

    /**
     * @param $name
     * @return Model|null
     */
    public static function orm($name)
    {
        if (!isset(self::$allOrm[$name]))
            self::$allOrm[$name] = call_user_func(self::$provider[$name]);
        self::$orm = self::$allOrm[$name];
        return self::getInstance(get_called_class());
    }

    /**
     * @param string $name
     * @return Model|null
     */
    public static function db($name){
        if (is_null(self::$orm))
            self::orm(self::$default['orm']);
        self::$db = self::$orm->setDb($name);
        return self::getInstance(get_called_class());
    }

    /**
     * @param $table
     * @return Model|null
     */
    public static function table($table)
    {
        if (is_null(self::$orm))
            self::orm(self::$default['orm']);
        if (is_null(self::$db))
            self::db(self::$default['db']);
        return self::getInstance($table);
    }

    /**
     * @return Model|null
     */
    public static function repo(){
        $repo = self::$orm->repo();
        return is_null($repo)?self::getInstance(get_called_class()):$repo;
    }

    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    public static function __callStatic($name, $args)
    {
        return self::call($name, $args);
    }

    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        return self::call($name, $args);
    }

    /**
     * @param $name
     * @param $args
     * @return
     */
    private static function call($name,$args){
        if(is_null(self::$class))
            self::$class = get_called_class();
        self::table(self::$class);
        self::$orm->setTable(self::$class);
        $call = self::$orm->callStatic($name, $args);
        if(!self::$keepLast) {
            self::$orm = null;
            self::$db = null;
        }
        return $call;
    }

} 
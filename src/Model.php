<?php

namespace JetFire\Db;


/**
 * Class Model
 * @package JetFire\Db
 */
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
    public static $defaultOrm;
    /**
     * @var
     */
    public static $allOrm;
    /**
     * @var null
     */
    public static $instance = null;

    /**
     * @var array
     */
    public static $provider = [];

    /**
     * @return Model|null
     */
    public static function getInstance()
    {
        if (self::$instance === null)
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param ModelInterface $orm
     */
    public static function init(ModelInterface $orm)
    {
        self::$orm = $orm;
    }

    /**
     * @param array $provider
     * @param null $default
     */
    public static function provide($provider = [], $default = null)
    {
        self::$provider = $provider;
        reset($provider);
        self::$defaultOrm = (!is_null($default)) ? $default : key($provider);
    }

    /**
     * @param $table
     * @return Model|null
     */
    public static function table($table)
    {
        if (is_null(self::$orm))
            self::orm(self::$defaultOrm);
        self::$orm->setTable($table);
        return self::getInstance();
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
        self::$orm->setTable(get_called_class());
        return self::getInstance();
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
     * @return mixed
     */
    private static function call($name, $args)
    {
        if (is_null(self::$orm))
            self::orm(self::$defaultOrm);
        if (is_null(self::$orm->getTable()))
            self::$orm->setTable(get_called_class());
        return self::$orm->callStatic($name, $args);
    }

} 
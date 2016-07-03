<?php

namespace JetFire\Db;

/**
 * Class Model
 * @package JetFire\Db
 * @method static|object all()
 * @method static|object find($id)
 * @method static|object select()
 * @method static|object where($key, $operator = null, $value = null, $boolean = "AND")
 * @method static|object orWhere($key, $operator = null, $value = null)
 * @method static|object whereRaw($sql, $value = null)
 * @method static|object get($single = false)
 * @method static|object take($limit,$first = null,$single = false)
 * @method static|object orderBy($value, $order = 'ASC')
 * @method static|object count()
 * @method static|object update($id, $contents = null)
 * @method static|object with($contents)
 * @method static|object set($contents)
 * @method static|object create($contents = null)
 * @method static|object delete()
 * @method static|object destroy()
 * @method static|object getOrm()
 * // Doctrine methods
 * @method static|\Doctrine\ORM\EntityManager em()
 * @method static|\Doctrine\ORM\QueryBuilder queryBuilder()
 * @method static|object save()
 * @method static|object watch()
 * @method static|object watchAndSave()
 * // RedBean methods
 * @method static remove()
 */
class Model
{

    /**
     * @var \JetFire\Db\Pdo\PdoModel|\JetFire\Db\Doctrine\DoctrineModel|\JetFire\Db\RedBean\RedBeanModel
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
     * @return Object|Model|null
     */
    public static function getInstance($class)
    {
        if (is_null(self::$instance) || self::$class !== $class) {
            self::$class = $class;
            self::$instance = new self::$class;
        }
        return self::$instance;
    }

    /**
     * @param $name
     * @return Object|Model|null
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
     * @return Object|Model|null
     */
    public static function db($name){
        if (is_null(self::$orm))
            self::orm(self::$default['orm']);
        self::$db = self::$orm->setDb($name);
        return self::getInstance(get_called_class());
    }

    /**
     * @param $table
     * @return Object|Model|null
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
     * @return Object|Model|null
     */
    public static function repo(){
        if(is_null(self::$class))
            self::$class = get_called_class();
        self::table(self::$class);
        self::$orm->setTable(self::$class);
        $repo = self::$orm->repo();
        if(!self::$keepLast) {
            self::$orm = null;
            self::$db = null;
        }
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
     * @return mixed
     */
    private static function call($name,$args){
        if(is_null(self::$class) || self::$class != get_called_class())
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
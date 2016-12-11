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
    public static $_orm;
    /**
     * @var
     */
    public static $_allOrm;
    /**
     * @var
     */
    public static $_db;
    /**
     * @var
     */
    public static $_default = [];
    /**
     * @var array
     */
    public static $_provider = [];
    /**
     * @var
     */
    public static $_class;
    /**
     * @var null
     */
    public static $_instance = null;
    /**
     * @var
     */
    private static $_keepLast;

    /**
     * @param ModelInterface $_orm
     */
    public static function init(ModelInterface $_orm)
    {
        self::$_orm = $_orm;
    }

    /**
     * @param array $_provider
     * @param array $_default
     * @param bool $_keepLast
     */
    public static function provide($_provider = [], $_default = [],$_keepLast = false)
    {
        self::$_provider = $_provider;
        reset($_provider);
        self::$_default['orm'] = (isset($_default['orm'])) ? $_default['orm'] : key($_provider);
        self::$_default['db'] = (isset($_default['db'])) ? $_default['db'] : 'default';
        self::$_keepLast = $_keepLast;
    }

    /**
     * @param $_class
     * @return Object|Model|null
     */
    public static function getInstance($_class)
    {
        if (is_null(self::$_instance) || self::$_class !== $_class) {
            self::$_class = $_class;
            self::$_instance = new self::$_class;
        }
        return self::$_instance;
    }

    /**
     * @param $name
     * @return Object|Model|null
     */
    public static function orm($name)
    {
        if (!isset(self::$_allOrm[$name]))
            self::$_allOrm[$name] = call_user_func(self::$_provider[$name]);
        self::$_orm = self::$_allOrm[$name];
        return self::getInstance(get_called_class());
    }

    /**
     * @param string $name
     * @return Object|Model|null
     */
    public static function db($name){
        if (is_null(self::$_orm))
            self::orm(self::$_default['orm']);
        self::$_db = self::$_orm->setDb($name);
        return self::getInstance(get_called_class());
    }

    /**
     * @param $table
     * @return Object|Model|null
     */
    public static function table($table)
    {
        if (is_null(self::$_orm))
            self::orm(self::$_default['orm']);
        if (is_null(self::$_db))
            self::db(self::$_default['db']);
        return self::getInstance($table);
    }

    /**
     * @return Object|Model|null
     */
    public static function repo(){
        if(is_null(self::$_class) || self::$_class != get_called_class())
            self::$_class = get_called_class();
        self::table(self::$_class);
        self::$_orm->setTable(self::$_class);
        $repo = self::$_orm->repo();
        if(!self::$_keepLast) {
            self::$_orm = null;
            self::$_db = null;
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
        if(is_null(self::$_class) || self::$_class != get_called_class())
            self::$_class = get_called_class();
        self::table(self::$_class);
        self::$_orm->setTable(self::$_class);
        $call = self::$_orm->callStatic($name, $args);
        if(!self::$_keepLast) {
            self::$_orm = null;
            self::$_db = null;
        }
        return $call;
    }

} 
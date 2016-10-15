<?php

namespace JetFire\Db\RedBean;

use JetFire\Db\DbConstructorInterface;
use RedBeanPHP\R;

/**
 * Class RedBeanConstructor
 * @package JetFire\Db\RedBean
 */
class RedBeanConstructor implements DbConstructorInterface
{

    /**
     * @var
     */
    protected $options;

    /**
     * @var array
     */
    protected $db;

    /**
     * @var bool
     */
    private $cache = false;

    /**
     * @param array $db
     * @throws \Exception
     */
    public function __construct($db = [])
    {
        $this->db = $db;
        $this->cache = (isset($db['dev']) && $db['dev']) ? true : false;
        foreach($this->db as $key => $db){
            if (!isset($db['user']) || !isset($db['pass']) || !isset($db['host']) || !isset($db['db']))
                throw new \Exception('Missing arguments for RedBean constructor');
            ($db['driver'] == 'sqlite')
                ? R::addDatabase($key,'sqlite:/tmp/dbfile.db')
                : R::addDatabase($key, $db['driver'] . ':host=' . $db['host'] . ';dbname=' . $db['db'], $db['user'], $db['pass']);
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    public function setDb($name)
    {
        $this->options = $this->db[$name];
        R::setAutoResolve(TRUE);
        R::selectDatabase($name);
        R::ext('xdispense', function ($type) {
            return R::getRedBean()->dispense($type);
        });
        if($this->cache) {
            R::useWriterCache(true);
            R::freeze(TRUE);
        }
        return $name;
    }

}
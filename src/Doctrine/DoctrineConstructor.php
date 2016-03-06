<?php

namespace JetFire\Db\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\Setup;
use InvalidArgumentException;
use Memcache;
use Memcached;
use Redis;

/**
 * Class DoctrineConstructor
 * @package JetFire\Db\Doctrine
 */
class DoctrineConstructor
{

    public $cacheDriver;
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var
     */
    protected $options;

    private $cacheProviders = [
        'Doctrine\Common\Cache\ArrayCache' => 'getCache',
        'Doctrine\Common\Cache\ApcCache' => 'getCache',
        'Doctrine\Common\Cache\XcacheCache' => 'getCache',
        'Doctrine\Common\Cache\MemcacheCache' => 'getMemcache',
        'Doctrine\Common\Cache\MemcachedCache' => 'getMemcached',
        'Doctrine\Common\Cache\RedisCache' => 'getRedis',
    ];

    /**
     * @param array $options
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function __construct($options = [])
    {
        $this->options = $options;
        $isDevMode = (isset($options['dev']) && $options['dev']) ? true : false;
        if (isset($options['db_url'])) {
            $dbParams = array(
                'url' => $options['db_url']
            );
        } else {
            if (!isset($options['driver']) || !isset($options['user']) || !isset($options['pass']) || !isset($options['host']) || !isset($options['db']))
                throw new \Exception('Missing arguments for doctrine constructor');
            $dbParams = array(
                'driver'   => ($options['driver'] === 'mysql') ? 'pdo_mysql' : $options['driver'],
                'user'     => $options['user'],
                'password' => $options['pass'],
                'host'     => $options['host'],
                'dbname'   => $options['db'],
                'charset'  => isset($options['charset']) ? $options['charset'] : 'utf8',
            );
        }
        $evm = new EventManager();
        if (isset($options['prefix'])) {
            $tablePrefix = new TablePrefix($options['prefix']);
            $evm->addEventListener(Events::loadClassMetadata, $tablePrefix);
        }
        $config = Setup::createAnnotationMetadataConfiguration($options['path'], $isDevMode);
        $this->em = EntityManager::create($dbParams, $config, $evm);
    }

    public function configCache($params = []){
        $config = new \Doctrine\ORM\Configuration();
        if(!isset($driver['use']))
            throw new InvalidArgumentException('Cache class is not defined');
        $this->cacheDriver = call_user_func_arrray([$this,$this->cacheProviders[$params['use']]],[$params]);
        $config->setQueryCacheImpl($this->cacheDriver);
        $config->setResultCacheImpl($this->cacheDriver);
        $config->setMetadataCacheImpl($this->cacheDriver);
    }

    private function getCache($driver){
        return new $driver['use'];
    }

    /**
     * @param $driver
     * @return \Doctrine\Common\Cache\MemcacheCache
     */
    private function getMemcache($driver){
         $memcache = new Memcache();
         if(!isset($driver['args'][0]) || !isset($driver['args'][1]))
             throw new InvalidArgumentException('Arguments for memcache driver missing');
         $memcache->connect($driver['args'][0],$driver['args'][1]);
         $driver = new $driver['use'];
         $driver->setMemcache($memcache);
         return $driver;
     }

    private function getMemcached($driver){
        $memcached = new Memcached();
        if(!isset($driver['args'][0]) || !isset($driver['args'][1]))
            throw new InvalidArgumentException('Arguments for memcached driver missing');
        $memcached->addServer($driver['args'][0],$driver['args'][1]);
        $driver = new $driver['use'];
        $driver->setMemcached($memcached);
        return $driver;
    }

    private function getRedis($driver){
        $redis = new Redis();
        if(!isset($driver['args'][0]) || !isset($driver['args'][1]))
            throw new InvalidArgumentException('Arguments for memcached driver missing');
        $redis->connect($driver['args'][0],$driver['args'][1]);
        $driver = new $driver['use'];
        $driver->setRedis($redis);
        return $driver;
    }
}
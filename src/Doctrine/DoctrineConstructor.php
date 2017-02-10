<?php

namespace JetFire\Db\Doctrine;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\Setup;
use JetFire\Db\DbConstructorInterface;


/**
 * Class DoctrineConstructor
 * @package JetFire\Db\Doctrine
 */
class DoctrineConstructor implements DbConstructorInterface
{

    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var
     */
    protected $options;
    /**
     * @var array
     */
    private $db;
    /**
     * @var array
     */
    private $allDb;

    /**
     * @param array $database
     * @param array $params
     */
    public function __construct($database = [], $params = [])
    {
        $this->db = $database;
        foreach($this->db as $key => $db) {
            $this->allDb[$key] = function()use($db,$params) {
                $db['dev'] = (isset($db['dev']) && $db['dev']) ? true : false;
                if (isset($db['db_url'])) {
                    $dbParams = array(
                        'url' => $db['db_url']
                    );
                } else {
                    if (!isset($db['driver']) || !isset($db['user']) || !isset($db['pass']) || !isset($db['host']) || !isset($db['db']))
                        throw new \Exception('Missing arguments for doctrine constructor');
                    $dbParams = array(
                        'driver'   => $this->getDriver($db['driver']),
                        'user'     => $db['user'],
                        'password' => $db['pass'],
                        'host'     => $db['host'],
                        'dbname'   => $db['db'],
                        'charset'  => isset($db['charset']) ? $db['charset'] : 'utf8',
                    );
                }
                $config = Setup::createAnnotationMetadataConfiguration($db['path'], $db['dev']);
                $this->setEnv($db, $params, $config);
                $this->setFunctions($params, $config);

                $evm = new EventManager();
                if (isset($db['prefix'])) {
                    $tablePrefix = new TablePrefix($db['prefix']);
                    $evm->addEventListener(Events::loadClassMetadata, $tablePrefix);
                }
                $this->setEvents($params, $evm);

                return EntityManager::create($dbParams, $config, $evm);
            };
        }
    }

    /**
     * @param array $params
     * @param EventManager $evm
     */
    private function setEvents($params = [], EventManager $evm){
        if(isset($params['events'])) {
            if(isset($params['events']['listeners']) && is_array($params['events']['listeners'])) {
                foreach ($params['events']['listeners'] as $listener) {
                    if (is_array($listener) && isset($listener[1]))
                        $evm->addEventListener($listener[0], $listener[1]);
                }
            }
            if(isset($params['events']['subscribers']) && is_array($params['events']['subscribers'])) {
                foreach ($params['events']['subscribers'] as $subscriber) {
                    $evm->addEventSubscriber($subscriber);
                }
            }
        }
    }

    /**
     * @param array $params
     * @param Configuration $config
     */
    private function setFunctions($params = [], Configuration $config){
        if(isset($params['functions']) && !empty($params['functions'])) {
            $config->setCustomDatetimeFunctions($params['functions']['customDatetimeFunctions']);
            $config->setCustomNumericFunctions($params['functions']['customNumericFunctions']);
            $config->setCustomStringFunctions($params['functions']['customStringFunctions']);
        }
    }

    /**
     * @param $db
     * @param array $params
     * @param Configuration $config
     */
    private function setEnv($db, $params = [], Configuration $config){
        if(!$db['dev']) {
            $config->setQueryCacheImpl($params['cache']);
            $config->setResultCacheImpl($params['cache']);
            $config->setMetadataCacheImpl($params['cache']);
        }
    }

    /**
     * @param $driver
     * @return string
     */
    private function getDriver($driver){
        switch($driver){
            case 'mysql':
                $driver = 'pdo_mysql';
                break;
            case 'pgsql':
                $driver= 'pdo_pgsql';
                break;
            case 'sqlite':
                $driver = 'pdo_sqlite';
                break;
        }
        return $driver;
    }

    /**
     * @param $name
     * @return $this
     * @throws \Exception
     */
    public function setDb($name){
        $this->options = $this->db[$name];
        if(is_callable($this->allDb[$name]))
            $this->allDb[$name] = call_user_func($this->allDb[$name]);
        $this->em = $this->allDb[$name];
        return $name;
    }

}
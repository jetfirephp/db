<?php

namespace JetFire\Db\Doctrine;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\EventManager;
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
     * @throws \Exception
     */
    public function __construct($database = [])
    {
        $this->db = $database;
        foreach($this->db as $key => $db) {
            $this->allDb[$key] = function()use($db) {
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
                $evm = new EventManager();
                if (isset($db['prefix'])) {
                    $tablePrefix = new TablePrefix($db['prefix']);
                    $evm->addEventListener(Events::loadClassMetadata, $tablePrefix);
                }
                $config = Setup::createAnnotationMetadataConfiguration($db['path'], $db['dev']);
                if(!$db['dev']) {
                    $config->setQueryCacheImpl($db['cache']);
                    $config->setResultCacheImpl($db['cache']);
                    $config->setMetadataCacheImpl($db['cache']);
                }
                if(isset($db['functions']) && !empty($db['functions'])) {
                    $config->setCustomDatetimeFunctions($db['functions']['customDatetimeFunctions']);
                    $config->setCustomNumericFunctions($db['functions']['customNumericFunctions']);
                    $config->setCustomStringFunctions($db['functions']['customStringFunctions']);
                }
                return EntityManager::create($dbParams, $config, $evm);
            };
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
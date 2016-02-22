<?php

namespace JetFire\Db\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\Setup;

/**
 * Class DoctrineConstructor
 * @package JetFire\Db\Doctrine
 */
class DoctrineConstructor {

    /**
     * @var EntityManager
     */
    public $em;

    /**
     * @param array $options
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function __construct($options = [])
    {
        $isDevMode = (isset($options['dev']) && $options['dev'])?true:false;
        if (isset($options['db_url'])) {
            $dbParams = array(
                'url' => $options['db_url']
            );
        }else {
            if(!isset($options['driver']) || !isset($options['user']) || !isset($options['pass']) || !isset($options['host']) || !isset($options['db']))
                throw new \Exception('Missing arguments for doctrine constructor');
            $dbParams = array(
                'driver'   => ($options['driver']==='mysql')?'pdo_mysql':$options['driver'],
                'user'     => $options['user'],
                'password' => $options['pass'],
                'host'     => $options['host'],
                'dbname'   => $options['db'],
                'charset' => isset($options['charset'])?$options['charset']:'utf8',
            );
        }
        $evm = new EventManager();
        if(isset($options['prefix'])) {
            $tablePrefix = new TablePrefix($options['prefix']);
            $evm->addEventListener(Events::loadClassMetadata, $tablePrefix);
        }
        $config = Setup::createAnnotationMetadataConfiguration($options['path'], $isDevMode);
        $this->em = EntityManager::create($dbParams, $config, $evm);
    }



} 
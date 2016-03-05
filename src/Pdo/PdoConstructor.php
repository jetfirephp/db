<?php

namespace JetFire\Db\Pdo;

use PDO;

/**
 * Class PdoConstructor
 * @package JetFire\Db\Pdo
 */
class PdoConstructor
{

    /**
     * @var PDO
     */
    protected $pdo;
    /**
     * @var
     */
    protected $options;

    /**
     * @param $options
     * @throws \Exception
     */
    public function __construct($options)
    {
        $this->options = $options;
        if (!isset($options['driver']) || !isset($options['user']) || !isset($options['pass']) || !isset($options['host']) || !isset($options['db']))
            throw new \Exception('Missing arguments for PDO constructor');
        $this->pdo = new PDO($options['driver'].':host=' . $options['host'] . ';dbname=' . $options['db'], $options['user'], $options['pass']);
    }

} 
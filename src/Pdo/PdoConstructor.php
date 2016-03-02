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
    public $pdo;

    /**
     * @param $options
     * @throws \Exception
     */
    public function __construct($options)
    {
        if (!isset($options['user']) || !isset($options['pass']) || !isset($options['host']) || !isset($options['db']))
            throw new \Exception('Missing arguments for PDO constructor');

        $this->pdo = new PDO('mysql:host=' . $options['host'] . ';dbname=' . $options['db'], $options['user'], $options['pass']);
    }

} 
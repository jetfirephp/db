<?php

namespace JetFire\Dbal\Pdo;

use PDO;

class PdoConstructor {

    public $pdo;

    public function __construct($options){
        if(!isset($options['user']) || !isset($options['pass']) || !isset($options['host']) || !isset($options['db']))
            throw new \Exception('Missing arguments for PDO constructor');

        $this->pdo = new PDO('mysql:host='.$options['host'].';dbname='.$options['db'], $options['user'], $options['pass']);
    }

} 
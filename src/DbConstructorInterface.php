<?php

namespace JetFire\Db;


/**
 * Interface DbConstructorInterface
 * @package JetFire\Db
 */
interface DbConstructorInterface {

    /**
     * @param array $options
     */
    public function __construct($options = []);

    /**
     * @param $name
     * @return mixed
     */
    public function setDb($name);

} 
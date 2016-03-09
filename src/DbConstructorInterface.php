<?php

namespace JetFire\Db;


interface DbConstructorInterface {

    public function __construct($options = []);

    public function setDb($name);

} 
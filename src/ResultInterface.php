<?php

namespace JetFire\Db;


interface ResultInterface {

    public function save();

    public function delete();

    public function __set($name,$value);

    public function __get($name);

    public function __call($name,$args);

} 
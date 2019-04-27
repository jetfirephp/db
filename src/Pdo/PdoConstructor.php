<?php

namespace JetFire\Db\Pdo;

use JetFire\Db\DbConstructorInterface;
use PDO;

/**
 * Class PdoConstructor
 * @package JetFire\Db\Pdo
 */
class PdoConstructor implements DbConstructorInterface
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
     * @var array
     */
    private $db;

    /**
     * @var array
     */
    private $allDb = [];

    /**
     * @param array $db
     * @param array $params
     * @throws \RuntimeException
     */
    public function __construct($db = [], $params = [])
    {
        $this->db = $db;
        foreach ($this->db as $key => $db) {
            if (!isset($db['driver'], $db['user'], $db['pass'], $db['host'], $db['db'])) {
                throw new \RuntimeException('Missing arguments for PDO constructor');
            }
            $this->allDb[$key] = static function () use ($db) {
                return new PDO($db['driver'] . ':host=' . $db['host'] . ';dbname=' . $db['db'], $db['user'], $db['pass']);
            };
        }
    }

    /**
     * @param $name
     * @return $this
     * @throws \Exception
     */
    public function setDb($name)
    {
        $this->options = $this->db[$name];
        if (is_callable($this->allDb[$name])) {
            $this->allDb[$name] = call_user_func($this->allDb[$name]);
        }
        $this->pdo = $this->allDb[$name];
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $name;
    }
}
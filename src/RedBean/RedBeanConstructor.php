<?php

namespace JetFire\Db\RedBean;

use RedBeanPHP\R;

/**
 * Class RedBeanConstructor
 * @package JetFire\Db\RedBean
 */
class RedBeanConstructor
{

    /**
     * @var
     */
    public $prefix;

    /**
     * @param $options
     * @throws \Exception
     */
    public function __construct($options)
    {
        if (!isset($options['user']) || !isset($options['pass']) || !isset($options['host']) || !isset($options['db']))
            throw new \Exception('Missing arguments for doctrine constructor');
        if (isset($options['prefix']))
            $this->prefix = $options['prefix'];
        R::setAutoResolve(TRUE);
        ($options['driver'] == 'sqlite')
            ? R::setup('sqlite:/tmp/dbfile.db')
            : R::setup($options['driver'] . ':host=' . $options['host'] . ';dbname=' . $options['db'], $options['user'], $options['pass']);
        R::ext('xdispense', function ($type) {
            return R::getRedBean()->dispense($type);
        });
        if (isset($options['dev']) && $options['dev'])
            R::freeze(TRUE);
    }

} 
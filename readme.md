## JetFire Database Abstract Layer for ORM

A unique facade for orm. For the moment only Doctrine is supported but other orm like RedBean will be supported.

### Installation

Via [composer](https://getcomposer.org)

```bash
$ composer require jetfirephp/db
```

For Doctrine usage you have to require the doctrine package

```bash
$ composer require doctrine/orm
```

For RedBean usage you have to require the redbean package

```bash
$ composer require gabordemooij/redbean
```

### Basic Usage

```php
// Require composer autoloader
require __DIR__ . '/vendor/autoload.php';

// database config
$options = [
     'driver' => 'pdo_sqlite',
     'host' => 'localhost',
     'user' => 'root',
     'pass' => '',
     'db' => 'project',
     'prefix' => 'jt_'
 ];
 
 $db = new \JetFire\Db\Doctrine\DoctrineModel($options);

 // Model facade
 JetFire\Db\Model::init($db);
 // Account.php must extends Model class
 $accounts = Account::all();
 
 // or you can use ModelTable facade
 JetFire\Db\ModelTable::init($db);
 $accounts = Account::table()->all();
 
```

### Multiple ORM

```php
// Require composer autoloader
require __DIR__ . '/vendor/autoload.php';

// redbean configuration
$redbeanConfig = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'db' => 'project',
    'prefix' => 'jt_',
];
// doctrine configuration
$doctrineConfig = [
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'db' => 'project',
    'prefix' => 'jt_',
    'path' => [__DIR__.'/']
];

// set your orm provider
$providers = [
    'doctrine' => function()use($doctrineConfig){
        return new \JetFire\Db\Doctrine\DoctrineModel($doctrineConfig);
    },
    'redbean' => function()use($redbeanConfig){
        return new \JetFire\Db\RedBean\RedBeanModel($redbeanConfig);
    },
];
Model::provide($providers);
$account1 = Account::orm('doctrine')->select('lastName')->where('firstName','Peter')->get();
$account2 = Account::orm('redbean')->select('firstName','lastName')->where('firstName','Peter')->orWhere('age','>',20)->get();
```

### License

The JetFire Db is released under the MIT public license : http://www.opensource.org/licenses/MIT. 
## JetFire Database Abstract Layer for ORM

A doctrine facade

### Installation

Via [composer](https://getcomposer.org)

```bash
$ composer require jetfirephp/dbal
```

### Usage

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
 
 $db = new \JetFire\Dbal\Doctrine\DoctrineModel($options);
 Model::init($db);
 
 // Account.php must extends Model class
 $accounts = Account::all();
```

### License

The JetFire Routing is released under the MIT public license : http://www.opensource.org/licenses/MIT. 
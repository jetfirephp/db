## JetFire Database Abstract Layer for ORM

A unique facade for orm. For the moment only Doctrine is supported but other orm like RedBean will be supported.

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

 // Model facade
 JetFire\Dbal\Model::init($db);
 // Account.php must extends Model class
 $accounts = Account::all();
 
 // or you can use ModelTable facade
 JetFire\Dbal\ModelTable::init($db);
 $accounts = Account::table()->all();
 
```

### License

The JetFire Routing is released under the MIT public license : http://www.opensource.org/licenses/MIT. 
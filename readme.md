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
     'prefix' => 'jt_',
     'path' => [__DIR__.'/']
 ];
 
 // Model facade
 $db = new \JetFire\Db\Doctrine\DoctrineModel($options);
 JetFire\Db\Model::init($db);
 
 // or for lazy loading
 // $db = [
 //     'doctrine' => function() use ($options) {
 //         new \JetFire\Db\Doctrine\DoctrineModel($options);
 //     }
 // ]
 // JetFire\Db\Model::provide($db);
 
 // And for retrieve data you have to possible way
 
 // 1)
 $accounts = Model::table('Account')->all(); 
 // Doctrine : the argument for table method must be the table class (with namespace)
 // RedBean : the argument for table method must be the name of the table in the database ('accounts') 
 
 // 2)
 $accounts = Account::all();
 // Account.php must extends Model class
 
```

### Multiple ORM

```php
// Require composer autoloader
require __DIR__ . '/vendor/autoload.php';

// configuration
$config = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'db' => 'project',
    'prefix' => 'jt_',
    'path' => [__DIR__.'/']
];


// set your orm provider
$providers = [
    'doctrine' => function()use($config){
        return new \JetFire\Db\Doctrine\DoctrineModel($config);
    },
    'redbean' => function()use($config){
        return new \JetFire\Db\RedBean\RedBeanModel($config);
    },
];
JetFire\Db\Model::provide($providers);
$account1 = Account::orm('doctrine')->select('lastName')->where('firstName','Peter')->get();
$account2 = Account::orm('redbean')->select('firstName','lastName')->where('firstName','Peter')->orWhere('age','>',20)->get();
// you can also omit the orm method. The model will load the first orm provided (here doctrine). To load another orm by default you have to add the orm key in second argument of provide method 
$account2 = Account::select('firstName','lastName')->where('firstName','Peter')->orWhere('age','>',20)->get(); // will load doctrine orm
```

### License

The JetFire Db is released under the MIT public license : http://www.opensource.org/licenses/MIT. 
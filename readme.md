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

### Quick Start

```php
 // Require composer autoloader
 require __DIR__ . '/vendor/autoload.php';

 // database config
 $options = [
     'driver' => 'mysql',
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
 
 // And for retrieve data you have 2 possible ways
 
 // 1)
 $accounts = Model::table('Account')->all();
 
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
    'pdo' => function()use($config){
        return new \JetFire\Db\Pdo\PdoModel($config);
    },
];
JetFire\Db\Model::provide($providers);

$account1 = Account::orm('doctrine')->select('lastName')->where('firstName','Peter')->get();
$account2 = Account::orm('pdo')->select('lastName')->where('firstName','Peter')->get();
$account3 = Account::orm('redbean')->select('firstName','lastName')->where('firstName','Peter')->orWhere('age','>',20)->get();

// you can also omit the orm method. The model will load the first orm provided (here doctrine). To load another orm by default you have to add the orm key in second argument of provide method 
$account3 = Account::select('firstName','lastName')->where('firstName','Peter')->orWhere('age','>',20)->get(); // will load doctrine orm
```

### Usage

Let's take an Account table for our example (this is a doctrine table example but it will work with other orms) 

```php
/**
 * Class Account
 * @Entity
 * @Table(name="accounts")
 */
class Account extends Model{

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    /**
     * @Column(type="string",name="first_name",length=32)
     */
    protected $first_name;
    /**
     * @Column(type="string",name="last_name",length=32)
     */
    protected $last_name;
    /**
     * @Column(type="string",unique=true)
     */
    protected $email;

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;
    }
 
    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }
} 
```

#### CRUD

CRUD stands for Create, Read, Update and Delete. CRUD operations are the core of many web applications.

##### Create

To create a new record in the database from a model, you have 2 ways to do it :

1)
```php
Account::create([
    'first_name' => 'Peter',
    'last_name' => 'Parker',
    'email' => 'peter.parker@spiderman.com'
]); // return true or false
// Account::create()->with([...]) will also work
```
2)
```php
$account = Account::get(); // return a new account object
$account->first_name = 'Peter'; // you can also use $account->setFirstName('Parker') or $account['first_name'] = 'Parker';
$account->last_name = 'Parker';
$account->email = 'peter.parker@spiderman.com';
$account->save();
```

##### Retrieve & Read

To load all accounts :

```php
$accounts = Account::all();
```

To load an account, simply pass the ID of the table you're looking for :

```php
$account = Account::find(1);
```

If you have to specify other parameters, you can do it like this :

```php
// with additional parameters
$account = Account::where('id',1)->where('last_name','Parker')->get();
// or
$account = Account::whereRaw('a.id = :id AND a.last_name = :last_name',['id' => 1, 'last_name' => 'Parker'])->get();
// select only some fields
$account = Account::select('id','first_name')->where('id',1)->where('last_name','Parker)->get(); // return only id and first_name
// order by
$account = Account::orderBy('id','DESC')->get(); 
// count
$account = Account::where('last_name','Parker')->count(); // return 1
// limit row
$account = Account::take(2); // return only the first 2 accounts
```

And to read the model data :

```php
$first_name = $account->first_name; // return 'Peter'
// or
$first_name = $account->getFirstName(); // return 'Peter'
// or
$first_name = $account['first_name']; // return 'Peter'
```

##### Update

To update a model in the database, you can do it in 3 different ways :

1) Update via ID
```php
Account::update(1)->with([
     'first_name' => 'Peter 2',
]);
```
2) Update with specif parameters
```php
Account::where('id',1)->where('id',2)->set(['first_name' => 'Peter 2']);
```
3) Retrieve the table object first then set your fields after
```php
$account = Account::where('id',1)->get();
$account->first_name = 'Peter 2'; // you can also use $account->setFirstName('Parker') or $account['first_name'] = 'Parker';
$account->save();
```

##### Delete

```php
Account::destroy(1) // Deleting An Existing Model By Key
Account::destroy(1,2) // multiple key supported
// or
Account::where('id',1)->delete()
// or
$account = Account::find(1);
$account->delete();
```

### Native sql

```php
$account = Account::sql('select * form accounts where id = 1');
$account = Account::sql('select * form accounts where id = :id',['id' => 1]); // with parameters
$account = Account::sql('update accounts set last_name = :last_name where id = :id',['last_name' => 'Parker 2','id' => 1]);
```

### Repository

You can create a repository for your table to handle custom query. Your repository class name must finish with 'Repository'

```php
class AccountRepository {
    
    public function loadParker(){
        return Account::where('last_name','Parker')->get();
    }
}

// you have to add your repository path in your configuration
$config = [
     // previous config
     // ...
     'repositories' => [
        ['path' => 'repository_folder_path','namespace' => 'respository_namespace']
     ],
];

$account = Account::repo()->loadParker();
```

### License

The JetFire Db is released under the MIT public license : http://www.opensource.org/licenses/MIT. 
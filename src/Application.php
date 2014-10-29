<?php

namespace Chatter;

use Silex\Application as SilexApplication;
use Silex\Provider\DoctrineServiceProvider;

class Application extends SilexApplication
{

  public function __construct()
  {
    parent::__construct();

    $this->registerServices($this);
    $this->registerProviders($this);
    $this->createRoutes($this);
  }

  protected function registerServices(Application $app)
  {
    $app['rot_encode.count'] = 13;

    $app['rot_encode'] = $app->share(function () use ($app) {
        return new RotEncode($app['rot_encode.count']);
      });
  }

  protected function registerProviders(Application $app)
  {
    $app->register(new DoctrineServiceProvider(), [
        'db.options' => [
          'driver'   => 'pdo_sqlite',
          'path'     => __DIR__ . '/../app.db',
        ],
      ]);
  }

  protected function createRoutes(Application $app)
  {
    $app->get('/about', function() use ($app) {
        $s = $app['rot_encode']->rot("This is a super simple messaging service.");
        return $s;
      });

    $app->get('/reinstall', function() use ($app) {

        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $app['db'];

        /** @var \Doctrine\DBAL\Schema\AbstractSchemaManager $sm */
        $sm = $conn->getSchemaManager();

        $schema = new \Doctrine\DBAL\Schema\Schema();

        $table = $schema->createTable('users');
        $table->addColumn("id", "integer", ["unsigned" => true]);
        $table->addColumn("username", "string", ["length" => 32]);
        $table->setPrimaryKey(["id"]);
        $table->addUniqueIndex(["username"]);
        $schema->createSequence("users_seq");
        $sm->dropAndCreateTable($table);

        $table = $schema->createTable('messages');
        $table->addColumn("id", "integer", ["unsigned" => true]);
        $table->addColumn("username", "string", ["length" => 32]);
        $table->addColumn("message", "string", ["length" => 256]);
        $table->addColumn("author", "integer", ["unsigned" => true]);
        $table->setPrimaryKey(["id"]);
        $sm->dropAndCreateTable($table);

        return 'DB installed';
      });

  }

}
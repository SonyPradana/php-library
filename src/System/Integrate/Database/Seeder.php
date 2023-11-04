<?php

declare(strict_types=1);

namespace System\Database\Seeder;

use System\Database\MyPDO;
use System\Database\MyQuery\Insert;
use System\Integrate\Application;

abstract class Seeder
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app =  $app;
    }

    /**
     * @param class-string $class_name
     */
    public function call(string $class_name): void
    {
        $this->app->call([$class_name, 'run']);
    }

    public function create(string $table_name): Insert
    {
        /** @var MyPDO */
        $pdo = $this->app->get(MyPDO::class);

        return new Insert($table_name, $pdo);
    }

    /**
     * Run seeder.
     */
    abstract public function run(): void;
}

<?php

declare(strict_types=1);

namespace System\Database\MyModel;

use System\Database\MyPDO;

abstract class Model extends ORMAbstract
{
    public ORM $single;

    /** @var ModelCollention<int, ORM> */
    public ModelCollention $many;

    public function __construct(MyPDO $pdo)
    {
        $this->pdo        = $pdo;
        $this->table_name ??= strtolower(__CLASS__);
        $this->single = new ORM($this->table_name, [], $pdo);
    }

    /**
     * Getter.
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->getter($name);
    }

    /**
     * Setter.
     *
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        $this->setter($name, $value);
    }

    /**
     * Setter.
     *
     * @param mixed $val
     */
    public function setter(string $key, $val): self
    {
        $this->single->setter($key, $val);

        return $this;
    }

    /**
     * Getter.
     *
     * @param mixed|null $defaul
     *
     * @return mixed
     */
    public function getter(string $key, $defaul = null)
    {
        return $this->single->getter($key, $defaul);
    }

    public function find($id): ORM
    {
        return new ORM(
            $this->table_name,
            $this->columns,
            $this->pdo,
            ['id' => $id],
            'id',
            $this->stash,
            $this->resistant
        );
    }

    public function where(string $column, $value): ORM
    {
        return $this->single->setUp(
            $this->table_name,
            $this->columns,
            $this->pdo,
            [$column => $value],
            $column,
            $this->stash,
            $this->resistant
        );
    }
}

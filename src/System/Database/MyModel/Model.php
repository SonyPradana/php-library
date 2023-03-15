<?php

declare(strict_types=1);

namespace System\Database\MyModel;

use System\Database\MyPDO;

abstract class Model extends ORM
{
    /** Share orm (self) */
    public ORM $single;

    /** @var ModelCollention<int, ORM> */
    public ModelCollention $many;

    public function __construct(MyPDO $pdo)
    {
        $this->pdo        = $pdo;
        $this->table_name ??= strtolower(__CLASS__);
        $this->single = $this;
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

    public function find($id): self
    {
        return $this->single = $this->setUp(
            $this->table_name,
            $this->columns,
            $this->pdo,
            ['id' => $id],
            'id',
            $this->stash,
            $this->resistant
        );
    }

   public function where(string $column, $value): self
   {
       return $this->single = $this->setUp(
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

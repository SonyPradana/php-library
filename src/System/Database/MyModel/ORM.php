<?php

declare(strict_types=1);

namespace System\Database\MyModel;

use System\Collection\Collection;
use System\Collection\CollectionImmutable;
use System\Database\MyPDO;
use System\Database\MyQuery;
use System\Database\MyQuery\Join\InnerJoin;

final class ORM
{
    /** @var MyPDO */
    private $pdo;

    private string $table_name;

    private string $primery_key;

    /** @var array<string, mixed> */
    private $columns = [];

    /** @var array<string, string> */
    private $indentifer = [];

    /** @var string[] Hide from shoing column */
    private $stash;

    /** @var string[] Set Column cant be modify */
    private $resistant;

    /** @var array<string, mixed> orginal data from database */
    private $fresh;

    // magic ----------------------

    /**
     * @param array<string, string> $indentifer
     * @param array<string, mixed>  $column
     * @param string[]              $stash
     * @param string[]              $resistant
     */
    public function __construct(
        string $table,
        array $column,
        MyPDO $pdo,
        array $indentifer = [],
        string $primery_key = 'id',
        array $stash = [],
        array $resistant = []
    ) {
        $this->table_name  = $table;
        $this->columns     = $this->fresh = $column;
        $this->pdo         = $pdo;
        $this->indentifer  = $indentifer;
        $this->primery_key = $primery_key;
        $this->stash       = $stash;
        $this->resistant   = $resistant;
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
     *
     * @return self
     */
    public function __set(string $name, $value)
    {
        return $this->setter($name, $value);
    }

    public function __isset(string $name)
    {
        return array_key_exists($name, $this->columns);
    }

    /**
     * Setter.
     *
     * @param mixed $val
     *
     * @return self
     */
    private function setter(string $key, $val)
    {
        if (key_exists($key, $this->columns) && !isset($this->resistant[$key])) {
            $this->columns[$key] = $val;
        }

        return $this;
    }

    /**
     * Getter.
     *
     * @param mixed|null $defaul
     *
     * @return mixed
     */
    private function getter(string $key, $defaul = null)
    {
        return $this->columns[$key] ?? $defaul;
    }

    // core -----------------------------

    public function get(): Collection
    {
        return (new Collection($this->columns))
            ->except($this->stash)
        ;
    }

    public function read(): bool
    {
        $read = MyQuery::from($this->table_name, $this->pdo)
            ->select();

        foreach ($this->indentifer as $key => $value) {
            $read->equal($key, $value);
        }

        $first = $read->single();

        if ([] === $first) {
            return false;
        }

        $this->columns = $this->fresh = $first;

        return true;
    }

    public function update(): bool
    {
        $update = MyQuery::from($this->table_name, $this->pdo)
            ->update()
            ->values(
                $this->changes()
            );

        foreach ($this->indentifer as $key => $value) {
            $update->equal($key, $value);
        }

        return $this->changing($update->execute());
    }

    public function delete(): bool
    {
        $delete = MyQuery::from($this->table_name, $this->pdo)
            ->delete();

        foreach ($this->indentifer as $key => $value) {
            $delete->equal($key, $value);
        }

        return $this->changing($delete->execute());
    }

    /**
     * @return CollectionImmutable<string, mixed>
     */
    public function hasOne(string $table, string $ref = 'id')
    {
        $ref = MyQuery::from($this->table_name, $this->pdo)
            ->select([$table . '.*'])
            ->join(InnerJoin::ref($table, $this->primery_key, $ref))
            ->equal($this->primery_key, $this->indentifer[$this->primery_key])
            ->single()
        ;

        return (new Collection($ref))
            ->add($this->columns)
            ->immutable();
    }

    /**
     * @return CollectionImmutable<string|int, mixed>
     */
    public function hasMany(string $table, string $ref = 'id')
    {
        $res = MyQuery::from($this->table_name, $this->pdo)
            ->select([$table . '.*'])
            ->join(InnerJoin::ref($table, $this->primery_key, $ref))
            ->equal($this->primery_key, $this->indentifer[$this->primery_key])
            ->get()
            ->immutable()
        ;

        return (new Collection($this->columns))
            ->set($table, $res->toArray())
            ->immutable();
    }

    public function isClean(string $column = null): bool
    {
        if ($column === null) {
            return $this->columns === $this->fresh;
        }

        if (isset($this->fresh[$column])) {
            return false;
        }

        return $this->columns[$column] === $this->fresh[$column];
    }

    public function isDirty(string $column = null): bool
    {
        return !$this->isClean($column);
    }

    public function changes(): array
    {
        $change = [];

        foreach ($this->columns as $key => $value) {
            if ($this->fresh[$key] !== $value) {
                $change[$key] = $value;
            }
        }

        return $change;
    }

    // private --------------------

    private function changing(bool $change): bool
    {
        if ($change) {
            $this->fresh = $this->columns;
        }

        return $change;
    }
}

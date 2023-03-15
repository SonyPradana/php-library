<?php

declare(strict_types=1);

namespace System\Database\MyModel;

use System\Collection\Collection;
use System\Collection\CollectionImmutable;
use System\Database\MyModel\Interfaces\ORMInterface;
use System\Database\MyPDO;
use System\Database\MyQuery;
use System\Database\MyQuery\Join\InnerJoin;

class ORM extends ORMAbstract implements ORMInterface
{
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
        $this->setUp($table, $column, $pdo, $indentifer, $primery_key, $stash, $resistant);
    }

    /**
     * @param array<string, string> $indentifer
     * @param array<string, mixed>  $column
     * @param string[]              $stash
     * @param string[]              $resistant
     */
    public function setUp(
        string $table,
        array $column,
        MyPDO $pdo,
        array $indentifer,
        string $primery_key,
        array $stash,
        array $resistant
    ): self {
        $this->table_name  = $table;
        $this->columns     = $this->fresh = $column;
        $this->pdo         = $pdo;
        $this->indentifer  = $indentifer;
        $this->primery_key = $primery_key;
        $this->stash       = $stash;
        $this->resistant   = $resistant;

        return $this;
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

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->columns);
    }

    /**
     * Setter.
     *
     * @param mixed $val
     */
    public function setter(string $key, $val): self
    {
        if (key_exists($key, $this->columns) && !in_array($key, $this->resistant)) {
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
    public function getter(string $key, $defaul = null)
    {
        if (array_key_exists($key, $this->stash)) {
            throw new \Exception("Cant read this colum `{$key}`");
        }

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

        return new CollectionImmutable($ref);
    }

    /**
     * @return CollectionImmutable<string|int, mixed>
     */
    public function hasMany(string $table, string $ref = 'id')
    {
        $ref = MyQuery::from($this->table_name, $this->pdo)
            ->select([$table . '.*'])
            ->join(InnerJoin::ref($table, $this->primery_key, $ref))
            ->equal($this->primery_key, $this->indentifer[$this->primery_key])
            ->get()
            ->immutable()
        ;

        return new CollectionImmutable($ref);
    }

    public function isClean(string $column = null): bool
    {
        if ($column === null) {
            return $this->columns === $this->fresh;
        }

        if (!array_key_exists($column, $this->columns) || !array_key_exists($column, $this->fresh)) {
            throw new \Exception("Column `{$column}` is not in table `{$this->table_name}`");
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
            if ($this->fresh[$key] !== $value && array_key_exists($key, $this->fresh)) {
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

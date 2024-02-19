<?php

declare(strict_types=1);

namespace System\Database\MyModel;

// use System\Collection\Collection;

use System\Collection\CollectionImmutable;
use System\Database\MyModel\Query\Select;
use System\Database\MyPDO;
use System\Database\MyQuery;
use System\Database\MyQuery\Join\InnerJoin;
use System\Database\MyQuery\Query;

class Model
{
    protected MyPDO $pdo;

    protected string $table_name;

    protected string $primery_key;

    /** @var array<int, array<string, mixed>> */
    protected $columns;

    /** @var array<string, string[]> */
    protected $indentifer;

    /** @var string[] Hide from shoing column */
    protected $stash;

    /** @var string[] Set Column cant be modify */
    protected $resistant;

    /** @var array<int, array<string, mixed>> Orginal data from database */
    protected $fresh;

    // test -----------------------
    /** @var array<string, mixed> Currrent columns use to diplay */
    public $current;

    private Select $select;
    private Select $select_multy;

    // magic ----------------------

    /**
     * @param array<string, string[]> $indentifer
     * @param array<string, mixed>    $column
     * @param string[]                $stash
     * @param string[]                $resistant
     */
    public function __construct(
        string $table,
        array $column,
        MyPDO $pdo,
        array $indentifer = [[]],
        string $primery_key = 'id',
        array $stash = [],
        array $resistant = []
    ) {
        $this->table_name ??= $table;
        $this->columns ??= $this->fresh = $column;
        $this->pdo ??= $pdo;
        $this->indentifer ??= $indentifer;
        $this->primery_key ??= $primery_key;
        $this->stash ??= $stash;
        $this->resistant ??= $resistant;
    }

    public function __debugInfo()
    {
        return $this->getColumns();
    }

    /**
     * @param array<string, string[]> $indentifer
     * @param array<string, mixed>    $column
     * @param string[]                $stash
     * @param string[]                $resistant
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
     */
    public function __get(string $name)
    {
        return $this->getter($name);
    }

    /**
     * Setter.
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
     */
    public function getter(string $key, $defaul = null)
    {
        if (array_key_exists($key, $this->stash)) {
            throw new \Exception("Cant read this colum `{$key}`");
        }

        return $this->columns[$key] ?? $defaul;
    }

    // core -----------------------------

    /**
     * Get first collomn.
     *
     * @return array<string, mixed>
     */
    public function first(): array
    {
        $columns = $this->getColumns();
        if (null === ($key = array_key_first($columns))) {
            throw new \Exception('Empty columns, try to assgin using read.');
        }

        return $columns[$key];
    }

    /** @return Collection<int, static> */
    public function get(): Collection
    {
        $collection = [];
        foreach ($this->columns as $column) {
            $collection[] = new static(
                $this->table_name,
                [$column],
                $this->pdo,
                [$this->primery_key => [$column[$this->primery_key]]],
                $this->primery_key,
                $this->stash,
                $this->resistant
            );
        }

        return new Collection($collection);
    }

    public function insert(): bool
    {
        $insert = MyQuery::from($this->table_name, $this->pdo);
        foreach ($this->columns as $column) {
            $success = $insert->insert()
                ->values($column)
                ->execute();

            if (!$success) {
                return false;
            }
        }

        return true;
    }

    public function read(): bool
    {
        $query = new Select($this->table_name, ['*'], $this->pdo);

        foreach ($this->indentifer as $column_name => $value) {
            $query->in($column_name, $value);
        }

        $all = $this->fetch($query);

        if ([] === $all) {
            return false;
        }

        $this->columns = $this->fresh = $all;

        return true;
    }

    public function update(): bool
    {
        if ($this->isClean) {
            return false;
        }

        $update = MyQuery::from($this->table_name, $this->pdo)
            ->update()
            ->values(
                $this->changes()
            );

        foreach ($this->indentifer as $column_name => $value) {
            $update->in($column_name, $value);
        }

        return $this->changing($update->execute());
    }

    public function delete(): bool
    {
        $delete = MyQuery::from($this->table_name, $this->pdo)
            ->delete();

        foreach ($this->indentifer as $column_name => $value) {
            $delete->in($column_name, $value);
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

    /**
     * Check current column has modify o not.
     */
    public function isClean(?string $column = null): bool
    {
        if ($column === null) {
            return $this->columns === $this->fresh;
        }

        if (false === (array_keys($this->columns) === array_keys($this->fresh))) {
            return false;
        }

        foreach (array_keys($this->columns) as $key) {
            if (!array_key_exists($column, $this->columns[$key])
            || !array_key_exists($column, $this->fresh[$key])) {
                throw new \Exception("Column `{$column}` is not in table `{$this->table_name}`");
            }

            if (false === ($this->columns[$key][$column] === $this->fresh[$key][$column])) {
                return false;
            }
        }

        return true;
    }

    public function isDirty(?string $column = null): bool
    {
        return !$this->isClean($column);
    }

    public function changes(): array
    {
        $change = [];

        foreach ($this->columns as $key => $column) {
            if (array_key_exists($key, $this->fresh)) {
                $change[$key] = $column;
                continue;
            }
            foreach ($column as $key_item => $item) {
                if (array_key_exists($key_item, $this->fresh[$key]) && $this->fresh[$key][$key_item] !== $item) {
                    $change[$key] = $item;
                    continue 2;
                }
            }
        }

        return $change;
    }

    // static ---------------------

    // protected ------------------

    /**
     * Get current column without stash.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getColumns(): array
    {
        $columns = [];
        foreach ($this->columns as $key => $column) {
            $columns[$key] = array_filter($column, fn ($k) => false === in_array($k, $this->stash), ARRAY_FILTER_USE_KEY);
        }

        return $columns;
    }

    // private --------------------

    private function changing(bool $change): bool
    {
        if ($change) {
            $this->fresh = $this->columns;
        }

        return $change;
    }

    // public function baseBinding(Query $query): Query
    // {
    //     foreach ($this->indentifer as $column_name => $value) {
    //         $query->in($column_name, $value);
    //     }
    //     return $query;
    // }

    private function builder($query): array
    {
        return [
            (fn () => $this->{'builder'}())->call($query),
            (fn () => $this->{'_binds'})->call($query),
        ];
    }

    private function fetch(Query $base_query)
    {
        [$query, $binds] = $this->builder($base_query);

        $this->pdo->query($query);
        foreach ($binds as $bind) {
            if (!$bind->hasBind()) {
                $this->pdo->bind($bind->getBind(), $bind->getValue());
            }
        }

        return $this->pdo->resultset();
    }

    private function execute(Query $base_query): bool
    {
        [$query, $binds] = $this->builder($base_query);

        if ($query != null) {
            $this->pdo->query($query);
            foreach ($binds as $bind) {
                if (!$bind->hasBind()) {
                    $this->pdo->bind($bind->getBind(), $bind->getValue());
                }
            }

            $this->pdo->execute();

            return $this->pdo->rowCount() > 0 ? true : false;
        }

        return false;
    }
}

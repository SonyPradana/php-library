<?php

declare(strict_types=1);

namespace System\Database\MyModel;

use System\Collection\CollectionImmutable;
use System\Database\MyPDO;
use System\Database\MyQuery;
use System\Database\MyQuery\Join\InnerJoin;

use function DI\value;

final class ORM
{
    /** @var MyPDO */
    protected $pdo;

    protected string $table_name;
    protected string $primery_key;

    /** @var array<string, mixed> */
    protected $columns = [];

    /** @var array<string, string> */
    protected $indentifer = [];

    /** @var string[] Hide from shoing column */
    protected $stash;

    /** @var string[] Set Column cant be modify */
    protected $resistant;

    /** @var array<string, mixed> orginal data from database */
    protected $fresh;

    // magic ----------------------

    /**
     * @param array<string, string> $indentifer
     */
    public function __construct(array $indentifer, MyPDO $pdo)
    {
        $this->indentifer = $indentifer;
        $this->pdo = $pdo;
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
     * @param mixed  $value
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
    protected function setter(string $key, $val)
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
    protected function getter(string $key, $defaul = null)
    {
        return $this->columns[$key] ?? $defaul;
    }

    public function read(): bool
    {
        $read = MyQuery::from($this->table_name, $this->pdo)
            ->select();

        foreach ($this->indentifer as $key => $value) {
            $read->equal($key, $value);
        }

        $read->single();

        if ([] === $read) {
            return false;
        }

        $this->columns = $this->fresh = $read;

        return true;
    }

    public function update(): bool
    {
        $update = MyQuery::from($this->table_name, $this->pdo)
            ->update();

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
    protected function hasOne(string $table, string $ref = 'id')
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
    protected function hasMany(string $table, string $ref = 'id')
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
    // private --------------------

    private function changing(bool $change): bool
    {
        if ($change) {
            $this->fresh = $this->columns;
        }

        return $change;
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
}

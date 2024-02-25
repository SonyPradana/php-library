<?php

declare(strict_types=1);

namespace System\Database\MyModel;

use System\Collection\CollectionImmutable;
use System\Database\MyPDO;
use System\Database\MyQuery;
use System\Database\MyQuery\Bind;
use System\Database\MyQuery\Join\InnerJoin;
use System\Database\MyQuery\Query;
use System\Database\MyQuery\Select;
use System\Database\MyQuery\Where;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements \ArrayAccess<TKey, TValue>
 * @implements \IteratorAggregate<TKey, TValue>
 */
class Model implements \ArrayAccess, \IteratorAggregate
{
    protected MyPDO $pdo;

    protected string $table_name;

    protected string $primery_key = 'id';

    /** @var array<array<TKey, TValue>> */
    protected $columns;

    /** @var string[] Hide from shoing column */
    protected $stash = [];

    /** @var string[] Set Column cant be modify */
    protected $resistant = [];

    /** @var array<array<TKey, TValue>> Orginal data from database */
    protected $fresh;

    private ?Where $where = null;

    /**
     * Binder array(['key', 'val']).
     *
     * @var Bind[] Binder for PDO bind */
    protected $binds = [];

    // magic ----------------------

    /**
     * @param array<TKey, TValue> $column
     *
     * @final
     */
    public function __construct(
        MyPDO $pdo,
        array $column
    ) {
        $this->pdo        = $pdo;
        $this->columns    = $this->fresh = $column;
        // auto table
        $this->table_name ??= strtolower(__CLASS__);
    }

    public function __debugInfo()
    {
        return $this->getColumns();
    }

    /**
     * @param array<array<TKey, TValue>> $column
     * @param string[]                   $stash
     * @param string[]                   $resistant
     *
     * @return static
     */
    public function setUp(
        string $table,
        array $column,
        MyPDO $pdo,
        string $primery_key,
        array $stash,
        array $resistant
    ): self {
        $this->table_name  = $table;
        $this->columns     = $this->fresh = $column;
        $this->pdo         = $pdo;
        $this->primery_key = $primery_key;
        $this->stash       = $stash;
        $this->resistant   = $resistant;

        return $this;
    }

    /**
     * Getter.
     *
     * @return TValue
     */
    public function __get(string $name)
    {
        return $this->getter($name);
    }

    /**
     * Setter.
     *
     * @param TValue $value
     */
    public function __set(string $name, $value)
    {
        $this->setter($name, $value);
    }

    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->first());
    }

    /**
     * Setter.
     *
     * @param TValue $value
     *
     * @return static
     */
    public function setter(string $key, $value): self
    {
        $this->firstColumn($current);
        if (key_exists($key, $this->columns[$current]) && !in_array($key, $this->resistant)) {
            $this->columns[$current][$key] = $value;

            return $this;
        }

        return $this;
    }

    /**
     * Getter.
     *
     * @param mixed|null $default
     *
     * @return TValue
     */
    public function getter(string $key, $default = null)
    {
        if (array_key_exists($key, $this->stash)) {
            throw new \Exception("Cant read this colum `{$key}`");
        }

        return $this->first()[$key] ?? $default;
    }

    // core -----------------------------

    public function indentifer(): Where
    {
        return $this->where = new Where($this->table_name);
    }

    /**
     * Get first collomn without stash.
     *
     * @param int|string|null $key ByRef key
     *
     * @return array<TKey, TValue>
     */
    public function first(&$key = null): array
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
            $collection[] = (new static($this->pdo, []))->setUp(
                $this->table_name,
                [$column],
                $this->pdo,
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

        $all = $this->fetch($query);

        if ([] === $all) {
            return false;
        }

        $this->columns = $this->fresh = $all;

        return true;
    }

    /**
     * Update column from database.
     */
    public function update(): bool
    {
        if ($this->isClean()) {
            return false;
        }

        $update = MyQuery::from($this->table_name, $this->pdo)
            ->update()
            ->values(
                $this->changes()
            );

        return $this->changing($this->execute($update));
    }

    public function delete(): bool
    {
        $delete = MyQuery::from($this->table_name, $this->pdo)
            ->delete();

        return $this->changing($this->execute($delete));
    }

    /**
     * @return CollectionImmutable<string, mixed>
     */
    public function hasOne(string $table, string $ref = 'id')
    {
        $ref = MyQuery::from($this->table_name, $this->pdo)
            ->select([$table . '.*'])
            ->join(InnerJoin::ref($table, $this->primery_key, $ref))
            ->whereRef($this->where)
            ->single();

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
            ->whereRef($this->where)
            ->get();

        return new CollectionImmutable($ref->immutable());
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

    /**
     * Get change (diff) between fresh and current column.
     *
     * @return array<TKey, TValue>
     */
    public function changes(): array
    {
        $change = [];

        $column = $this->firstColumn($current);
        if (false === array_key_exists($current, $this->fresh)) {
            return $column;
        }

        foreach ($column as $key => $value) {
            if (array_key_exists($key, $this->fresh[$current])
            && $this->fresh[$current][$key] !== $value) {
                $change[$key] = $value;
            }
        }

        return $change;
    }

    /**
     * Convert model column to array.
     *
     * @return array<array<TKey, TValue>>
     */
    public function toArray(): array
    {
        return $this->getColumns();
    }

    // array access --------------

    /**
     * @param TKey $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @param TKey $offset
     *
     * @return TValue|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->getter($offset, null);
    }

    public function offsetSet($offset, $value): void
    {
        $this->setter($offset, $value);
    }

    public function offsetUnset($offset): void
    {
    }

    /**
     * @return \Traversable<TKey, TValue>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->first());
    }

    // static ---------------------

    /**
     * Find model using defined primery key.
     *
     * @param int|string $id
     */
    public static function find($id, MyPDO $pdo): static
    {
        $model          = new static($pdo, []);
        $model->where   = (new Where($model->table_name))
            ->equal($model->primery_key, $id);

        $model->read();

        return $model;
    }

    /**
     * Find model using costume where.
     *
     * @param array<string|int> $binder
     */
    public static function where(string $where_condition, array $binder, MyPDO $pdo): static
    {
        $model = new static($pdo, []);
        $map   = [];
        foreach ($binder as $bind => $value) {
            $map[] = [$bind, $value];
        }

        $model->where = (new Where($model->table_name))
            ->where($where_condition, $map);
        $model->read();

        return $model;
    }

    /**
     * @return Collection<int, static>
     */
    public static function all(MyPDO $pdo): Collection
    {
        $model = new static($pdo, []);
        $model->read();

        return $model->get();
    }

    // protected ------------------

    /**
     * Get current column without stash.
     *
     * @return array<array<TKey, TValue>>
     */
    protected function getColumns(): array
    {
        $columns = [];
        foreach ($this->columns as $key => $column) {
            $columns[$key] = array_filter($column, fn ($k) => false === in_array($k, $this->stash), ARRAY_FILTER_USE_KEY);
        }

        return $columns;
    }

    /**
     * Get first collumn.
     *
     * @param int|string|null $key ByRef key
     *
     * @return array<TKey, TValue>
     */
    protected function firstColumn(&$key = null): array
    {
        if (null === ($key = array_key_first($this->columns))) {
            throw new \Exception('Empty columns, try to assgin using read.');
        }

        return $this->columns[$key];
    }

    // private --------------------

    private function changing(bool $change): bool
    {
        if ($change) {
            $this->fresh = $this->columns;
        }

        return $change;
    }

    /**
     * Get binder.
     *
     * @return array<Bind[]|string>
     */
    private function builder(Query $query): array
    {
        return [
            (fn () => $this->{'builder'}())->call($query),
            (fn () => $this->{'_binds'})->call($query),
        ];
    }

    /**
     * Fetch pdo query result.
     *
     * @return mixed[]|false
     */
    private function fetch(Query $base_query)
    {
        // costume where
        $base_query->whereRef($this->where);

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
        // costume where
        $base_query->whereRef($this->where);

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

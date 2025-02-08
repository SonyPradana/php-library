<?php

declare(strict_types=1);

namespace System\Database\MyModel;

use System\Database\MyPDO;
use System\Database\MyQuery;
use System\Database\MyQuery\Bind;
use System\Database\MyQuery\Join\InnerJoin;
use System\Database\MyQuery\Query;
use System\Database\MyQuery\Select;
use System\Database\MyQuery\Where;

/**
 * @implements \ArrayAccess<array-key, mixed>
 * @implements \IteratorAggregate<array-key, mixed>
 */
class Model implements \ArrayAccess, \IteratorAggregate
{
    protected MyPDO $pdo;

    protected string $table_name;

    protected string $primery_key = 'id';

    /** @var array<array<array-key, mixed>> */
    protected $columns;

    /** @var string[] Hide from shoing column */
    protected $stash = [];

    /** @var string[] Set Column cant be modify */
    protected $resistant = [];

    /** @var array<array<array-key, mixed>> Orginat data from database */
    protected $fresh;

    protected ?Where $where = null;

    /**
     * Binder array(['key', 'val']).
     *
     * @var Bind[] Binder for PDO bind */
    protected $binds = [];

    // costume select -------------

    protected int $limit_start    = 0;
    protected int $limit_end      = 0;
    protected int $offset         = 0;

    /** @var array<string, string> */
    protected $sort_order  = [];

    // magic ----------------------

    /**
     * @param array<array-key, mixed> $column
     *
     * @final
     */
    public function __construct(
        MyPDO $pdo,
        array $column,
    ) {
        $this->pdo        = $pdo;
        $this->columns    = $this->fresh = $column;
        // auto table
        $this->table_name ??= strtolower(__CLASS__);
        $this->where = new Where($this->table_name);
    }

    /**
     * Debug information, stash exclude from showing.
     */
    public function __debugInfo()
    {
        return $this->getColumns();
    }

    /**
     * @param array<array<array-key, mixed>> $column
     * @param string[]                       $stash
     * @param string[]                       $resistant
     *
     * @return static
     */
    public function setUp(
        string $table,
        array $column,
        MyPDO $pdo,
        Where $where,
        string $primery_key,
        array $stash,
        array $resistant,
    ): self {
        $this->table_name  = $table;
        $this->columns     = $this->fresh = $column;
        $this->pdo         = $pdo;
        $this->where       = $where;
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
        if (method_exists($this, $name)) {
            $highorder = $this->{$name}();
            if (is_a($highorder, Model::class)) {
                return $highorder->first();
            }

            if (is_a($highorder, ModelCollection::class)) {
                return $highorder->toArrayArray();
            }
        }

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
     * Check first column has key.
     */
    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    /**
     * Check first column contains key.
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->first());
    }

    /**
     * Setter.
     *
     * @param mixed $value
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
     * @return mixed
     */
    public function getter(string $key, $default = null)
    {
        if (array_key_exists($key, $this->stash)) {
            throw new \Exception("Cant read this column `{$key}`.");
        }

        return $this->first()[$key] ?? $default;
    }

    // core -----------------------------

    /**
     * Get value of primery key from first collumn/record.
     *
     * @return mixed
     *
     * @throws \Exception No records founds
     */
    public function getPrimeryKey()
    {
        $first = $this->first();
        if (false === array_key_exists($this->primery_key, $first)) {
            throw new \Exception('this ' . __CLASS__ . 'model doest contain correct record, plase check your query.');
        }

        return $first[$this->primery_key];
    }

    /**
     * Costume where condition (overwrite where).
     */
    public function indentifer(): Where
    {
        return $this->where = new Where($this->table_name);
    }

    /**
     * Get first collomn without stash.
     *
     * @param int|string|null $key ByRef key
     *
     * @return array<array-key, mixed>
     */
    public function first(&$key = null): array
    {
        $columns = $this->getColumns();
        if (null === ($key = array_key_first($columns))) {
            throw new \Exception('Empty columns, try to assgin using read.');
        }

        return $columns[$key];
    }

    /**
     * Fetch query return as model collection.
     *
     * @return ModelCollection<array-key, static>
     */
    public function get(): ModelCollection
    {
        /** @var ModelCollection<array-key, static> */
        $collection = new ModelCollection([], $this);
        foreach ($this->columns as $column) {
            $where = new Where($this->table_name);
            if (array_key_exists($this->primery_key, $column)) {
                $where->equal($this->primery_key, $column[$this->primery_key]);
            }

            $collection->push((new static($this->pdo, []))->setUp(
                $this->table_name,
                [$column],
                $this->pdo,
                $where,
                $this->primery_key,
                $this->stash,
                $this->resistant
            ));
        }

        return $collection;
    }

    /**
     * Insert all column to database.
     */
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

    /*
     * Read record base where condition given.
     */
    public function read(): bool
    {
        $query = new Select($this->table_name, ['*'], $this->pdo);

        $query->sortOrderRef($this->limit_start, $this->limit_end, $this->offset, $this->sort_order);

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

    /*
     * Delete record base on where condition given.
     */
    public function delete(): bool
    {
        $delete = MyQuery::from($this->table_name, $this->pdo)
            ->delete();

        return $this->changing($this->execute($delete));
    }

    /**
     * Check where condition has rocord or not.
     */
    public function isExist(): bool
    {
        $query = new Select($this->table_name, [$this->primery_key], $this->pdo);

        $query->whereRef($this->where);

        return $this->execute($query);
    }

    /**
     * Get get model relation.
     *
     * @param class-string|string $model
     *
     * @return Model
     */
    public function hasOne($model, ?string $ref = null)
    {
        if (class_exists($model)) {
            /** @var object */
            $model      = new $model($this->pdo, []);
            $table_name = $model->table_name;
            $join_ref   = $ref ?? $model->primery_key;
        } else {
            $table_name = $model;
            $join_ref   = $ref ?? $this->primery_key;
            $model      = new static($this->pdo, []);
        }
        $result   = MyQuery::from($this->table_name, $this->pdo)
            ->select([$table_name . '.*'])
            ->join(InnerJoin::ref($table_name, $this->primery_key, $join_ref))
            ->whereRef($this->where)
            ->single();
        $model->columns = $model->fresh = [$result];

        return $model;
    }

    /**
     * Get get model relation.
     *
     * @param class-string|string $model
     *
     * @return ModelCollection<array-key, Model>
     */
    public function hasMany($model, ?string $ref = null)
    {
        if (class_exists($model)) {
            /** @var object */
            $model      = new $model($this->pdo, []);
            $table_name = $model->table_name;
            $join_ref   = $ref ?? $model->primery_key;
        } else {
            $table_name = $model;
            $join_ref   = $ref ?? $this->primery_key;
            $model      = new static($this->pdo, []);
        }
        $result = MyQuery::from($this->table_name, $this->pdo)
             ->select([$table_name . '.*'])
             ->join(InnerJoin::ref($table_name, $this->primery_key, $join_ref))
             ->whereRef($this->where)
             ->get();
        $model->columns = $model->fresh = $result->toArray();

        return $model->get();
    }

    /**
     * Check current column has modify or not.
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
                throw new \Exception("Column {$column} is not in table `{$this->table_name}`.");
            }

            if (false === ($this->columns[$key][$column] === $this->fresh[$key][$column])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check current coulmn has modify or not.
     */
    public function isDirty(?string $column = null): bool
    {
        return !$this->isClean($column);
    }

    /**
     * Get change (diff) between fresh and current column.
     *
     * @return array<array-key, mixed>
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
     * @return array<array<array-key, mixed>>
     */
    public function toArray(): array
    {
        return $this->getColumns();
    }

    // costume select ------------

    /**
     * Set data start for feact all data.
     *
     * @param int $limit_start limit start
     * @param int $limit_end   limit end
     *
     * @return static
     */
    public function limit(int $limit_start, int $limit_end)
    {
        $this->limitStart($limit_start);
        $this->limitEnd($limit_end);

        return $this;
    }

    /**
     * Set data start for feact all data.
     *
     * @param int $value limit start default is 0
     *
     * @return static
     */
    public function limitStart(int $value)
    {
        $this->limit_start = $value < 0 ? 0 : $value;

        return $this;
    }

    /**
     * Set data end for feact all data
     * zero value meaning no data show.
     *
     * @param int $value limit start default
     *
     * @return static
     */
    public function limitEnd(int $value)
    {
        $this->limit_end = $value < 0 ? 0 : $value;

        return $this;
    }

    /**
     * Set offest.
     *
     * @param int $value offet
     *
     * @return static
     */
    public function offset(int $value)
    {
        $this->offset = $value < 0 ? 0 : $value;

        return $this;
    }

    /**
     * Set limit using limit and offset.
     *
     * @return static
     */
    public function limitOffset(int $limit, int $offset)
    {
        return $this
            ->limitStart($limit)
            ->limitEnd(0)
            ->offset($offset);
    }

    /**
     * Set sort column and order
     * column name must register.
     */
    public function order(string $column_name, int $order_using = MyQuery::ORDER_ASC, ?string $belong_to = null): self
    {
        $order = 0 === $order_using ? 'ASC' : 'DESC';
        $belong_to ??= $this->table_name;
        $res = "{$belong_to}.{$column_name}";

        $this->sort_order[$res] = $order;

        return $this;
    }

    // array access --------------

    /**
     * @param array-key $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @param array-key $offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset): mixed
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
     * @return \Traversable<array-key, mixed>
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
     * Find model using defined primery key.
     *
     * @param mixed                   $id
     * @param array<array-key, mixed> $column
     *
     * @throws \Exception cant inset data
     */
    public static function findOrCreate($id, array $column, MyPDO $pdo): static
    {
        $model          = new static($pdo, [$column]);
        $model->where   = (new Where($model->table_name))
            ->equal($model->primery_key, $id);

        if ($model->isExist()) {
            $model->read();

            return $model;
        }

        if ($model->insert()) {
            return $model;
        }

        throw new \Exception('Cant inset data.');
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
     * Find model using costume equal.
     *
     * @param array-key $column_name
     * @param mixed     $value
     */
    public static function equal($column_name, $value, MyPDO $pdo): static
    {
        $model = new static($pdo, []);

        $model->indentifer()->equal($column_name, $value);
        $model->read();

        return $model;
    }

    /**
     * Fetch all records.
     *
     * @return ModelCollection<array-key, static>
     */
    public static function all(MyPDO $pdo): ModelCollection
    {
        $model = new static($pdo, []);
        $model->read();

        return $model->get();
    }

    // protected ------------------

    /**
     * Get current column without stash.
     *
     * @return array<array<array-key, mixed>>
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
     * @return array<array-key, mixed>
     */
    protected function firstColumn(&$key = null): array
    {
        if (null === ($key = array_key_first($this->columns))) {
            throw new \Exception('Empty columns, try to assgin using read.');
        }

        return $this->columns[$key];
    }

    // private --------------------

    /**
     * Reverse fresh column with current column.
     */
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

    /**
     * Execute query with where condition given.
     */
    private function execute(Query $base_query): bool
    {
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

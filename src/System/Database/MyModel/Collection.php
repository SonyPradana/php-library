<?php

declare(strict_types=1);

namespace System\Database\MyModel;

use System\Collection\Collection as BaseCollection;
use System\Database\MyQuery\Delete;
use System\Database\MyQuery\Update;

/**
 * @template TKey of array-key
 * @template Model
 *
 * @extends BaseCollection<TKey, Model>
 */
class Collection extends BaseCollection
{
    /** @var Model */
    private $model;

    /**
     * @param iterable<TKey, Model> $models
     * @param Model                 $of
     */
    public function __construct($models, $of)
    {
        parent::__construct($models);
        $this->model = $of;
    }

    /**
     * Get value of primery key from first collumn/record.
     *
     * @template TValue
     *
     * @return TValue[]
     *
     * @throws \Exception No records founds
     */
    public function getPrimeryKey()
    {
        $primeryKeys = [];
        foreach ($this->collection as $model) {
            $primeryKeys[] = $model->getPrimeryKey();
        }

        return $primeryKeys;
    }

    public function isClean(?string $column = null): bool
    {
        return $this->every(fn ($model) => $model->isClean($column));
    }

    public function isDirty(?string $column = null): bool
    {
        return !$this->isClean($column);
    }

    public function update(array $values): bool
    {
        $table_name  = (fn () => $this->{'table_name'})->call($this->model);
        $pdo         = (fn () => $this->{'pdo'})->call($this->model);
        $primery_key = (fn () => $this->{'primery_key'})->call($this->model);
        $update      = new Update($table_name, $pdo);

        $update->values($values)->in($primery_key, $this->getPrimeryKey());

        return $update->execute();
    }

    public function delete(): bool
    {
        $table_name  = (fn () => $this->{'table_name'})->call($this->model);
        $pdo         = (fn () => $this->{'pdo'})->call($this->model);
        $primery_key = (fn () => $this->{'primery_key'})->call($this->model);
        $delete      = new Delete($table_name, $pdo);

        $delete->in($primery_key, $this->getPrimeryKey());

        return $delete->execute();
    }
}

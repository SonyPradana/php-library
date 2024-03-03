<?php

declare(strict_types=1);

namespace System\Database\MyModel;

use System\Collection\Collection;
use System\Database\MyQuery\Delete;
use System\Database\MyQuery\Update;

/**
 * @extends Collection<array-key, Model>
 */
class ModelCollection extends Collection
{
    /** @var Model */
    private $model;

    /**
     * @param iterable<array-key, Model> $models
     * @param Model                      $of
     */
    public function __construct($models, $of)
    {
        parent::__construct($models);
        $this->model = $of;
    }

    /**
     * Get value of primery key from first collumn/record.
     *
     * @return mixed[]
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

    /**
     *  Check every Model has clean column.
     */
    public function isClean(?string $column = null): bool
    {
        return $this->every(fn ($model) => $model->isClean($column));
    }

    /**
     * Check every Model has dirty column.
     */
    public function isDirty(?string $column = null): bool
    {
        return !$this->isClean($column);
    }

    /**
     * Update using query using model primery key.
     *
     * @param array<array-key, mixed> $values
     */
    public function update(array $values): bool
    {
        $table_name  = (fn () => $this->{'table_name'})->call($this->model);
        $pdo         = (fn () => $this->{'pdo'})->call($this->model);
        $primery_key = (fn () => $this->{'primery_key'})->call($this->model);
        $update      = new Update($table_name, $pdo);

        $update->values($values)->in($primery_key, $this->getPrimeryKey());

        return $update->execute();
    }

    /**
     * Delete using query using model primery key.
     */
    public function delete(): bool
    {
        $table_name  = (fn () => $this->{'table_name'})->call($this->model);
        $pdo         = (fn () => $this->{'pdo'})->call($this->model);
        $primery_key = (fn () => $this->{'primery_key'})->call($this->model);
        $delete      = new Delete($table_name, $pdo);

        $delete->in($primery_key, $this->getPrimeryKey());

        return $delete->execute();
    }

    /**
     * Convert array of model to pure array;.
     *
     * @return array<array-key, mixed>
     */
    public function toArrayArray(): array
    {
        /** @var array<array-key, mixed> */
        $arr = [];
        foreach ($this->collection as $model) {
            $arr = array_merge($arr, $model->toArray());
        }

        return $arr;
    }
}

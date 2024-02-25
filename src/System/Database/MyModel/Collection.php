<?php

declare(strict_types=1);

namespace System\Database\MyModel;

use System\Collection\Collection as BaseCollection;

/**
 * @template TKey of array-key
 * @template Model
 *
 * @extends BaseCollection<TKey, Model>
 */
class Collection extends BaseCollection
{
    public function isClean(?string $column = null): bool
    {
        return $this->every(fn ($model) => $model->isClean($column));
    }

    public function isDirty(?string $column = null): bool
    {
        return !$this->isClean($column);
    }
}

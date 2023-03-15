<?php

declare(strict_types=1);

namespace System\Database\MyModel;

use System\Collection\CollectionImmutable;

/**
 * @template TKey of int
 * @template TValue of ORM
 *
 * @extends AbstractCollectionImmutable<int, ORM>
 */
final class ModelCollention extends CollectionImmutable
{
}

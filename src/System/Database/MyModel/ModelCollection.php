<?php

declare(strict_types=1);

namespace System\Database\MyModel;

use System\Collection\AbstractCollectionImmutable;

/**
 * @template TKey of int
 * @template TValue of ORM
 *
 * @extends AbstractCollectionImmutable<int, ORM>
 */
final class ModelCollention extends AbstractCollectionImmutable
{
}

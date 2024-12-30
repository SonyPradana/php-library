<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Database\MyQuery\Traits\ConditionTrait;
use System\Database\MyQuery\Traits\SubQueryTrait;

class Where
{
    use ConditionTrait;
    use SubQueryTrait;

    /** @var string Table Name */
    private $_table;

    /** This property use for helper phpstan (auto skip) */
    private ?InnerQuery $_sub_query = null;

    /**
     * Binder array(['key', 'val']).
     *
     * @var Bind[] Binder for PDO bind */
    private $_binds = [];

    /**
     * Final where statmnet.
     *
     * @var string[]
     */
    private $_where = [];

    /**
     * Single filter and single strict mode.
     *
     * @var array<string, string>
     */
    private $_filters = [];

    /**
     * Strict mode.
     *
     * @var bool True if use AND instance of OR
     */
    private $_strict_mode = true;

    public function __construct(string $table_name)
    {
        $this->_table = $table_name;
    }

    /**
     * Get raw property.
     *  - binds
     *  - where
     *  - filter
     *  - isStrict.
     *
     * @return array<string, Bind[]|string[]|array<string, string>|bool>
     */
    public function get(): array
    {
        return [
            'binds'     => $this->_binds,
            'where'     => $this->_where,
            'filters'   => $this->_filters,
            'isStrict'  => $this->_strict_mode,
        ];
    }

    /**
     * Reset all condition.
     */
    public function flush(): void
    {
        $this->_binds       = [];
        $this->_where       = [];
        $this->_filters     = [];
        $this->_strict_mode = true;
    }

    public function isEmpty(): bool
    {
        return [] === $this->_binds && [] === $this->_where && [] === $this->_filters && true === $this->_strict_mode;
    }
}

<?php

declare(strict_types=1);

namespace System\Database\MyQuery;

use System\Database\Interfaces\EscapeQuery;
use System\Database\MyPDO;
use System\Database\MyQuery\Identifiers\MySQLIdentifier;

abstract class Query implements EscapeQuery
{
    /** @var MyPDO PDO property */
    protected $PDO;

    /** @var string Main query */
    protected $_query;

    /** @var string Table Name */
    protected $_table = '';

    protected ?InnerQuery $_sub_query = null;

    /** @var string[] Columns name */
    protected $_column = ['*'];

    /**
     * Binder array(['key', 'val']).
     *
     * @var Bind[] Binder for PDO bind
     */
    protected $_binds = [];

    /** @var int Limit start from */
    protected $_limit_start = 0;

    /** @var int Limit end to */
    protected $_limit_end = 0;

    /** @var int offest */
    protected $_offset = 0;

    /** @var array<string, string> Sort result ASC|DESC */
    protected array $_sort_order  = [];

    public const ORDER_ASC  = 0;
    public const ORDER_DESC = 1;

    /**
     * Final where statmnet.
     *
     * @var string[]
     */
    protected $_where = [];

    /**
     * Grouping.
     *
     * @var string[]
     */
    protected $_group_by = [];

    /**
     * Multy filter with strict mode.
     *
     * @var array<int, array<string, array<string, array<string, string>>>>
     */
    protected $_group_filters = [];

    /**
     * Single filter and single strict mode.
     *
     * @var array<string, string>
     */
    protected $_filters = [];

    /**
     * Strict mode.
     *
     * @var bool True if use AND instance of OR
     */
    protected $_strict_mode = true;

    /**
     * @var string[]
     */
    protected $_join = [];

    protected ?EscapeQuery $_escape = null;

    /**
     * reset all property.
     *
     * @return self
     */
    public function reset()
    {
        $this->_table         = '';
        $this->_sub_query     = null;
        $this->_column        = ['*'];
        $this->_binds         = [];
        $this->_limit_start   = 0;
        $this->_limit_end     = 0;
        $this->_where         = [];
        $this->_group_filters = [];
        $this->_filters       = [];
        $this->_strict_mode   = true;
        $this->_escape        = null;

        return $this;
    }

    // Query builder

    /**
     * Get where statment baseon binding set before.
     *
     * @return string Where statment from binder
     */
    protected function getWhere(): string
    {
        $merging      = $this->mergeFilters();
        $where        = $this->splitGrupsFilters($merging);
        $glue         = $this->_strict_mode ? ' AND ' : ' OR ';
        $whereCostume = implode($glue, $this->_where);

        if ($where !== '' && $whereCostume !== '') {
            // menggabungkan basic where dengan costume where
            $whereString = $this->_strict_mode ? "AND $whereCostume" : "OR $whereCostume";

            return "WHERE $where $whereString";
        } elseif ($where === '' && $whereCostume !== '') {
            // hanya menggunkan costume where
            $whereString = $this->_strict_mode ? "$whereCostume" : "$whereCostume";

            return "WHERE $whereString";
        } elseif ($where !== '') {
            // hanya mengunakan basic where
            return "WHERE $where";
        }

        // return condition where statment
        return $where;
    }

    /**
     * @return array<int, array<string, array<string, array<string, string>>>>
     */
    protected function mergeFilters(): array
    {
        $new_group_filters = $this->_group_filters;
        if (!empty($this->_filters)) {
            // merge group filter and main filter (condition)
            $new_group_filters[] = [
                'filters' => $this->_filters,
                'strict'  => $this->_strict_mode,
            ];
        }

        // hasil penggabungan
        return $new_group_filters;
    }

    /**
     * @param array<int, array<string, array<string, array<string, string>>>> $group_filters Groups of filters
     */
    protected function splitGrupsFilters(array $group_filters): string
    {
        // mengabungkan query-queery kecil menjadi satu
        $whereStatment = [];
        foreach ($group_filters as $filters) {
            $single          = $this->splitFilters($filters);
            $whereStatment[] = "( $single )";
        }

        return implode(' AND ', $whereStatment);
    }

    /**
     * @param array<string, array<string, array<string, string>>> $filters Filters
     */
    protected function splitFilters(array $filters): string
    {
        $query      = [];
        $table_name = null === $this->_sub_query ? $this->_table : $this->_sub_query->getAlias();
        foreach ($filters['filters'] as $fieldName => $fieldValue) {
            $value        = $fieldValue['value'];
            $comparation  = $fieldValue['comparation'];
            $column       = str_contains($fieldName, '.') ? $fieldName : "{$table_name}.{$fieldName}";
            $bind         = $fieldValue['bind'];

            if ($value !== '') {
                $query[] = "({$this->escape($column)} {$comparation} :{$bind})";
            }
        }

        $clear_query = array_filter($query);

        return $filters['strict'] ? implode(' AND ', $clear_query) : implode(' OR ', $clear_query);
    }

    /**
     * Bind query with binding.
     */
    public function queryBind(): string
    {
        [$binds, $values] = $this->bindsDestructur();

        $quote_values = array_map(function ($value) {
            if (is_string($value)) {
                return "'" . $value . "'";
            }

            if (is_bool($value)) {
                if ($value === true) {
                    return 'true';
                }

                return 'false';
            }

            /* @phpstan-ignore-next-line */
            return $value;
        }, $values);

        return str_replace($binds, $quote_values, $this->builder());
    }

    protected function builder(): string
    {
        return '';
    }

    public function escape(?string $identifier): ?string
    {
        if ($this->_escape === null) {
            $this->_escape = new MySQLIdentifier();
        }

        return $this->_escape->escape($identifier);
    }

    public function setEscape(?EscapeQuery $escapeQuery): static
    {
        $this->_escape = $escapeQuery;

        return $this;
    }

    /**
     * @return array{0: string[], 1: mixed[], 2:mixed[]|null[]}
     */
    public function bindsDestructur(): array
    {
        $bind_name = [];
        $value     = [];
        $columns   = [];

        foreach ($this->_binds as $bind) {
            $bind_name[] = $bind->getBind();
            $value[]     = $bind->getValue();
            $column      = $this->escape($bind->getColumnName());
            if ($column !== null && !in_array($column, $columns, true)) {
                $columns[] = $column;
            }
        }

        return [$bind_name, $value, $columns];
    }

    /** @return Bind[]  */
    public function getBinds()
    {
        return $this->_binds;
    }

    /**
     * Add where condition from where referans.
     */
    public function whereRef(?Where $ref): static
    {
        if ($ref->isEmpty()) {
            return $this;
        }
        $conditon = $ref->get();
        foreach ($conditon['binds'] as $bind) {
            $this->_binds[] = $bind;
        }
        foreach ($conditon['where'] as $where) {
            $this->_where[] = $where;
        }
        foreach ($conditon['filters'] as $name => $filter) {
            $this->_filters[$name] = $filter;
        }
        $this->_strict_mode = $conditon['isStrict'];

        return $this;
    }
}

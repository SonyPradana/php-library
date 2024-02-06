<?php

declare(strict_types=1);

namespace System\Database;

use System\Database\MyQuery\Join\AbstractJoin;
use System\Database\MyQuery\Select;

abstract class MyModel
{
    /**
     * Kumpulan array filter.
     *
     * @var array<int, array<string, string[]|bool>>
     */
    protected $_GROUP_FILTER = [];

    /**
     * Primery filter.
     *
     * @var string[]
     */
    protected $_FILTERS = [];
    /** duplicate filter for where statmment */
    protected bool $_ALLOW_DUPLICATE_FILTERS = true;

    /**
     * Table yang dugunakan.
     *
     * @var string[]
     */
    protected $_TABELS  = [];

    /** column yang dugunaka.
     * @var array<int|string, string>
     */
    protected $_COLUMNS = ['*'];

    /** column order */
    protected string $_SORT_ORDER = 'id DESC';
    /** Logica where stactment [AND / OR] */
    protected bool $_STRICT_SEARCH = true;

    /**
     * costume where optional added to where statment.
     *
     * @var array<int, array<string, array<string|int, string>>>
     */
    protected $_COSTUME_WHERE = [];

    /** @var int Limit start from */
    protected $_limit_start = 0;
    /** @var int Limit end to */
    protected $_limit_end = 10;
    /** @var string costume join */
    protected $_COSTUME_JOIN = '';
    /** @var string[] costume join */
    protected $_JOIN = [];

    public const ORDER_ASC  = 0;
    public const ORDER_DESC = 1;

    /** @var MyPDO */
    protected $PDO;

    // setter

    /**
     * Combine where condition with 'AND' or 'OR'.
     * If true where condition using 'AND'.
     *
     * @return $this
     */
    public function setStrictSearch(bool $strict_mode)
    {
        $this->_STRICT_SEARCH = $strict_mode;

        return $this;
    }

    /**
     * Set data start for feact all data.
     *
     * @param int $val limit start default is 0
     *
     * @return $this
     */
    public function limitStart(int $val)
    {
        $this->_limit_start = $val;

        return $this;
    }

    /**
     * Set data end for feact all data
     * zero value meaning no data show.
     *
     * @param int $val limit start default
     *
     * @return $this
     */
    public function limitEnd(int $val)
    {
        $this->_limit_end = $val;

        return $this;
    }

    /**
     * Set sort column and order
     * column name must register.
     *
     * @return $this
     */
    public function order(string $column_name, int $order_using = MyModel::ORDER_ASC)
    {
        $order             = $order_using == 0 ? 'ASC' : 'DESC';
        $this->_SORT_ORDER = "$column_name $order";

        return $this;
    }

    /**
     * Add costume where to query.
     *
     * @param string                $statment Where query statment
     * @param array<string, string> $bind     Where query bind
     *
     * @return $this
     */
    public function costumeWhere(string $statment, array $bind)
    {
        $this->_COSTUME_WHERE[] = [
            'statment' => "($statment)",
            'bind'     => $bind,
        ];

        return $this;
    }

    /**
     * reset value of filters andor costume where,
     * to prevent duplicate.
     */
    public function reset(bool $costumeWhere = true): void
    {
        $this->_FILTERS      = [];
        $this->_GROUP_FILTER = [];
        if ($costumeWhere) {
            $this->_COSTUME_WHERE = [];
        }
    }

    /**
     * Join oner or more to antoher table.
     *
     * @param AbstractJoin $join             Type of join
     * @param bool         $use_parent_table True will replace perent table to this table
     *
     * @return $this
     */
    public function join(AbstractJoin $join, bool $use_parent_table = true)
    {
        // rewrite table relation with this current table
        if ($use_parent_table) {
            $join->table($this->_TABELS[0]);
        }

        $this->_JOIN[] = $join->stringJoin();

        return $this;
    }

    // getter
    /** Get this query */
    public function getQuery(): string
    {
        return $this->query();
    }

    /**
     * Get only where statment query.
     */
    public function getWhere(): string
    {
        return $this->grupQueryFilters($this->mergeFilters());
    }

    /**
     * Get filter array and groups filter.
     *
     * @return array<int, array<string, array<string, array<string, string>>|bool>> Groups array
     */
    public function getMegerFilters(): array
    {
        return $this->mergeFilters();
    }

    /**
     * mengenered grups filter ke-dalam format sql query (preapre statment).
     *
     * @return string query yg siap di esekusi
     */
    protected function query(): string
    {
        $table          = $this->_TABELS[0];
        $column         = implode(', ', $this->_COLUMNS);
        $where_statment = $this->getWhere();
        $where_statment = $where_statment == '' ? '' : "WHERE $where_statment";
        $sortOrder      = $this->_SORT_ORDER;
        $limit          = $this->_limit_start < 0 ? "LIMIT $this->_limit_end" : "LIMIT $this->_limit_start, $this->_limit_end";
        $limit          = $this->_limit_end < 1 ? '' : $limit;
        // merge join
        $this->_JOIN[] = $this->_COSTUME_JOIN;
        $join          = implode(' ', $this->_JOIN);

        return "SELECT $column FROM $table
      $join $where_statment ORDER BY $sortOrder $limit";
    }

    /**
     * sql query tanpa menggunkan where statment.
     */
    protected function originQuery(): string
    {
        $table     = $this->_TABELS[0];
        $column    = implode(', ', $this->_COLUMNS);
        $sortOrder = $this->_SORT_ORDER;
        // merge join
        $this->_JOIN[] = $this->_COSTUME_JOIN;
        $join          = implode(' ', $this->_JOIN);

        return "SELECT $column FROM $table
      $join ORDER BY $sortOrder";
    }

    // main function

    /**
     * menggabungkan primery filter array dengan groups filter, tanpa merubah isi groups class.
     * karean query di-runing dalam bentuk group filter.
     *
     * @return array<int, array<string, array<string, array<string, string>>|bool>> New Groups array
     */
    protected function mergeFilters(): array
    {
        $new_grups_filters = $this->_GROUP_FILTER;
        // menambahkan filter group yg sudah ada, dengan filter
        if (empty($this->_FILTERS) == false) {
            $new_grups_filters[] = [
                'filters' => $this->_FILTERS,
                'strict'  => $this->_STRICT_SEARCH,
            ];
        }

        // membuat group filter baru tanpa merubah grups filter dr classs
        return $new_grups_filters;
    }

    /**
     * @param array<int, array<string, array<string, array<string, string>>|bool>> $grup_fillters
     */
    protected function grupQueryFilters(array $grup_fillters): string
    {
        /** @var string[] */
        $where_statment = array_values(array_column($this->_COSTUME_WHERE, 'statment'));
        foreach ($grup_fillters as $filter) {
            $query = $this->queryfilters($filter['filters'], $filter['strict']);
            if (!empty($query)) {
                $where_statment[] = '(' . $query . ')';
            }
        }

        return implode(' AND ', $where_statment);
    }

    /**
     * @param array<string, array<string, string>> $filters
     */
    protected function queryfilters(array $filters, bool $strict = true): string
    {
        $querys   = [];
        // identitas
        foreach ($filters as $key => $val) {
            if (isset($val['value']) && $val['value'] !== '') {
                $param  = $val['param'] ?? $key;
                $id     = $val['id'] ?? '';
                $bind   = $param . $id;

                $option           = $val['option'] ?? ['imperssion' => [':', ''], 'operator'   => '='];
                $option['column'] = $val['column'] ?? '';

                $querys[] = $this->queryBuilder($param, $bind, $option);
            }
        }

        $arr_query = array_filter($querys);

        return $strict ? implode(' AND ', $arr_query) : implode(' OR ', $arr_query);
    }

    /**
     * @param array<string, string|string[]> $option
     */
    protected function queryBuilder(string $key, ?string $val, array $option = ['imperssion' => ["'%", "%'"], 'operator' => 'LIKE']): string
    {
        $column   = $option['column'] != '' ? $option['column'] . '.' : '';
        $operator = $option['operator'];
        $sur      = $option['imperssion'][0];
        $pre      = $option['imperssion'][1];
        if (null !== $val && '' !== $val) {
            return "($column$key $operator $sur$val$pre)";
        }

        return '';
    }

    /** Bind this query with set value from filter */
    protected function bindingFilters(): void
    {
        // binding from filter
        foreach ($this->mergeFilters() as $filters) {
            foreach ($filters['filters'] as $key => $val) {
                if (isset($val['value']) && $val['value'] !== '') {
                    $param = $key;
                    if (isset($val['param'])) {
                        $id    = $val['id'] ?? '';
                        $param = $val['param'] . $id;
                    }

                    $type = $val['type'] ?? null;
                    $this->PDO->bind($param, $val['value'], $type);
                }
            }
        }

        // binding from costume where
        $bindWhere = array_values(array_column($this->_COSTUME_WHERE, 'bind'));
        foreach ($bindWhere as $binds) {
            foreach ($binds as $bind) {
                $this->PDO->bind($bind[0], $bind[1]);
            }
        }
    }

    /**
     * Menapilakan single data dari query.
     *
     * @return mixed
     */
    public function single()
    {
        if ($this->PDO === null) {
            return [];
        }
        $this->PDO->query($this->query());
        $this->bindingFilters();

        return $this->PDO->single();
    }

    /**
     * Menampilkan data dari hasil query yang ditentukan sebelumnya.
     *
     * @return mixed[]|false Single result array
     */
    public function result()
    {
        if ($this->PDO == null) {
            return [];
        }                              // return null jika db belum siap
        $this->PDO->query($this->query());
        $this->bindingFilters();

        return $this->PDO->resultset();
    }

    /**
     * Menmpilkan semua data yang tersedia.
     *
     * @return mixed[]|false Return data without using filter
     */
    public function resultAll()
    {
        if ($this->PDO == null) {
            return [];
        }                          // return null jika db belum siap
        $this->PDO->query($this->originQuery());

        // binding from filter
        foreach ($this->mergeFilters() as $filters) {
            foreach ($filters['filters'] as $key => $val) {
                if (isset($val['value']) && $val['value'] != '') {
                    $param = $val['param'] ?? $key;
                    $type  = $val['type'] ?? null;
                    $this->PDO->bind(':' . $param, $val['value'], $type);
                }
            }
        }

        // binding from costume where
        $bindWhere = array_values(array_column($this->_COSTUME_WHERE, 'bind'));
        foreach ($bindWhere as $binds) {
            foreach ($binds as $bind) {
                $this->PDO->bind($bind[0], $bind[1]);
            }
        }

        return $this->PDO->resultset();
    }

    /**
     * Create new instance from static.
     *
     * @param MyPDO $pdo PDO DI
     */
    public static function call(?MyPDO $pdo = null): self
    {
        /* @phpstan-ignore-next-line */
        return new static($pdo);
    }

    /**
     * Its like costumeWhere() but more elegant syntax.
     * Intreget with Select() class.
     */
    public function select(): Select
    {
        return new Select($this->_TABELS[0], $this->_COLUMNS, $this->PDO, ['query' => $this->query()]);
    }
}

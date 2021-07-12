<?php

namespace System\Database\MyQuery;

abstract class Query
{
  /** @var MyPDO PDO property */
  protected $PDO;
  /** @var string Main query */
  protected $_query;
  /** @var string Table Name */
  protected $_table = null;
  /** @var array Columns name */
  protected $_column = array('*');
  /** @var array Binder for PDO bind */
  protected $_binder = array();  // array(['key', 'val'])
  /** @var int Limit start from */
  protected $_limit_start = 0;
  /** @var int Limit end to */
  protected $_limit_end = 0;
  /** @var int Sort result ASC|DESC */
  protected $_sort_order = '';
  const ORDER_ASC = 0;
  const ORDER_DESC = 1;

  // final where statmnet
  protected $_where = array();
  // Grouping
  protected $_group_by = null;

  // multy filter with strict mode
  protected $_group_filters = array();
  // single filter and single strict mode
  protected $_filters = array();
  protected $_strict_mode = true;

  // join
  protected $_join = '';

  /**
   * reset all property
   */
  public function reset()
  {
    $this->_table         = null;
    $this->_column        = array('*');
    $this->_binder        = array();
    $this->_limit_start   = 0;
    $this->_limit_end     = 0;
    $this->_where         = array();
    $this->_group_filters = array();
    $this->_filters       = array();
    $this->_strict_mode   = true;

    return $this;
  }

  // Query builder

  /**
   * Where statment setter,
   * menambahakan syarat pada query builder
   *
   * @param string $key Key atau nama column
   * @param string $comparation tanda hubung yang akan digunakan (AND|OR|>|<|=|LIKE)
   * @param string $value Value atau nilai dari key atau nama column
   */
  public function compare(string $bind, string $comparation, string $value, bool $bindValue = false)
  {
    $this->_binder[] = array($bind, $value);
    $this->_filters[$bind] = array (
      'value'       => $value,
      'comparation' => $comparation,
      $bindValue
    );
    return $this;
  }

  /**
   * Get where statment baseon binding set before
   * @return string Where statment from binder
   */
  protected function getWhere(): string
  {
    $merging      = $this->mergeFilters();
    $where        = $this->splitGrupsFilters($merging);
    $glue         = $this->_strict_mode ? ' AND ' : ' OR ';
    $whereCostume = implode($glue, $this->_where);

    if ($where != '' && $whereCostume != '') {
      // menggabungkan basic where dengan costume where
      $whereString = $this->_strict_mode ? " AND $whereCostume" : " OR $whereCostume";
      return "WHERE $where $whereString";
    } elseif ($where == '' && $whereCostume != '') {
      // hanya menggunkan costume where
      $whereString = $this->_strict_mode ? " $whereCostume" : " $whereCostume";
      return "WHERE $whereString";
    } elseif ($where != '') {
      // hanya mengunakan basic where
      return "WHERE $where";
    }
    // return condition where statment
    return $where;
  }

  protected function mergeFilters(): array
  {
    $new_group_filters = $this->_group_filters;
    if (! empty($this->_filters)) {
      // merge group filter and main filter (condition)
      $new_group_filters[] = array (
        'filters' => $this->_filters,
        'strict'  => $this->_strict_mode
      );
    }
    // hasil penggabungan
    return $new_group_filters;
  }

  protected function splitGrupsFilters(array $group_filters): string
  {
    // mengabungkan query-queery kecil menjadi satu
    $whereStatment = array();
    foreach ($group_filters as $filters) {
      $single = $this->splitFilters($filters);
      $whereStatment[] = "( $single )";
    }
    return implode(' AND ', $whereStatment);
  }

  protected function splitFilters(array $filters): string
  {
    // mengconvert array ke string query
    $query = array();
    foreach ($filters['filters'] as $fieldName => $fieldValue) {
      $value        = $fieldValue['value'];
      $comparation  = $fieldValue['comparation'];
      if ($value != null || $value != '') {
        $query[] = "($this->_table.$fieldName $comparation :$fieldName)";
      }
    }

    $clear_query = array_filter($query);
    return $filters['strict'] ? implode(' AND ',$clear_query) : implode(' OR ', $clear_query);
  }

  // this class must be overvrided
  protected function builder(): string
  {
    return '';
  }
}

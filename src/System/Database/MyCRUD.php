<?php namespace System\Database;

use System\Collection\Collection;
use System\Collection\CollectionImmutable;
use System\Database\MyPDO;
use System\Database\MyQuery\Join\InnerJoin;

abstract class MyCRUD
{
  /** @var MyPDO|null */
  protected $PDO = null;
  /** @var string */
  protected $TABLE_NAME;
  /** @var array */
  protected $COLUMNS = [];
  /** @var string */
  protected $PRIMERY_KEY = 'id';
  /** @var string|int */
  protected $IDENTIFER = '';
  /** @var array set Column cant be modify */
  protected $RESISTANT;
  /** @var array orginal data from database */
  protected $FRESH;

  public function getID()
  {
    return $this->IDENTIFER;
  }

  public function setID($val)
  {
    $this->IDENTIFER = $val;
    return $this;
  }

  protected function setter(string $key, $val)
  {
    if (key_exists($key, $this->COLUMNS) && !isset($key, $this->RESISTANT)) {
      $this->COLUMNS[$key] = $val;
    }
    return $this;
  }

  protected function getter($key, $defaul = null)
  {
    return $this->COLUMNS[$key] ?? $defaul;
  }

  public function __get($name)
  {
    return $this->getter($name);
  }

  public function __set($name, $value)
  {
    $this->setter($name, $value);
  }

  public function read(): bool
  {
    $key = $this->PRIMERY_KEY;
    $value = $this->IDENTIFER;
    $arr_column = array_keys($this->COLUMNS);

    $read = MyQuery::from($this->TABLE_NAME, $this->PDO)
      ->select($arr_column)
      ->equal($key, $value)
      ->limitStart(1)
      ->single()
    ;

    if ($read == []) {
      return false;
    }

    $this->COLUMNS = $this->FRESH = $read;
    return true;
  }

  public function cread(): bool
  {
    $create = MyQuery::from($this->TABLE_NAME, $this->PDO)
      ->insert()
      ->values($this->COLUMNS)
      ->execute()
    ;

    return $this->changing($create);
  }

  public function update(): bool
  {
    $key = $this->PRIMERY_KEY;
    $value = $this->IDENTIFER;

    $update = MyQuery::from($this->TABLE_NAME, $this->PDO)
      ->update()
      ->values($this->COLUMNS)
      ->equal($key, $value)
      ->execute()
    ;

    return $this->changing($update);
  }

  public function delete(): bool
  {
    $key = $this->PRIMERY_KEY;
    $value = $this->IDENTIFER;

    return MyQuery::from($this->TABLE_NAME, $this->PDO)
      ->delete()
      ->equal($key, $value)
      ->execute()
    ;
  }

  public function isExist(): bool
  {
    $key = $this->PRIMERY_KEY;
    $value = $this->IDENTIFER;

    $get = MyQuery::from($this->TABLE_NAME, $this->PDO)
      ->select(['id'])
      ->equal($key, $value)
      ->single()
    ;

    return $get == [] ? false : true;
  }

  public function getLastInsertID(): string
  {
    return $this->PDO->lastInsertId() ?? '';
  }

  public function convertFromArray(array $arr_column)
  {
    foreach ($arr_column as $key => $value) {
      $this->COLUMNS[$key] = $value;
    }

    return $this;
  }

  public function convertToArray(): array
  {
    return $this->COLUMNS;
  }

  public function toCollection(): Collection
  {
    return new Collection($this->COLUMNS);
  }

  protected function column_names(): array
  {
    $table_info = MyQuery::from($this->TABLE_NAME, $this->PDO)
      ->info()
    ;

    return array_values(array_column($table_info, 'COLUMN_NAME'));
  }

  public function __isset($name)
  {
    return isset($this->COLUMNS[$name]);
  }

  protected function hasOne(string $table, string $ref = 'id')
  {
    $ref = MyQuery::from($this->TABLE_NAME, $this->PDO)
      ->select([$table.'.*'])
      ->join(InnerJoin::ref($table, $this->PRIMERY_KEY, $ref))
      ->equal($this->PRIMERY_KEY, $this->IDENTIFER)
      ->limitStart(1)
      ->single()
    ;

  return new CollectionImmutable($ref);
  }

  protected function hasMany(string $table, string $ref = 'id')
  {
    $ref = MyQuery::from($this->TABLE_NAME, $this->PDO)
      ->select([$table.'.*'])
      ->join(InnerJoin::ref($table, $this->PRIMERY_KEY, $ref))
      ->equal($this->PRIMERY_KEY, $this->IDENTIFER)
      ->all()
    ;

  return new CollectionImmutable($ref);
  }


  private function changing(bool $change): bool
  {
    if ($change) {
      $this->FRESH = $this->COLUMNS;
    }

    return $change;
  }

  public function isClean(string $column = null): bool
  {
    if ($column == null) {
      return $this->COLUMNS == $this->FRESH;
    }

    if (isset($this->FRESH[$column])) {
      return false;
    }

    return $this->COLUMNS[$column] == $this->FRESH[$column];
  }

  public function isDirty(string $column = null): bool
  {
    return ! $this->isClean($column);
  }

}

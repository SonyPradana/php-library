<?php namespace System\Database;

use System\Database\CrudInterface;
use System\Database\MyPDO;

abstract class MyCRUD implements CrudInterface
{
  /** @var MyPDO */
  protected MyPDO $PDO;

  protected string $TABLE_NAME;
  /** @var array */
  protected array $COLUMNS = [];
  // TODO: merge ke FILTERS
  protected array $ID;

  protected function setter(string $key, $val)
  {
    $this->COLUMNS[$key] = $val;
    return $this;
  }
  protected function getter($key)
  {
    return $this->COLUMNS[$key];
  }

  public function read(): bool
  {
    $get_colomn = $this->getColumn();
    $get_table  = $this->TABLE_NAME;
    $get_id_key = array_keys($this->ID)[0];
    $get_id_val = array_values($this->ID)[0];

    $this->PDO->query(
      "SELECT
        $get_colomn
      FROM
        $get_table
      WHERE
      `$get_id_key` = :$get_id_key"
    );

    $this->PDO->bind(':' . $get_id_key, $get_id_val);
    if( $this->PDO->single() ) {
      $this->COLUMNS = $this->PDO->single();
      return true;
    }
    return false;
  }

  public function cread(): bool
  {
    $get_table  = $this->TABLE_NAME;
    $get_column = $this->column_names();
    $column_name = implode('`, `', $get_column);
    $column_bind = implode(', :', $get_column);

    $this->PDO->query(
      "INSERT INTO
        `$get_table`
        (`$column_name`)
      VALUES
        (:$column_bind)"
    );
    // binding
    foreach ($this->COLUMNS as $key => $val) {
      $this->PDO->bind(':' . $key, $val);
    }

    $this->PDO->execute();
    if ($this->PDO->rowCount() > 0) {
        return true;
    }
    return false;
  }

  public function update(): bool
  {
    $get_table  = $this->TABLE_NAME;
    $get_id_key = array_keys($this->ID)[0];
    $get_id_val = array_values($this->ID)[0];
    $get_set = $this->queryFilters($this->COLUMNS);

    $this->PDO->query(
      "UPDATE
        $get_table
      SET
        $get_set
      WHERE
        `$get_id_key` = :$get_id_key"
    );

    // binding
    foreach( $this->COLUMNS as $key => $val) {
      if(isset($val) && $val !== '') {
        $this->PDO->bind(':' . $key, $val);
      }
    }
    $this->PDO->bind(':' . $get_id_key, $get_id_val);
    $this->PDO->execute();

    if( $this->PDO->rowCount() > 0){
        return true;
    }
    return false;
  }

  public function delete(): bool
  {
    $this->PDO->query(
      "DELETE FROM $this->TABLE_NAME WHERE `id` = :id"
    );
    $this->PDO->bind(':id', $this->ID['id']);
    $this->PDO->execute();

    if ($this->PDO->rowCount() > 0) {
      return true;
    }
    return false;
  }

  public function isExist(): bool
  {
    $this->PDO->query(
      "SELECT `id` FROM $this->TABLE_NAME WHERE `id` =  :id"
    );
    $this->PDO->bind(':id', $this->ID['id']);
    $this->PDO->execute();
    if ($this->PDO->rowCount() > 0) {
      return true;
    }
    return false;
  }

  public function getLastInsertID(): string
  {
    return $this->PDO->lastInsertId() ?? '';
  }

  public function convertFromArray(array $cloulumnValue)
  {
    $this->COLUMNS = $cloulumnValue;
    return true;
  }
  public function convertToArray(): array
  {
    return $this->COLUMNS;
  }

  protected function column_names()
  {
    $this->PDO->query(
      "SELECT
        COLUMN_NAME
      FROM
        INFORMATION_SCHEMA.COLUMNS
      WHERE
        TABLE_SCHEMA = :dbs AND TABLE_NAME = :table
    ");
    $this->PDO->bind(':dbs', DB_NAME);
    $this->PDO->bind(':table',  $this->TABLE_NAME);
    $this->PDO->execute();
    $column_name = $this->PDO->resultset();
    return array_values(array_column($column_name, 'COLUMN_NAME'));
  }

  // helper
  private function getColumn(): string
  {
    $get_column = array_keys($this->COLUMNS);
    $get_column = array_map(function($x){
      return '`' . $x . '`';
    }, $get_column);

    return implode(', ', $get_column);
  }

  protected function queryFilters(array $filters)
  {
    $query = [];
    foreach($filters as $key => $val) {
      if(isset($val) && $val !== '') {
        $query[] = $this->queryBuilder($key, $key, [
          'imperssion' => [':', ''],
          'operator' => '='
        ]);
      }
    }

    $arr_query = array_filter($query);
    return implode(', ', $arr_query);
  }

  protected function queryBuilder($key, $val, array $option = ["imperssion" => ["'%", "%'"], "operator" => "LIKE"])
  {
    $operator = $option["operator"];
    $sur = $option["imperssion"][0];
    $pre = $option["imperssion"][1];
    if( isset( $val ) && $val !== '') {
        return "$key $operator $sur$val$pre";
    }
    return "";
  }


}

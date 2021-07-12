<?php

namespace System\Database;

use System\Database\MyQuery\Table;

/**
 * Query Builder untuk mempermudah pembuatan/penyusunan query,
 * penyusunan query menggunkan chain-function,
 * sehingga query lebih mudah dibaca/di-maintance
 *
 * TODO: join table support
 *
 * @return String String query yang disusun seblumnya
 */
class MyQuery
{
  const ORDER_ASC   = 0;
  const ORDER_DESC  = 1;
  protected $PDO    = null;

  public function __construct(MyPDO $PDO = null)
  {
    $this->PDO = $PDO ?? new MyPDO();
  }

  public function __invoke(string $table_name)
  {
    return new Table($table_name, $this->PDO);
  }

  public function table(string $table_name)
  {
    return new Table($table_name, $this->PDO);
  }

  public static function from(string $table_name, MyPDO $PDO = null)
  {
    $conn = new MyQuery($PDO);
    return $conn->table($table_name);
  }
}

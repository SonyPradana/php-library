<?php namespace System\Database;

use \PDO;
use \PDOException;

class MyPDO
{
  private $host = DB_HOST;
  private $user = DB_USER;
  private $pass = DB_PASS;

  private $dbh;
  private $stmt;

  public function __construct(string $database_name = DB_NAME)
  {
    // konfigurasi driver
    $dsn = 'mysql:host=' . $this->host . ';dbname=' . $database_name;
    $option = array (
      PDO::ATTR_PERSISTENT => true,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    );

    // menjalankan koneksi daabase
    try {
      $this->dbh = new PDO($dsn, $this->user, $this->pass, $option);
    } catch(PDOException $e) {
      die($e->getMessage());
    }
  }

  private static $MySelf = null;

  public static function conn(string $database_name = DB_NAME)
  {
    return new MyPDO($database_name);
  }

  /**
   * Singleton pattern implemnt for Databese connation
   *
   * @param string $database_name string Database Name   *
   * @return MyPDO MyPDO with singleton
   */
  public static function getInstance(string $database_name = DB_NAME)
  {
    self::$MySelf = self::$MySelf ?? new static($database_name);

    return self::$MySelf;
  }

  /**
   *  mempersiapkan statement pada query
   */
  public function query(string $query)
  {
    $this->stmt = $this->dbh->prepare($query);
    return $this;
  }

  /**
   * menggantikan paramater input dari user dengan sebuah placeholder
   */
  public function bind($param, $value, $type = null)
  {
    if (is_null($type)) {
      switch (true){
        case is_int($value);
          $type = PDO::PARAM_INT;
          break;

        case is_bool($value);
          $type = PDO::PARAM_BOOL;
          break;

        case is_null($value);
          $type = PDO::PARAM_NULL;
          break;

        default;
          $type = PDO::PARAM_STR;
      }
    }
    $this->stmt->bindValue($param, $value, $type);
    return $this;
  }

  /**
   * menjalankan atau mengeksekusi query
   */
  public function execute()
  {
    return $this->stmt->execute();
  }

  /**
   * mengembalikan hasil dari query yang dijalankan berupa array
   */
  public function resultset()
  {
    $this->execute();
    return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * mengembalikan hasil dari query, ditampilkan hanya satu baris data saja
   */
  public function single()
  {
    $this->execute();
    return $this->stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * menampilkan jumlah data yang berhasil di simpan, di ubah maupun dihapus
   */
  public function rowCount()
  {
    return $this->stmt->rowCount();
  }

  /**
   * id dari data yang terakhir disimpan
   * @return string last id
   */
  public function lastInsertId()
  {
    return $this->dbh->lastInsertId();
  }

  public function beginTransaction()
  {
    return $this->dbh->beginTransaction();
  }

  public function endTransaction()
  {
    return $this->dbh->commit();
  }

  public function cancelTransaction(){
    return $this->dbh->rollBack();
  }
}

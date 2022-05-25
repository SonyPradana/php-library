<?php namespace System\Database;

use \PDO;
use \PDOException;

class MyPDO
{
  /**
   * PDO class.
   *
   *  @var \PDO PDO
   */
  private $dbh;

  /**
   * PDO Statment.
   *
   * @var \PDOStatement
   */
  private $stmt;

  /**
   * Array config.
   *
   * @var array
   */
  private $configs;

  public function __construct(array $configs)
  {
      $database_name    = $configs['database_name'];
      $host             = $configs['host'];
      $user             = $configs['user'];
      $pass             = $configs['password'];

      $this->configs = $configs;

    // konfigurasi driver
    $dsn = "mysql:host=$host;dbname=$database_name";
    $option = array (
      PDO::ATTR_PERSISTENT => true,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    );

    // menjalankan koneksi database
    try {
      $this->dbh = new PDO($dsn, $user, $pass, $option);
    } catch(PDOException $e) {
      die($e->getMessage());
    }
  }

  /** Create connaction using static */
  public static function conn(array $configs)
  {
    return new self($configs);
  }

  /**
   * Get config connection.
   *
   * @return array
   */
  public function configs()
  {
      return $this->configs;
  }

  /**
   *  mempersiapkan statement pada query
   */
  public function query(string $query): self
  {
    $this->stmt = $this->dbh->prepare($query);
    return $this;
  }

  /**
   * menggantikan paramater input dari user dengan sebuah placeholder
   */
  public function bind($param, $value, $type = null): self
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
   * Menjalankan atau mengeksekusi query
   *
   * @return bool True if success
   * @throws \PDOException
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
   *
   * @return int The number of rows.
   */
  public function rowCount()
  {
    return $this->stmt->rowCount();
  }

  /**
   * id dari data yang terakhir disimpan
   * @return string|false last id
   */
  public function lastInsertId()
  {
    return $this->dbh->lastInsertId();
  }

  public function transaction(callable $callable)
  {
      if ($allow_tansaction = $this->beginTransaction()) {
          return $allow_tansaction;
      }

      if ($callable === false) {
          return false;
      }

      if ($success_commit = $this->endTransaction()) {
          return $success_commit;
      }
  }

  /**
   * Initiates a transaction
   *
   * @return bool True if success
   * @throws \PDOException
   */
  public function beginTransaction(): bool
  {
    return $this->dbh->beginTransaction();
  }

  /**
   * Commits a transaction
   *
   * @return bool True if success
   * @throws \PDOException
   */
  public function endTransaction(): bool
  {
    return $this->dbh->commit();
  }

  /**
   * Rolls back a transaction
   *
   * @return bool True if success
   * @throws \PDOException
   */
  public function cancelTransaction(): bool
  {
    return $this->dbh->rollBack();
  }
}

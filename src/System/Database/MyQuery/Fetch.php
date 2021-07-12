<?php

namespace System\Database\MyQuery;

abstract class Fetch extends Query
{
  public function single(): array
  {
    $this->builder();

    $this->PDO->query($this->_query);
    foreach ($this->_binder as $bind) {
      $this->PDO->bind($bind[0], $bind[1]);
    }
    $result = $this->PDO->single();
    return $result == false ? array() : $this->PDO->single();
  }

  public function all(): array
  {
    $this->builder();

    $this->PDO->query($this->_query);
    foreach ($this->_binder as $bind) {
      $this->PDO->bind($bind[0], $bind[1]);
    }
    return $this->PDO->resultset();
  }
}

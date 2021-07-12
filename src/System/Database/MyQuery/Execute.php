<?php

namespace System\Database\MyQuery;

abstract class Execute extends Query
{
  public function execute(): bool
  {
    $this->builder();

    if ($this->_query != null) {
      $this->PDO->query($this->_query);
      foreach ($this->_binder as $bind) {
        $isVal = $bind[2] ?? false;
        $binder = $isVal ? "val_$bind[0]" : $bind[0];
        $this->PDO->bind($binder, $bind[1]);
      }

      $this->PDO->execute();
      return $this->PDO->rowCount() > 0 ? true : false;
    }
    return false;
  }
}

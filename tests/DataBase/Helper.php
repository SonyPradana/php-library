<?php

// this class is never call
// just avoid error in MyPDO classes

if (! defined('DB_HOST')) {
  define('DB_HOST', "");
}

if (! defined('DB_USER')) {
  define('DB_USER', "");
}

if (! defined('DB_PASS')) {
  define('DB_PASS', "");
}

if (! defined('DB_NAME')) {
  define('DB_NAME', "");
}

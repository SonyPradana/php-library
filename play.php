<?php

use System\Template\Generate;
use System\Template\Property;

use function System\Console\ok;

require __DIR__ . '/vendor/autoload.php';

$class = new Generate('test');
$class->tabSize(4);
$class->tabIndent(' ');
$class->setEndWithNewLine();
$class->namespace('App\\Models\\');
$class->uses(['System\Database\MyModel\Model']);
$class->addComment('@property mixed $user');
$class->addComment('@property mixed $pwd');
$class->addComment('@property mixed $stat');
$class->extend('Model');
$class->addProperty('table_name')->visibility(Property::PROTECTED_)->dataType('string')->expecting(" = 'users'");
$class->addProperty('primery_key')->visibility(Property::PROTECTED_)->dataType('string')->expecting("= 'user'");
ok ($class)->out();

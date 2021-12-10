# PHP MVC

Php mvc with minum mvc framework. is simple and easy to use

> **Note:** This repository contains the core code of the php-mvc. If you want to build an application, visit the main [php-mvc](https://github.com/SonyPradana/php-mvc).

> **Note:** This repository high inspire with `laravel\framework` and `symfony\symfony`.

## Feature
- MVC base 
- Route
- Model (database class relation)
- View and Controller
- [MyQuery](#Built-in-Query-Builder) (database query builder)
- [Collection](#Collection) (array collection)
- [Console](#Console) (assembling beutifull console app)
- Template (create class using class generator)
- Cron
- Now (time managing)
- Http request and responese

## **Built in Query Builder**
of cource we are support CRUD data base, this a sample

### Select data 
```php
MyQuery::from('table_name')
  ->select(['column_1'])
  ->equal('column_2', 'fast_mvc')
  ->order("column_1", MyQuery::ORDER_ASC)
  ->limit(1, 10)
  ->all()
;  
```
the result will show data from query,
its same with SQL query
```SQL
SELECT `column_1` FROM `table_name` WHERE (`column_2` = 'fast_mvc') ORDER BY `table_name`.`column_1` ASC LIMIT 1, 10
```
[🔝 Back to contents](#Feature)

### Update data 
```php
MyQuery::from('table_name')
  ->update()
  ->values([
    'column_1' => 'simple_mvc',
    'column_2' => 'fast_mvc',
    'column_3' => 123
  ])
  ->equal('column_4', 'fast_mvc')
  ->execute()
;
```
the result is boolen true if sql success excute quert,
its same with SQL query
```SQL
UPDATE `table_name` SET `column_1` = 'simple_mvc', `column_2` = 'fast_mvc', 'column_3' = 123  WHERE (`column_4` = 'speed')
```
[🔝 Back to contents](#Feature)

### Insert and Delete
```php
// insert
MyQuery::from('table_name')
  ->insert()
  ->values([
    'column_1'  => '',
    'column_2'  => 'simple_mvc',
    'column_3'  => 'fast_mvc'
    ])
  ->execute()
;
// delete
MyQuery::from('table_name')
  ->delete()
  ->equal('column_3', 'slow_mvc')
  ->execute()
;
```
its supported cancel transation if you needed

[🔝 Back to contents](#Feature)

## Collection 
Array collection, handel functional array as chain method

### Create New Collection
```php
$coll = new Collection(['vb_net', 'c_sharp', 'java', 'python', 'php', 'javascript', 'html']);

$arr = $coll
  ->remove('html')
  ->sort()
  ->filter(fn ($item) => strlen($item) > 4)
  ->map(fn ($item) => ucfirst($item))
  ->each(function($item) {
    echo $item . PHP_EOL;
  })
  ->all()
;

// arr = ['c_sharp', 'javascript', 'python', 'vb_net']
```
[🔝 Back to contents](#Feature)

### Available Methods
- `add()`
- `remove()`
- `set()`
- `clear()`
- `replace()`
- `each()`
- `map`
- `filter()`
- `sort()`
- `sortDesc()`
- `sortKey()`
- `sortKeyDesc()`
- `sortBy()`
- `sortByDecs()`
- `all()`

[🔝 Back to contents](#Feature)

## Console

Assembling beautifull console app make easy

- naming parameter
- coloring console (text and background)

### Build simple console app
```php
class GreatConsole extends Console
{
  public function println()
  {
    // getter to get param form cli argument
    $name = $this->name ?? 'animus';

    $this->prints(
      $this->textGreen("Great console application\n"),
      "hay my name is " . $this->bgYellow($name)
    );
  }

  public function printHelp()
  {
   return array(
     'option' => array(
        "run" . $this->tab(3) . "Run greate app"
     ),
     'argument' => array(
        $this->textDim("--name") . $this->tab(3) . "Set file name"
     )
   );
  }
}
```

**Run your app**

- create bootstrapper
```php
#!usr/bin/env php

// $argv come with default global php 
return (new greatConsole($argv))->println();

```

- on your console
```bash
php cli greate --name php_mvc

# output:
# Great console application
# hay my name is php_mvc
```
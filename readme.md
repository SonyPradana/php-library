# PHP MVC

Php mvc with minum mvc framework. is simple and easy to use

> **Note:** This repository contains the core code of the php-mvc. If you want to build an application, visit the main [php-mvc](https://github.com/SonyPradana/php-mvc).

## Feature
- mvc structur
- re-use mvc (easy to maintenance)
- [Router](http://github.com/steampixel/simplePHPRouter) Support
- models builder
- query builder
- secure 'public/' folder
- builtin npm (sass, traser) -> minify css, js
- CLI command
- ready to use Rest API (get, post, put)

## Optional Feature
- laravel-mix
- tailwind css
- vue router

this app is ready to use vue route, be default vue route is disable using comment. if you want just remove comment and run your ```npm```.

- laravel mix
```js
// vue router - optional
// mix.js('resources/vue/app.js', 'public/vue')
//   .postCss("resources/vue/css/app.css", "public/vue/css", [
//     require("tailwindcss"),
//   ])
```
- vue router
```php
// vue apps router - optional
// if you use vue-router (sub path) forget register router here
// Route::get('/(:text)', function() {
//   return (new VueAppController)->index();
// });
```

## Serve your apps (4 steps)
- clone this repository
```bash
git clone https://github.com/SonyPradana/php-mvc my-project-name
 ```
 - composer update
 ```bash
 composer install
 ```
- building recouce css / js (optional)
```bash
npm install
npm run dev
```
- serve your page
```bash
php -S 127.0.0.1:3000 -t public/
# or using cli command
php cli serve
```
### Short hand to setup project
you can do manual by follow instruction above, or run this command to easy setup (its same result)
```bash
# type or copy this command to your terminal
./bin/setup.sh
```
## Built in cli command
### make controll and view
```bash
php cli make:controller controllerName
```
### make model /models
model name is singular
```bash
php cli make:model user --table-name=users
php cli make:models user --table-name=users
```
before you make model, make sure database config has set.
to config your database you must copy ```.env.example``` to ```.env```.

## Built in Query Builder
of cource we are support CRUD data base, this a sample
### Select data 
```php
$db = new MyQuery();
$db('table_name')
  ->select(['column_1'])
  ->equal('column_2', 'fast_mvc')
  ->all();  
```
the result will show data from query,
its same with SQL query
```SQL
SELECT `column_1` FROM `table_name` WHERE (`column_2` = 'fast_mvc')
```
also support join table
### Update data 
```php
$db = new MyQuery();
$db('table_name')
  ->update()
  ->value('column_3', 'simple_mvc')
  ->equal('column_2', 'fast_mvc')
  ->execute();  
```
the result is boolen true if sql success excute quert,
its same with SQL query
```SQL
UPDATE `table_name` SET `column_3` = 'simple_mvc' WHERE (`column_2` = 'fast_mvc')
```
### Also support Insert and Delete
```PHP
// insert
$db = new MyQuery();
$db('table_name')
  ->insert()
  ->values([
    'column_1'  => '',
    'column_2'  => 'simple_mvc',
    'column_3'  => 'fast_mvc'
    ])
  ->execute();
// delete
$db('table_name')
  ->delete()
  ->equal('column_3', 'slow_mvc')
  ->execute();
```
its supported cancel transation if you needed

## Cron job schaduler
Command chaduler for run cron job, just run one cron job to handle all shaduler. cli will automatily run your any shaduler base time you set.

### Register command
```php
// ../app/command/CronCommand.php
class CronCommand
{
  public function schaduler(Schadule $schadule): void
  {
    // put your schaduller here
    $schadule->call(function() {
      echo "Its Cron Job Schaduller, run every hour\n";
    })
    ->hourly();
    // add schaule as many as possible
  }
}
```
#### avilable time
 - justintime - run every cron call
 - everyTenMinute - run every 10 minute
 - everyThirtyMinutes - run every 30 minute
 - everyTwoHour - run every 2 hour
 - everyTwelveHour - run every 12 hour / half day
 - hourly - run every 1 hour
 - haurlyAt - run every single hour (costume time)
 - daily - run every 00.00 
 - dailyAt - run every single day 1-31 (costume time)
 - weekly - run every week (sunday)
 - mountly - run every frist day in mount

### Cli command
```bash
  php CLI cron
  # will run shaduler in the same time you type

  php CLi cron:work
  # simultanly run cronjob every minute
```
### Setup local cron schaduller
here the sample use ```cron:work```:
```bash
Simulate Cron in terminal (every minute)

Ctrl+C to stop
Run Cron at - Fri, 20 19
Run Cron at - Fri, 20 20
Run Cron at - Fri, 20 21
...
...
Run Cron at - Fri, 24 59
```
will cek every minute in your schadule
### Setup serve cron schaduller
on your server cron job command type
```bash
* * * * * * usr/local/bin/php ../bin/cron.script.php >/dev/null 2>&1
```
locate cron to cron.script.php
### Setup 


## Update and Maintenance 🚀
this repository will be maintans every thursday or friday (probely 😅), Open contribution

## Todo
- Support Basic Auth
- Support MiddleWare Router

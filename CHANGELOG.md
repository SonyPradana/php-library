# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### [0.16.0] - 2023-01-21
### Added
- Added `\System\Database\MySchema\Create::class`, `\System\Database\MySchema\Drop::class`, `\System\Database\MySchema\Truncate::class` to crate/drop database or table. Or using shorthand 'MySchema::class`.

### [0.15.0] - 2023-01-10
### Added
- Added `Query::queryBind()` method to return query string with the bind's.
- Added support `Query::class` limit using `offet`.
- Added `Collection::shuffle()`, `Collection::rand()`, `Collection::current()`, `Collection::next()` and `Collection::prev()`.

### Fixed
- Fixed concat between join and where condition in query builder.
- Fixed return query bind boolean return as boolean.

### [0.14.0] - 2022-12-24
- Allowed schadule logging event job execute has error (#90).
- Fixed query limit less that 0 (#91).

### Added
- Added `Request::macro` to upload file (#92).
- Added support database insert multy raw (#98).

### Changed
- Collection implements `Countable`, `ArrayAccess` and `IteratorAggregate` interface (#93) (#97).
- Database query using Bind as Class `Bind::class` (#94).

### [0.13.0] - 2022-11-14
### Changed
- Changed `Request::all` with check content type.
- Changed `Request` from json request, return only json body from content.
- Changed `Requet::all` return base on method and return files (if avilable). Post Method return only post, and Get Method return only get.
- Change `Str::class` using `Macro::trait`.

### Added
- Added `Request::isJson` determinate request from json request.
- Added `Request::input()` return merge post and query request.
- Added `Macro::trait` to add macro in class.
- Added integrate Request-Validation (servise provider) macro.
- Added `Collection::ref` to add refrence (add collection in collection).
- Added support cron job to `retry` and `retryCondition()`.
- Added `Now::shortDay` property.

### Removed 
- Removed `AbstractMiddleware::class`.

### [0.12.4] - 2022-11-09
### Added
- Added support Request Iterator interface (loop class-object).

### [0.12.3] - 2022-11-04
### Added
- Added `Request::getAttribute()`.
- Added `Request::initialize()`.

### [0.12.2] - 2022-11-04
### Added
- Added `Request::isAjax()` detect ajax request.

### Removed
- Removed `AbstracMiddleware::handle()`.

- Added 
### [0.12.1] - 2022-10-21
### Fixed
- Fix error when create from global to get content body #66.

### [0.12.0] - 2022-10-21
### Added
- Added `Style::tap()` push style class.
- `Request::class` implement ArrayAccess.
- `Request::class` supported higher order expectations.
- Added `Request::query()`, `Request::post()` return collection $__GET, $__POST.

### Changed
- Change method name `Request::allin()` to `Request::wrap()`.
- `Request::__constructor` parameter not nullable.
- `Request::rawBody()` return string|null.
- `Request::getJsonBody()` throw exception when rawBody is not array/json and empty body.
- `Request::all()` return body content if content length not equal zero or empty.

### [0.11.0] - 2022-09-11
### Deprecated
- Deprecated set header from content['header'] in `Respone::class`.
- Deprecated set header from content['header'] in `View::render`.
- Deprecated `Command::printHelp`.

### Added
- Added trait to print help option and argument using array.
- Added `Respone::__toString` to get respone content include http version, header, and content.

### Changed
- Using kay-val array instead of header line array.


## [0.10.5] - 2022-08-19
### Fixed
-  Added laravel `dont-discover` package.

### Changed
- Add composer branch alias for `dev-master` to `0.x-dev`.

## [0.10.4] - 2022-08-17
### Fixed
- Fixed default when route middleware group not found.
- Fixed use `apache_request_headers` when function is exists.

## [0.10.3] - 2022-12-08
### Fixed
- Fixed support `Style::lenght` with integer parameter.

## [0.10.2] - 2022-04-08
### Added
- Added `Style::textColor` and `Style::BgColor` with paramter as `ForegroundColor` or `BackgroundColor` or string hex color.
- Support color variant call directly uning magic call (eg `Style::text_blue_500`).
- Added `Style::lenght()` count text lenght without count rule.

### Changed
- `Style::raw` will add specific on textColor or bgColor.

## [0.10.1] - 2022-29-07
### Added
- Added method `Colors::rgbText`, `Colors::rgbBg`.
- Added `Style::repeat`, `Style::new_lines` and `Style::tabs`.
- Added property to define terminal color `ForegroundColor::class` and `BackgroundColor::class`.

### Changed
- Updated php-cs-fixer to "3.9".
- Changed class from `Rule::class` to `Style::class`.
- `Style::push()` return as self.
- Change from 255 color to true color teminal `Colors::class`.
- Changed method `Colors::RawHexText` to `Colors::hexText`, `Colors::RawHexBg` to `Colors::hexBg`.
- Push no longger print out before method `out` call.
- Use `chr(27)` intead of "\e" for escape charakter.

## [0.10.0] - 2022-07-07
### Added
- Added default argument for parse console argument in `Command::class`.
- Added more terminal color pallet.
- Added reset parameter in method rule `CommandTrait::rule($rule, $text, $rest, $reset_rule)`.
- Added feature to remove qoute (single or double) in parse console argument.
- Added console/terminal decoration with chainging way `System\Console\Style\Rule::class`;

### Changed
- Changed deafault method to call from `Command::println` to `Command::main`.
- Use `CommandTrait` in `Console::class` is optional.

## [0.9.1] - 2022-06-30
### Changed
- Rename NameSpace `System\Support\Facedes` to `System\Support\Facades`.
- Rename class from `Facede::class` to `Facade::class`.
- Changed `mb_string` instance of string function in `Str::class`.
- Changed doc block `MyCRUD::SetID()` return as `static` instance of `self`.

## [0.9.0] - 2022-06-24
### Added
- Added `Facade::class` abstract class.
- Added `System\Support\Facades\DB::class` and `System\Support\Facades\PDO::class`
- Added `MyPDO::instance`.
- Added `CrossJoin::class` query builder for join table query.

### Changed
- PHPStan level 6 for `System\Database` exclude `MyModel::class`.

### Removed 
- Remove `MyQuery::getInstance`.

## [0.8.0] - 2022-06-17
### Added
- Added `Str::limit` for trucate text.
- Added `FileMultyUpload::class` for handle multy files upload.
- Added `UploadFile::class` support test `UploadFile::markTest` with use `copy` instance of `move_uploaded_file`.
- Added `UploadFile::get` for get file content of uploaded file.

### Changed
- Changed `UploadFile::folder_location` not provide `$_SERVER['root']`.
- Changed `UploadFile::Success` to `UploadFile::success`.
- Changed visibility `Time::class` to prevent ilegal changes property witch make property not sync.

### Security
- Folder location check `UploadFile::setFolderLocation`.
- File extention check `UploadFile::validate`.

### Fixed
- File size check `UploadFile::validate` always passed.

## [0.7.1] - 2022-06-09
### Added
- Added `Str::fill()`, `Str::fillEnd()` and `Str::mask()`.
- Added `Text::fill()`, `Text::fillEnd()` and `Text::mask()`.

### Fixed
- Fixed `MyCRUD::setter()` check `MyCRUD::RESISTANT` always true.
- Fixed `MyCRUD::getLastInsertID()` cant set default id (if id is empty).
- Fixed `MyPDO::transaction` return `MyPDO::beginTransaction` without run callable function.

### Changed
- Changed `Str::class` to final class.
- Changed `Select::class` to final class.
- Changed `Application::class` to final class.
- Changed `RouteDispatcher::class` to final class.
- Changed `ServiceProvider::__construct()` param from `Container::class` to `Application::class`.
- Changed `View::render` throw exception when file not found.

### Security
- `MyModel::call()`, Unsafe usage of `new static()`.
- `MyPDO::$Instance`, Unsafe usage of `new static()`.
- `AbstractJoin::ref()`, Unsafe usage of `new static()`.
- `Controller::static()`, Unsafe usage of `new static()`.
- `Router::getRoutes()`, Call to an undefined method `System\Router\Route::route()`.

## [0.7.0] - 2022-06-06
### Added 
- Added `MyPDO::config()` PDO connection configure.
- Added string manipulation `Str::chartAt`, `Str::concat`, `Str::indexOf`, `Str::lastIndexOf`, `Str::match`, `Str::repalce`, `Str::search`, `Str::slice`, `Str::split`, `Str::toLowerCase`, `Str::toUpperCase`, `Str::firstUpper`, `Str::firstUppeAll`, `Str::toPascalCase`, `Str::toCamelCase`, `Str::contains`, `Str::startsWith`, `Str::endsWith`, `Str::template`, `Str::slug`, `Str::repeat`, `Str::length`, `Str::isString`, `Str::isEmpty`, `Str::isMatch`.
- Added string macro `System\Text\Str::macro`.
- Added common regex `System\Text\Regex::class`.
- Added string class for manipulation string `System\Text\Text::class`.

### Changed
- Change composer require php version `7.4` and `8.0`.

## [0.6.2] - 2022-05-27
### Changed
- New `MyPDO::__construct` params, using array contain database configuration.
- `MyPDO::getInstance()` return single instance.
- New `MyPDO::__construct` throwing error without call `die` function.

### Removed
- Remove database defineder (`DB_NAME`, `DB_HOST`, `DB_USER`, `DB_PASS`).

### Fixed
- Fixed `Route::group` can't combine middleware.

## [0.6.1] - 2022-05-19
###  Added
- Prevent duplicate middleware call.
- Added method (`Route::middleware`) to call middleware individualy.

### Changed
- Change `Router::middleware` param form middleware object to middlewar class-name.
- `RouterGroup::group` retrun as callback param.

## [0.6.0] - 2022-05-15
### Added
- Added `RouteDispatcher::class` dispatch request route.

### Changed
- `Router::class` run throw `RouteDispatcher::class`.
- `View::render` return as `Respone::class`.
- `respone->html()`, `respone->json()`, `respone->planText()` no longger send respone. Its just set content type.

## [0.5.0] - 2022-04-29
### Added
- Added `flush()` method to reset application container.
- Added global function `now()` instance of `System\Time\Now::class`.
- Added function to load register service provider (in `Application::class`).

### Changed
- Application container have default config by default.

## [0.4.0] - 2022-04-24
### Added
- Added `ScheduleTime->interpolate()` to execute closure. Action after schadule execute.
- Added DI container `Container::class`.
- Added core framework `Appcliaction::class`, witch load DI and application configuration.
- Added abstract `Karnel::class` and `ServiceProvider::class`.
- Added global function Application.

## [0.3.0] - 2022-04-06
### Added
- Added Collection->immutable class.
- Added Fetch->get() return as collection.
- Added uploadfile class.
- Added method close() to closed middleware.
- Add `Router::prefix()->group()` group router base on prefix.
- Add `Router::name()->group()` group router base on name.
- Add `Router::midleware()->group()` group router base on midleware.
- Add `Router::controller()->group()` group router base on controller.
- Add `Router::group(array, closure)` costume group.
- Add `Router::current()` get current route.
- Router group can be combine with other router group.

### Changed
- Change `MyCrud` not implement `CrudInterface`

## [0.2.1] - 2021-12-11
### Added
- Add `collection::class` method `split`, `chunk`, `only`, `except` and `flatten`

## [0.2.0] - 2021-12-09
### Added
- Add Parameter pool (`ConstPool`, `PropertyPool` and `MethodPool`) in method `Generate->consts(); Generate->propertys(); Generate->Methods()`
- Add feature command name (get option directly from object name) ```Command:class```
- Add `MyQuery->whereExits()` and `MyQuery->whereNotExits()`
- Add `MyModel->join()` join method
- Add MyCRUD `RESISTANT` to prevent changes from some table column
- Add MyCRUD `hasOne` and `hasMany`
- Add MyCRUD magic method `__get` and `__set`
- `MyQuery` query quality, remove extra white space

### Changed
- MyQuery use `ConditionTrait` instead of `ConditionInterface`
- MyQuery join method using `AbstractJoin` parameter instead of `Join`
- Refactory `MyCRUD`
- `MyCRUD` use `MyQuery` for database connection
- `MyCRUD` change property `ID` to `PRIMERY_KEY` and `IDENTIFER`

## [0.1.3] - 2021-11-07
### Added
- Add feature to ```generate::class``` class, property, const and method as string
- Add feature to collact array ```Collection::class``` and ```CollectionImmutable::class```

### Removed
- Remove ```Router::view```

## [0.1.2] - 2021-09-15
### Added
- Add class to render view ```View::class```
### Fixed 
-  ```Router::view``` cant render using child controller class

## [0.1.1] - 2021-09-14
### Added
- Add ```Request::class``` to handle http request
- Add ```Response::class``` to handle http respone
- Add ```RequestFactory::class``` to create request from global parameter
- ```Router::class``` suport router name, route view (override), support HEAD request method
- Route suport middleware ```Router::middleware```

### Changed
- Rename class ```RouterFactory::class``` to ```RouteFactory::class```
- Rename class ```RouterProvider::class``` to ```RouteProvider::class```

## [0.1.0] - 2021-07-12
### Added
- Project init

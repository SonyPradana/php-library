# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.6.0] - 2022-05-15
### Added
- Add `RouteDispatcher::class` dispatch request route.

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

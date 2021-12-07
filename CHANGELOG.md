# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- Add Parameter pool (`ConstPool`, `PropertyPool` and `MethodPool`) in method `Generate->consts(); Generate->propertys(); Generate->Methods()`
- Add feature command name (get option directly from object name) ```Command:class```

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

# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.3.0] - 2023-05-15

### Added
- Added support for Blade templates via BladeThemeRenderer, BladeTheme, and BladeThemeLoader
- Added Vite integration for all template engines (Plates, Twig, Latte, Blade)
- Added engine-specific configuration for templates paths, cookie names, and query parameters

## [1.1.0] - 2023-05-10

### Added
- Added support for Plates templates
- Added support for configurable template paths

## [1.0.7] - 2023-05-01

### Fixed
- Fixed issue with Twig functions not being properly registered
- Added twig/twig as a required dependency instead of a suggested one
- Improved initialization of Twig environment to ensure extensions are properly registered
## [1.0.6] - 2025-04-30

### Changed
- Update examples and documentation to use modular theme structure
- Update templates paths to use module-based organization (home/index.twig, layout/default.twig)

## [1.0.5] - 2025-04-30

### Changed
- Update documentation, examples and tests to reflect recent changes
- Fix tests for new theme structure

## [1.0.4] - 2023-04-30

### Changed
- Added support for theme configuration in settings
- Simplified theme directory structure by removing /templates subdirectory

## [1.0.3] - 2023-04-29

### Changed
- Use theme path directly instead of templates subdirectory

## [1.0.2] - 2023-04-28

### Changed
- Simplify theme structure by removing /templates subdirectory

## [1.0.1] - 2023-04-27

### Changed
- Update dependency to responsive-sk/slim4-root

## [1.0.0] - 2023-04-26

### Added
- Initial release
- Support for Twig and Latte template engines
- Theme inheritance
- Theme switching via query parameter or cookie
- PSR-7 compatible
- Singleton attribute for dependency injection

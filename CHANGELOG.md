# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

---

## [Unreleased] — develop branch

### Added
- **OPcache-compatible rendering** — component view files are now loaded with
  `include` instead of `eval(file_get_contents(...))`, allowing PHP's OPcache to
  cache compiled bytecode and skip re-parsing on every request.
- **Request-scoped view-path cache** — `ComponentRenderer` stores resolved
  component file paths in a static array so the filesystem is only probed once
  per unique component name per request.
- **Renderer-level output caching for view-only components** — set
  `$viewCacheTtl` (seconds) in your `app/Config/Components.php` to cache the
  rendered HTML of every component that has no companion class. Uses CI4's
  `cache()` service so the backend (file, Redis, Memcached …) is controlled by
  your existing `app/Config/Cache.php`.
- **Renderer-level output caching for class-based components** — set
  `public ?int $cacheTtl` on a `Component` subclass to cache that component's
  rendered HTML for the given number of seconds.
- **`Component::cacheKey()` hook** — override this method to add extra data
  (e.g. `session('user_id')`) to the renderer's cache key so that different
  contexts produce separate cache entries.
- Cache key is derived from component name + view file path + file mtime
  (auto-invalidates on deploy) + serialised attributes + `cacheKey()` result.
- New README section **Component Output Caching** with examples and a table of
  what is/isn't captured automatically.
- 8 new tests covering the new caching behaviour.

---

## [0.4.0] — 2025-10-05

### Changed
- Rector configuration made functional; codebase passes Rector analysis.

### Fixed
- GitHub Actions `upload-artifact` step renamed and upgraded to action version 4.

---

## [0.3.0] — 2025-10-03

### Changed
- `FamousQuotesComponent` refactored: improved error handling and internal code
  structure.

---

## [0.2.0] — 2025-01-19

### Fixed
- Components nested inside other components (components-within-components) now
  render correctly. Improvement adapted from a community contribution to
  Bonfire2 ([reference](https://github.com/lonnieezell/Bonfire2/issues/464#issuecomment-2253990338)).

### Added
- `rector.php` and `phpstan.neon.dist` configuration files.
- CI4 coding-standard style rules enforced; `php-cs-fixer` workflow added.

---

## [0.1.0] — 2025-01-12

### Added
- Full PHPUnit test suite with code coverage reporting.
- GitHub Actions workflow publishing coverage via Codecov.
- Build status and coverage badges in README.
- Debug log entry when a component's companion class cannot be loaded.

### Changed
- Example components moved from `src/Views/Components/` to `src/Components/`.

---

## [0.0.1] — 2025-01-05 to 2025-01-10

### Added
- Initial release of the project.
- `ComponentRenderer` — parses `x-*` self-closing and paired tags in rendered
  HTML and replaces them with the corresponding view output.
- `Component` — base class for class-backed (controlled) components, providing
  `withView()`, `withData()`, and `render()`.
- `ComponentDecorator` implementing CI4's `ViewDecoratorInterface` so the
  renderer integrates automatically via `app/Config/View.php` `$decorators`.
- `Components` config class with `$componentsLookupPaths`; overridable by
  placing a `Config\Components` file in `app/Config/`.
- Example components: `avatar`, `bootstrap-icon`, `button-green`, `button-red`,
  `famous-quotes` (with `FamousQuotesComponent` class and ZenQuotes API
  integration).
- Composer installation support; README with full usage documentation.

### Fixed
- Component renderer now correctly locates the controlling class of a component.
- Self-closing controlled components correctly accept and pass attributes.

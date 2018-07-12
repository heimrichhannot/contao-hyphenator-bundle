# Changelog
All notable changes to this project will be documented in this file.

## [1.2.4] - 2018-07-12

### Fixed
- `Couldn't fetch DOMElement. Node no longer exists` error while using `hyphenateHtml` in dev mode

## [1.2.3] - 2018-07-12

### Fixed
- `Couldn't fetch DOMElement. Node no longer exists` error while using `hyphenateHtml`, now always use `hyphenateHtml`

## [1.2.2] - 2018-07-12

### Fixed
- `Couldn't fetch DOMText. Node no longer exists` error while using `hyphenateHtml`, use `$node->replaceWith()` instead of `$node->html()`

## [1.2.1] - 2018-07-12

### Fixed
- disable error reporting when potential using HTML5 tags while using `hyphenateHtml`

## [1.2.0] - 2018-07-12

### Changed
- now also hyphenate anchor text `<a>` by default (excluding HTML tags and attributes)
- hyphenation within following selectors: `h1:not(:empty), h2:not(:empty), h3:not(:empty), h4:not(:empty), h5:not(:empty), h6:not(:empty), p:not(:empty), a:not(:empty)` (before: `h1, h1> a, h2, h2 > a, h3, h3 > a, h4, h4 > a, h5, h5 > a, h6, h6 > a, p`)

### Added
- performance improvement of previous already hyphenated elements by adding simple caching technique  

## [1.1.0] - 2018-06-12

### Changed
- replaced `org_heigl/hyphenator` with `vanderlee/syllable` 

## [1.0.1] - 2018-06-12

### Fixed
- addressed wrong service for `modifyFrontendPage` hook

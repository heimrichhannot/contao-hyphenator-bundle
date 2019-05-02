# Changelog
All notable changes to this project will be documented in this file.

## [1.8.0] - 2019-05-02

### Changed
- prevent `white-space` wrap in line break exception replacement by wrapping replacement in `<span class="text-nowrap"></span>`, do not wrap (no line break) soft-hyphen in line break exceptions 

## [1.7.5] - 2019-04-25

### Fixed
- missing documentation 

## [1.7.4] - 2019-04-25

### Fixed
- travis-ci build

## [1.7.3] - 2019-04-25

### Fixed
- DOMDocument::saveHTML and DOMDocument::saveHTMLFile methods does not product valid HTML when using void elements introduced in HTML5 (`<source>`, `<embed>`…),results in w3c validator errors 
- hyphenation did not properly work while `tl_page.hyphenation` option is `active`
- unit tests and coverage 

### Changed
- first handle line break exceptions, afterwards hyphenate

## [1.7.2] - 2019-03-27

### Fixed
- handle html entities in lineBreakExceptions replacement properly when using regular expression in order to handle special characters like € sign

## [1.7.1] - 2019-03-27

### Fixed
- do not replace attributes of html tags (`tl_page.lineBreakExceptions`)
- Ungreedy, case insensitive and single line flag added to line break exception replacement

## [1.7.0] - 2019-03-27

### Changed
- `tl_page.customLineBreakExceptions` and `tl_page.lineBreakExceptions` now properly supports regular expressions

## [1.6.0] - 2019-03-26

### Added
- `tl_page` hyphenation disable/enable/nesting handling in backend mask
- `tl_page.customLineBreakExceptions` and `tl_page.lineBreakExceptions` added in order to provide support for line break exception to keep words like company names together and prevent line break

## [1.5.1] - 2019-03-26

### Fixed
- update cs-fixer config, and fix skipPage strict comparision check

## [1.5.0] - 2019-03-19

### Added
- `.hyphen-none` css class selector, in order to skip hyphenation on elements with that css class

## [1.4.2] - 2019-03-18

### Fixed
- drop skipPage strict comparision check

## [1.4.1] - 2019-03-15

### Fixed
- hyphenation did not replace same text again, when cache is enabled

## [1.4.0] - 2019-03-14

### Added
- `dd` and `dt` elements to list of tags that should be recognized for hyphenation

## [1.3.0] - 2019-01-23

### Fixed
- `vanderlee/syllable` compatibility to version 1.5 (namespace change…)

## [1.2.7] - 2018-12-04

### Fixed
- `cz` language to `cs` tex file mapping

## [1.2.6] - 2018-12-04

### Fixed
- page language and naming of .tex files do not match, provide `$GLOBALS['TL_CONFIG']['hyphenator_locale_language_mapping']` in order to handle with

## [1.2.5] - 2018-10-22

### Fixed
- prevent unescape unicode html entities (email obfuscation)

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

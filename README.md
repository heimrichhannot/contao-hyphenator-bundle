# Contao Hyphenator Bundle

![](https://img.shields.io/packagist/v/heimrichhannot/contao-hyphenator-bundle.svg)
![](https://img.shields.io/packagist/dt/heimrichhannot/contao-hyphenator-bundle.svg)
[![](https://img.shields.io/travis/heimrichhannot/contao-hyphenator-bundle/master.svg)](https://travis-ci.org/heimrichhannot/contao-hyphenator-bundle/)
[![](https://img.shields.io/coveralls/heimrichhannot/contao-hyphenator-bundle/master.svg)](https://coveralls.io/github/heimrichhannot/contao-hyphenator-bundle)

A contao bundle that grants server-side hyphenation (thanks to [vanderlee/phpSyllable](https://github.com/vanderlee/phpSyllable)). 
It does support headlines and paragraphs by default. 

This module also handles line break exceptions, in order to keep words like company names together and prevent line break (see `tl_page` backend entity).  

## Options

To extend the functionality, all options can be adjusted within your localconfig.

Option | Type | Default |  Description
------ | ---- | ------- |  -----------
hyphenator_tags | string | 'h1:not(:empty):not(.hyphen-none), h2:not(:empty):not(.hyphen-none), h3:not(:empty):not(.hyphen-none), h4:not(:empty):not(.hyphen-none), h5:not(:empty):not(.hyphen-none), h6:not(:empty):not(.hyphen-none), p:not(:empty):not(.hyphen-none), a:not(:empty):not(.hyphen-none), dt:not(:empty):not(.hyphen-none), dd:not(:empty):not(.hyphen-none)' | What type of selectors the hyphenator should look at. 
hyphenator_wordMin | int | 10 | Words under the given length will not be hyphenated altogether.
hyphenator_hyphen | string | &shy; | This character shall be used as Hyphen-Character. 
hyphenator_skipPages | array | empty | Array of Contao Page Ids, the Hyphenator should skip from hyphenation.
hyphenator_enableCache | bool | true | Enable simple caching and do not hyphenate equal elements twice.  
hyphenator_locale_language_mapping | array | ['en' => 'en-us', 'cz' => 'cs'] | Map locale to hyphenator tex separation pattern dictionary

## Skip hyphenation

If you want to skip several tags from hyphenation simply add `hyphen-none` as css-class to the appropriate element or use the `tl_page.hyphenation` field. 


## Line break exceptions

Hyphenator comes with line break exception handling. 
Simply add `lineBreakExceptions` on `tl_page` and prevent line break for connected word groups like:

- Company Names (search: `Heimrich & Hannot(?:\sGmbH)|Heimrich & Hannot(?:s)?`, will be replaced to: `<span class="text-nowrap">Heimrich&nbsp;&amp;&nbsp;Hannot&nbsp;GmbH</span>`)
- Prices and other units (search: `(\d|€)(\s)(\w)`, replace: `$1[nbsp]$3`, Example: `160.000 m²` -> `160.00<span class="text-nowrap">0&nbsp;m</span>²`, `167 Mio. €` -> `16<span class="text-nowrap">7&nbsp;M</span>io.&nbsp;€`)

As you can see, if you provide an replace pattern, than an regular expression will handle the replacement, otherwise if only an search pattern is provided, spaces will be protected with `&nbsp;`.

## Requirements

* [vanderlee/phpSyllable](https://github.com/vanderlee/phpSyllable)
* [wa72/htmlpagedom](https://github.com/wasinger/htmlpagedom)


# Contao Hyphenator Bundle

![](https://img.shields.io/packagist/v/heimrichhannot/contao-hyphenator-bundle.svg)
![](https://img.shields.io/packagist/dt/heimrichhannot/contao-hyphenator-bundle.svg)
[![](https://img.shields.io/travis/heimrichhannot/contao-hyphenator-bundle/master.svg)](https://travis-ci.org/heimrichhannot/contao-hyphenator-bundle/)
[![](https://img.shields.io/coveralls/heimrichhannot/contao-hyphenator-bundle/master.svg)](https://coveralls.io/github/heimrichhannot/contao-hyphenator-bundle)

A contao bundle that grants server-side hyphenation (thanks to [vanderlee/phpSyllable](https://github.com/vanderlee/phpSyllable)). 
It does support headlines and paragraphs by default. 

## Options

To extend the functionality, all options can be adjusted within your localconfig.

Option | Type | Default |  Description
------ | ---- | ------- |  -----------
hyphenator_tags | string | h1, h1> a, h2, h2 > a, h3, h3 > a, h4, h4 > a, h5, h5 > a, h6, h6 > a, p | What type of tags the hyphenator should look at. 
hyphenator_wordMin | int | 10 | Words under the given length will not be hyphenated altogether.
hyphenator_hyphen | string | &shy; | This character shall be used as Hyphen-Character. 
hyphenator_skipPages | array | empty | Array of Contao Page Ids, the Hyphenator should skip from hyphenation. 


## Requirements

* [vanderlee/phpSyllable](https://github.com/vanderlee/phpSyllable)
* [wa72/htmlpagedom](https://github.com/wasinger/htmlpagedom)

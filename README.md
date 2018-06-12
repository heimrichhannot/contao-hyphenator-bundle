# Contao Hyphenator Bundle

A contao bundle that grants server-side hyphenation (thanks to [org_heigl/hyphenator](https://github.com/heiglandreas/Org_Heigl_Hyphenator)). 
It does support headlines and paragraphs by default. 

## Options

To extend the functionality, all options can be adjusted within your localconfig.

Option | Type | Default |  Description
------ | ---- | ------- |  -----------
hyphenator_tags | string | h1,h2,h3,h4,h5,h6,p | What type of tags the hyphenator should look at. 
hyphenator_wordMin | int | 10 | Words under the given length will not be hyphenated altogether. It makes sense to set option to a higher value than the sum of rightMin and leftMin.
hyphenator_leftMin | int | 5 | How many characters have to be left unhyphenated to the left of the word. 
hyphenator_rightMin | int | 5 | How many characters have to be left unhyphenated to the right of the word.
hyphenator_quality | int | 9 | How good shal the hyphenation be. The higher the number the better. THis can be any integer from 0 (no Hyphenation at all) through 9 (best hyphernation).
hyphenator_hyphen | string | &shy; | This character shall be used as Hyphen-Character. 
hyphenator_filter | string | Simple | A comma-separated list of filters to use for postprocessing the hyphenated text The filters have to extend the Org\Heigl\Filter\Filter-class. The filters can be given by using the Part of the Classname before the “Filter”. So for the SimpleFilter it would suffice to use “Simple” as name of the filter. 
hyphenator_tokenizers | array | array('Whitespace', 'Punctuation') | A list of tokenizers to use for splitting the text to be hyphenated into hypheable chunks. The tokenizers have to implement the Org\Heigl\Tokenizer\Tokenizer-interface. The tokenizers can be given by using the Part of the Classname before the “Tokenizer”. So for the WhitespaceTokeinzer it would suffice to use “Whitespace” as name of the tokenizer. 
hyphenator_skipPages | array | empty | Array of Contao Page Ids, the Hyphenator should skip from hyphenation. 


## Requirements

* [org_heigl/hyphenator](https://github.com/heiglandreas/Org_Heigl_Hyphenator)
* [wa72/htmlpagedom](https://github.com/wasinger/htmlpagedom)

<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HyphenatorBundle\Hyphenator;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\StringUtil;
use Contao\System;
use Vanderlee\Syllable\Syllable;
use Wa72\HtmlPageDom\HtmlPageCrawler;

class FrontendHyphenator
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * Request constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    public function hyphenate($strBuffer)
    {
        global $objPage;

        $arrSkipPages = Config::get('hyphenator_skipPages');

        if (null === $objPage || \is_array($arrSkipPages) && \in_array($objPage->id, $arrSkipPages)) {
            return $strBuffer;
        }

        Syllable::setCacheDir(System::getContainer()->getParameter('kernel.cache_dir'));

        $language = $objPage->language;

        if (isset($GLOBALS['TL_CONFIG']['hyphenator_locale_language_mapping'][$language])) {
            $language = $GLOBALS['TL_CONFIG']['hyphenator_locale_language_mapping'][$language];
        }

        $h = new Syllable($language);
        $h->setMinWordLength(Config::get('hyphenator_wordMin'));
        $h->setHyphen(Config::get('hyphenator_hyphen'));

        // mask esi tags, otherwise dom crawler will remove them
        $strBuffer = preg_replace_callback(
            '#<esi:((?!\/>).*)\s?\/>#sU',
            function ($matches) {
                return '####esi:open####'.str_replace('"', '#~~~#', StringUtil::specialchars($matches[1])).'####esi:close####';
            },
            $strBuffer
        );

        // prevent unescape unicode html entities (email obfuscation)
        $strBuffer = preg_replace('/&(#+[x0-9a-fA-F]+);/', '&_$1;', $strBuffer);

        $doc = HtmlPageCrawler::create($strBuffer);
        $isHtmlDocument = $doc->isHtmlDocument();

        if (false === $isHtmlDocument) {
            $doc = HtmlPageCrawler::create(sprintf('<div id="crawler-root">%s</div>', $strBuffer));
        }

        $cacheEnabled = (bool) Config::get('hyphenator_enableCache');
        $cache = [];

        $doc->filter(Config::get('hyphenator_tags'))->each(
            function (HtmlPageCrawler $node, $i) use ($h, &$cache, $cacheEnabled) {
                $clone = $node->makeClone(); // make a clone to prevent `Couldn't fetch DOMElement. Node no longer exists`
                $html = $clone->html(); // restore nested inserttags that were replaced with %7B or %7D
                $cacheKey = $html;

                if (empty($html)) {
                    return $node;
                }

                if (true === $cacheEnabled && isset($cache[$cacheKey])) {
                    $clone->html(StringUtil::decodeEntities($cache[$cacheKey]));
                    $node->replaceWith($clone->saveHTML());

                    return $node;
                }

                $html = str_replace('&shy;', '', $html); // remove manual &shy; html entities before

                // if html contains nested tags, use the hyphenateHtml that excludes HTML tags and attributes
                libxml_use_internal_errors(true); // disable error reporting when potential using HTML5 tags
                $html = $h->hyphenateHtml($html);
                libxml_clear_errors();

                if (false === preg_match('#<body>(<p>)?(?<content>.+?)(<\/p>)?<\/body>#is', $html, $matches) || !isset($matches['content'])) {
                    return $node;
                }

                $html = $matches['content'];
                $clone->html(StringUtil::decodeEntities($html));
                $node->replaceWith($clone->saveHTML());

                $cache[$cacheKey] = $html;

                return $node;
            }
        );

        $strBuffer = false === $isHtmlDocument ? $doc->filter('#crawler-root')->getInnerHtml() : $doc->saveHTML();

        // prevent unescape unicode html entities (email obfuscation)
        $strBuffer = preg_replace('/&amp;_(#+[x0-9a-fA-F]+);/', '&$1;', $strBuffer);

        $strBuffer = preg_replace_callback(
            '/####esi:open####(.*)####esi:close####/',
            function ($matches) {
                return '<esi:'.str_replace('#~~~#', '"', StringUtil::decodeEntities($matches[1])).'/>';
            },
            $strBuffer
        );

        return $strBuffer;
    }
}

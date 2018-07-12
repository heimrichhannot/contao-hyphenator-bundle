<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HyphenatorBundle\Hyphenator;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\StringUtil;
use Contao\System;
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

        if (null === $objPage || is_array($arrSkipPages) && in_array($objPage->id, $arrSkipPages, true)) {
            return $strBuffer;
        }

        \Syllable::setCacheDir(System::getContainer()->getParameter('kernel.cache_dir'));

        $h = new \Syllable($objPage->language);
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

        $doc = HtmlPageCrawler::create($strBuffer);

        $cacheEnabled = (bool) Config::get('hyphenator_enableCache');
        $cache = [];

        $doc->filter(Config::get('hyphenator_tags'))->each(
            function ($node, $i) use ($h, &$cache, $cacheEnabled) {
                /** @var $node HtmlPageCrawler */
                $html = $node->html();
                $cacheKey = $html;

                if (empty($html)) {
                    return $node;
                }

                if (true === $cacheEnabled && isset($cache[$cacheKey])) {
                    return $cache[$cacheKey];
                }

                $html = str_replace('&shy;', '', $html); // remove manual &shy; html entities before

                // if html contains nested tags, use the hyphenateHtml that excludes HTML tags and attributes
                if ($html != strip_tags($html)) {
                    libxml_use_internal_errors(true); // disable error reporting when potential using HTML5 tags
                    $html = $h->hyphenateHtml($html);
                    libxml_clear_errors();

                    if (false === preg_match('#<body>(<p>)?(?<content>.+?)(<\/p>)?<\/body>#is', $html, $matches) || !isset($matches['content'])) {
                        return $node;
                    }

                    $html = $matches['content'];
                } else {
                    $html = $h->hyphenateText($html);
                }

                $node->html(StringUtil::decodeEntities($html));

                $cache[$cacheKey] = $html;

                return $node;
            }
        );

        $strBuffer = $doc->saveHTML();

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

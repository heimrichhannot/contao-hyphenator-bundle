<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HyphenatorBundle\Hyphenator;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
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

        $o = new \Org\Heigl\Hyphenator\Options();

        $o->setHyphen(Config::get('hyphenator_hyphen'))
            ->setDefaultLocale($this->getLocaleFromLanguage($objPage->language))
            ->setRightMin(Config::get('hyphenator_rightMin'))
            ->setLeftMin(Config::get('hyphenator_leftMin'))
            ->setWordMin(Config::get('hyphenator_wordMin'))
            ->setFilters(Config::get('hyphenator_filter'))
            ->setQuality(Config::get('hyphenator_quality'))
            ->setTokenizers(Config::get('hyphenator_tokenizers'));

        $h = new \Org\Heigl\Hyphenator\Hyphenator();
        $h->setOptions($o);

        // mask esi tags, otherwise dom crawler will remove them
        $strBuffer = preg_replace_callback('#<esi:((?!\/>).*)\s?\/>#sU', function ($matches) {
            return '####esi:open####'.str_replace('"', '#~~~#', \Contao\StringUtil::specialchars($matches[1])).'####esi:close####';
        }, $strBuffer);

        $doc = HtmlPageCrawler::create($strBuffer);

        $doc->filter(Config::get('hyphenator_tags'))->each(function ($node, $i) use ($h) {
            /** @var $node HtmlPageCrawler */
            $text = $node->html();

            // ignore html tags, otherwise &shy; will be added to links for example
            if ($text != strip_tags($text)) {
                return $node;
            }

            $text = str_replace('&shy;', '', $text); // remove manual &shy; html entities before

            $text = $h->hyphenate($text);

            if (is_array($text)) {
                $text = current($text);
            }

            $node->html($text);

            return $node;
        });

        $strBuffer = $doc->saveHTML();

        $strBuffer = preg_replace_callback('/####esi:open####(.*)####esi:close####/', function ($matches) {
            return '<esi:'.str_replace('#~~~#', '"', \Contao\StringUtil::decodeEntities($matches[1])).'/>';
        }, $strBuffer);

        return $strBuffer;
    }

    private function getLocaleFromLanguage($strLanguage)
    {
        $locales = array_keys(Controller::getLanguages());
        $locale = $strLanguage;

        foreach ($locales as $l) {
            $regex = '/'.$strLanguage.'\_[A-Z]{2}$/';

            if (preg_match($regex, $l)) {
                $locale = $l;
                break;
            }
        }

        return $locale;
    }
}

<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HyphenatorBundle\Hyphenator;

use Contao\Config;
use Contao\PageModel;
use Contao\StringUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vanderlee\Syllable\Syllable;
use Wa72\HtmlPageDom\HtmlPageCrawler;

class FrontendHyphenator
{
    /**
     * Elements that have no closing tag (see: https://bugs.php.net/bug.php?id=73175).
     *
     * @var array
     */
    protected $voidElements = ['area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Request constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function hyphenate($strBuffer)
    {
        /* @var PageModel $objPage */
        global $objPage;

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

        Syllable::setCacheDir($this->container->getParameter('kernel.cache_dir'));

        $languageMapping = Config::get('hyphenator_locale_language_mapping');

        $language = $languageMapping[$objPage->language] ?? $objPage->language;

        $h = new Syllable($language);
        $h->setMinWordLength(Config::get('hyphenator_wordMin'));
        $h->setHyphen(Config::get('hyphenator_hyphen'));

        $doc = HtmlPageCrawler::create($strBuffer);
        $isHtmlDocument = $doc->isHtmlDocument();

        if (false === $isHtmlDocument) {
            $doc = HtmlPageCrawler::create(sprintf('<div id="crawler-root">%s</div>', $strBuffer));
        }

        $cacheEnabled = (bool) Config::get('hyphenator_enableCache');
        $cache = [];

        $doc->filter(Config::get('hyphenator_tags'))->each(
            function (HtmlPageCrawler $node, $i) use ($h, &$cache, $cacheEnabled, $objPage) {
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

                $html = $this->handleLineBreakExceptions($html, $objPage);

                if (!$this->isHyphenationDisabled($objPage, Config::get('hyphenator_skipPages'))) {
                    // if html contains nested tags, use the hyphenateHtml that excludes HTML tags and attributes
                    libxml_use_internal_errors(true); // disable error reporting when potential using HTML5 tags
                    $html = $h->hyphenateHtml($html);
                    libxml_clear_errors();
                }

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

        //  DOMDocument::saveHTHMl currently renders this tags as non-void elements (see: https://bugs.php.net/bug.php?id=73175)
        foreach ($this->voidElements as $voidElement) {
            $strBuffer = preg_replace('/<\/'.$voidElement.'>/i', '', $strBuffer);
        }

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

    /**
     * Determine if hyphenation is enabled within current page scope.
     *
     * @param PageModel $page
     * @param array     $skipPageIds
     *
     * @return bool
     */
    protected function isHyphenationDisabled($page = null, array $skipPageIds = []): bool
    {
        if (null === $page) {
            return true;
        }

        if (\is_array($skipPageIds) && \in_array($page->id, $skipPageIds)) {
            return true;
        }

        if ('inactive' === $page->hyphenation) {
            return true;
        }

        if ('active' === $page->hyphenation) {
            return false;
        }

        if ($page->pid && null !== ($parent = $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_page', $page->pid))) {
            return $this->isHyphenationDisabled($parent);
        }

        return false;
    }

    /**
     * Handle line break exceptions.
     *
     * @param string $buffer
     * @param null   $page
     *
     * @return string
     */
    protected function handleLineBreakExceptions(string $buffer, $page = null): string
    {
        if (true === (bool) $page->customLineBreakExceptions) {
            $exceptions = StringUtil::deserialize($page->lineBreakExceptions, true);

            foreach ($exceptions as $exception) {
                if (!isset($exception['search']) || empty($exception['search'])) {
                    continue;
                }

                // custom user regex whitespace replacement
                if (isset($exception['replace']) && !empty($exception['replace'])) {
                    $search = '#'.StringUtil::decodeEntities($exception['search']);
                    $replace = '<span class="text-nowrap">'.StringUtil::restoreBasicEntities($exception['replace']).'</span>';
                } // default: whitespace replacement
                else {
                    $search = '#('.StringUtil::decodeEntities($exception['search']).')';
                    $replace = '<span class="text-nowrap">'.implode('&nbsp;', explode(' ', $exception['search'])).'</span>';
                }

                $search .= '(?![^<]*>)'; // ignore html tags
                $search .= '#siU'; // single line and ungreedy
                $buffer = preg_replace($search, $replace, $buffer);
            }
        }

        // always handle root page exceptions
        if ($page->rootId && $page->id !== $page->rootId && (null !== ($root = $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_page', $page->rootId)))) {
            $buffer = $this->handleLineBreakExceptions($buffer, $root);
        }

        return $buffer;
    }
}

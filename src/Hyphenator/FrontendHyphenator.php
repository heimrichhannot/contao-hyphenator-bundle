<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HyphenatorBundle\Hyphenator;

use Contao\Config;
use Contao\PageModel;
use Contao\StringUtil;
use HeimrichHannot\HyphenatorBundle\Source\File;
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
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function hyphenate($strBuffer)
    {
        /* @var PageModel $objPage */
        global $objPage;

        if ($this->isHyphenationDisabled($objPage, Config::get('hyphenator_skipPages'))) {
            return $strBuffer;
        }

        $esiTagCache = [];
        $esiTagCacheIndex = 0;

        // mask esi tags, otherwise no body element is found
        $strBuffer = preg_replace_callback(
            '#<esi:((?!\/>).*)\s?\/>#sU',
            function ($matches) use (&$esiTagCache, &$esiTagCacheIndex) {
                $esiTagCache[$esiTagCacheIndex] = $matches[0];
                ++$esiTagCacheIndex;

                return '####esi:open####'.($esiTagCacheIndex - 1).'####esi:close####';
            },
            $strBuffer
        );

        // prevent unescape unicode html entities (email obfuscation)
        $strBuffer = preg_replace('/&(#+[x0-9a-fA-F]+);/', '&_$1;', $strBuffer);

        Syllable::setCacheDir($this->container->getParameter('kernel.cache_dir'));

        $languageMapping = Config::get('hyphenator_locale_language_mapping');

        $language = $languageMapping[$objPage->language] ?? $objPage->language;

        $h = new Syllable($language);

        $source = new File($language, __DIR__.'/../../../../vanderlee/syllable/languages', [
            $language => [$GLOBALS['TL_CONFIG']['hyphenator_hyphenedLeftMin'], $GLOBALS['TL_CONFIG']['hyphenator_hyphenedRightMin']],
        ]);

        $h->setSource($source);
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
                    $clone->setInnerHtml(StringUtil::decodeEntities($cache[$cacheKey]));
                    $node->replaceWith($clone->saveHTML());

                    return $node;
                }

                $html = str_replace('&shy;', '', $html); // remove manual &shy; html entities before

                $html = $this->handleLineBreakExceptions($html, $objPage);

                // mask tags configured to be skipped
                $skipTagCache = [];
                $skipTagCacheIndex = 0;

                foreach ($this->container->getParameter('huh_hyphenator')['skip_tags'] as $tag) {
                    if (\in_array($tag, $this->voidElements)) {
                        $html = preg_replace_callback(
                            '#<\s*?'.$tag.'\b[^>]*>#s',
                            function ($matches) use (&$skipTagCache, $skipTagCacheIndex) {
                                $skipTagCache[$skipTagCacheIndex] = $matches[0];
                                ++$skipTagCacheIndex;

                                return '####skip:open####'.($skipTagCacheIndex - 1).'####skip:close####';
                            }, $html
                        );
                    } else {
                        $html = preg_replace_callback(
                            '#<\s*?'.$tag.'\b[^>]*>(.*?)</'.$tag.'\b[^>]*>#s',
                            function ($matches) use (&$skipTagCache, &$skipTagCacheIndex) {
                                $skipTagCache[$skipTagCacheIndex] = $matches[0];
                                ++$skipTagCacheIndex;

                                return '####skip:open####'.($skipTagCacheIndex - 1).'####skip:close####';
                            }, $html
                        );
                    }
                }

                // if html contains nested tags, use the hyphenateHtml that excludes HTML tags and attributes
                libxml_use_internal_errors(true); // disable error reporting when potential using HTML5 tags
                $html = $h->hyphenateHtml(utf8_decode($html));
                libxml_clear_errors();

                // replace skipped tags
                $html = preg_replace_callback(
                    '/####skip:open####(.*)####skip:close####/',
                    function ($matches) use ($skipTagCache) {
                        return $skipTagCache[$matches[1]];
                    },
                    $html
                );

                if (false === preg_match('#<body>(<p>)?(?<content>.+?)(<\/p>)?<\/body>#is', $html, $matches) || !isset($matches['content'])) {
                    return $node;
                }

                $html = $matches['content'];
                $clone->setInnerHtml(StringUtil::decodeEntities($html));
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
            function ($matches) use ($esiTagCache) {
                return $esiTagCache[$matches[1]];
            },
            $strBuffer
        );

        return $strBuffer;
    }

    /**
     * Determine if hyphenation is enabled within current page scope.
     *
     * @param PageModel $page
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
     * @param null $page
     */
    protected function handleLineBreakExceptions(string $buffer, $page = null): string
    {
        if (true === (bool) $page->customLineBreakExceptions) {
            $exceptions = StringUtil::deserialize($page->lineBreakExceptions, true);

            foreach ($exceptions as $exception) {
                if (!isset($exception['search']) || empty($exception['search'])) {
                    continue;
                }

                $search = '/';

                // custom user regex whitespace replacement
                if (isset($exception['replace']) && !empty($exception['replace'])) {
                    $search .= StringUtil::decodeEntities($exception['search']);
                    $replace = '<span class="text-nowrap">'.StringUtil::restoreBasicEntities($exception['replace']).'</span>';
                    $search .= '(?![^<]*>)'; // ignore html tags
                    $search .= '/siU'; // single line and ungreedy
                    $buffer = preg_replace($search, $replace, $buffer);
                } // default: whitespace replacement
                else {
                    $search .= '('.StringUtil::decodeEntities($exception['search']).')';
                    $search .= '(?![^<]*>)'; // ignore html tags
                    $search .= '/siU'; // single line and ungreedy
                    $buffer = preg_replace_callback($search, function ($matches) {
                        return '<span class="text-nowrap">'.implode('&nbsp;', explode(' ', $matches[0])).'</span>';
                    }, $buffer);
                }
            }
        }

        // always handle root page exceptions
        if ($page->rootId && $page->id !== $page->rootId && (null !== ($root = $this->container->get('huh.utils.model')->findModelInstanceByPk('tl_page', $page->rootId)))) {
            $buffer = $this->handleLineBreakExceptions($buffer, $root);
        }

        return $buffer;
    }
}

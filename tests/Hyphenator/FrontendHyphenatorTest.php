<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HyphenatorBundle\Tests\Hyphenator;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\HyphenatorBundle\Hyphenator\FrontendHyphenator;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FrontendHyphenatorTest extends ContaoTestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $this->container = $this->mockContainer();
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $listener = new FrontendHyphenator($this->container);

        $this->assertInstanceOf(FrontendHyphenator::class, $listener);
    }

    /**
     * Test hyphenate without page context.
     */
    public function testHyphenateWithoutPageContext()
    {
        $listener = new FrontendHyphenator($this->container);

        $this->assertSame('test', $listener->hyphenate('test'));
    }

    /**
     * Test hyphenate without page context.
     *
     * @dataProvider hyphenationProvider
     */
    public function testHyphenate($buffer, array $pageData, array $config, $expected)
    {
        $this->container->set('contao.resource_finder', new ResourceFinder([$this->getFixturesDir().'/vendor/contao/core-bundle/src/Resources/contao']));
        $this->container->setParameter('kernel.cache_dir', $this->getFixturesDir().'/var/cache');

        $modelUtil = $this->createMock(ModelUtil::class);

        $this->container->set('huh.utils.model', $modelUtil);

        $listener = new FrontendHyphenator($this->container);

        global $objPage;

        $objPage = (object) $pageData;

        foreach ($config as $key => $value) {
            $GLOBALS['TL_CONFIG'][$key] = $value;
        }

        $this->assertSame($expected, $listener->hyphenate($buffer));
    }

    public function hyphenationProvider()
    {
        $GLOBALS['TL_CONFIG']['hyphenator_tags'] = 'h1, h1> a, h2, h2 > a, h3, h3 > a, h4, h4 > a, h5, h5 > a, h6, h6 > a, p';
        $GLOBALS['TL_CONFIG']['hyphenator_wordMin'] = 10;
        $GLOBALS['TL_CONFIG']['hyphenator_hyphen'] = '&shy;';
        $GLOBALS['TL_CONFIG']['hyphenator_skipPages'] = [];
        $GLOBALS['TL_CONFIG']['hyphenator_enableCache'] = true;
        $GLOBALS['TL_CONFIG']['hyphenator_hyphenedLeftMin'] = 6;
        $GLOBALS['TL_CONFIG']['hyphenator_hyphenedRightMin'] = 6;

        return [
            [
                '<p>We have some really long words in german like sauerstofffeldflasche.</p>',
                $this->getPage(),
                $this->getConfig(['hyphenator_skipPages' => [2]]),
                '<p>We have some really long words in german like sauerstofffeldflasche.</p>',
            ],
            [
                '<p>We have some really long words in german like sauerstofffeldflasche.</p>',
                $this->getPage(),
                $this->getConfig(),
                '<p>We have some really long words in german like sau&shy;er&shy;stoff&shy;feld&shy;fla&shy;sche.</p>',
            ],
            [
                '<p>Kromě toho můžete závodit na cyklotrenažerech, řešit hádanky, vyrábět a stavět z Lega.</p>',
                $this->getPage(['customLineBreakExceptions' => true, 'lineBreakExceptions' => [[], ['search' => '(\s\w{1})(\s)', 'replace' => '$1&nbsp;']]]),
                $this->getConfig(['hyphenator_locale_language_mapping' => ['cz' => 'cs']]),
                '<p>Krom&#283; toho m&#367;&#382;ete z&aacute;vodit na cy&shy;klot&shy;ren&shy;a&#382;e&shy;rech, &#345;e&scaron;it h&aacute;danky, vyr&aacute;b&#283;t<span class="text-nowrap"> a&nbsp;</span>stav&#283;t<span class="text-nowrap"> z&nbsp;</span>Lega.</p>',
            ],
            [
                '<p>Die Musterfirma AG hat den Kredit in Höhe von 126 Mio. € bewilligt und somit die Realisierung der ersten 81.000 m² finanziert.</p>',
                $this->getPage(['customLineBreakExceptions' => true, 'lineBreakExceptions' => [['search' => 'Musterfirma AG', 'replace' => ''], ['search' => '(\d|€)(\s)(\w)', 'replace' => '$1[nbsp]$3']]]),
                $this->getConfig(['hyphenator_locale_language_mapping' => ['cz' => 'cs']]),
                '<p>Die <span class="text-nowrap">Mus&shy;ter&shy;fir&shy;ma&nbsp;AG</span> hat den Kredit in H&ouml;he von 12<span class="text-nowrap">6&nbsp;M</span>io. &euro; bewilligt und somit die Rea&shy;li&shy;sie&shy;rung der ersten 81.00<span class="text-nowrap">0&nbsp;m</span>&sup2; fi&shy;nan&shy;ziert.</p>',
            ],
            [
                '<p>Die Musterfirma AG wurde unter dem Namen Musterfirmas gegründet.</p>',
                $this->getPage(['customLineBreakExceptions' => true, 'lineBreakExceptions' => [['search' => 'Musterfirma(?:\sAG)|Musterfirma(?:s)', 'replace' => '']]]),
                $this->getConfig(['hyphenator_locale_language_mapping' => ['cz' => 'cs']]),
                '<p>Die <span class="text-nowrap">Mus&shy;ter&shy;fir&shy;ma&nbsp;AG</span> wurde unter dem Namen <span class="text-nowrap">Mus&shy;ter&shy;fir&shy;mas</span> gegr&uuml;ndet.</p>',
            ],
            [
                '<p>We have some really long words in german like <a href="http://sauerstofffeldflasche.de" title="sauerstofffeldflasche">sauerstofffeldflasche.</a></p>',
                $this->getPage(),
                $this->getConfig(),
                '<p>We have some really long words in german like <a href="http://sauerstofffeldflasche.de" title="sauerstofffeldflasche">sau&shy;er&shy;stoff&shy;feld&shy;fla&shy;sche.</a></p>',
            ],
            [
                '<!DOCTYPE html><html><head></head><body class="index <esi:include src="/_fragment?_path=insertTag={{ua::class}}&_format=html&_locale=de&_controller=contao.controller.insert_tags:renderAction&clientCache=0&pageId=4&request=de/&_hash=zZYbGzqkVE2ZqKSMgbxxQT6iXorr3OSFLJVqBiou0HE=" onerror="continue"/>"><p>We have some really long words in german like sauerstofffeldflasche.</p></body></html>',
                $this->getPage(),
                $this->getConfig(),
                "<!DOCTYPE html>\n<html><head></head><body class=\"index <esi:include src=\"/_fragment?_path=insertTag={{ua::class}}&_format=html&_locale=de&_controller=contao.controller.insert_tags:renderAction&clientCache=0&pageId=4&request=de/&_hash=zZYbGzqkVE2ZqKSMgbxxQT6iXorr3OSFLJVqBiou0HE=\" onerror=\"continue\"/>\"><p>We have some really long words in german like sau&shy;er&shy;stoff&shy;feld&shy;fla&shy;sche.</p></body></html>\n",
            ],
            [
                '<p>Familienunternehmen</p>',
                $this->getPage(),
                $this->getConfig(),
                '<p>Fa&shy;mi&shy;li&shy;en&shy;un&shy;ter&shy;neh&shy;men</p>',
            ],
            [
                '<p>Wasserwirtschaft</p>',
                $this->getPage(),
                $this->getConfig(),
                '<p>Was&shy;ser&shy;wirt&shy;schaft</p>',
            ],
            [
                '<h1><a href="http://sauerstofffeldflasche.de" title="sauerstofffeldflasche">We have some really long words in german like sauerstofffeldflasche.</a></h1>',
                $this->getPage(),
                $this->getConfig(),
                '<h1><a href="http://sauerstofffeldflasche.de" title="sauerstofffeldflasche">We have some really long words in german like sau&shy;er&shy;stoff&shy;feld&shy;fla&shy;sche.</a></h1>',
            ],
            [
                '<p><br> Tel: +49 40 123 45 67<br> Fax: +49 40 123 45 68 <br> {{email::test@test.de} <br> <br><a class="more" href="mailto:{{email_url::test@test.de}}">Kontaktieren Sie uns</a></p>',
                $this->getPage(),
                $this->getConfig(),
                '<p><br> Tel: +49 40 123 45 67<br> Fax: +49 40 123 45 68 <br> {{email::test@test.de} <br> <br><a class="more" href="mailto:%7B%7Bemail_url::test@test.de%7D%7D">Kon&shy;tak&shy;tie&shy;ren Sie uns</a></p>',
            ],
            [
                '<h1><a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;&#116;&#x65;&#x73;&#x74;&#64;&#x74;&#101;&#115;&#x74;&#46;&#99;&#x6F;&#109;" title="email obfuscation test for test@test.com">&#116;&#x65;&#x73;&#x74;&#64;&#x74;&#101;&#115;&#x74;&#46;&#99;&#x6F;&#109;</a></h1>',
                $this->getPage(),
                $this->getConfig(),
                '<h1><a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;&#116;&#x65;&#x73;&#x74;&#64;&#x74;&#101;&#115;&#x74;&#46;&#99;&#x6F;&#109;" title="email obfuscation test for test@test.com">&#116;&#x65;&#x73;&#x74;&#64;&#x74;&#101;&#115;&#x74;&#46;&#99;&#x6F;&#109;</a></h1>',
            ],
            [
                '<picture><!--[if IE 9]><video style="display: none;"><![endif]--><source srcset="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkqAcAAIUAgUW0RjgAAAAASUVORK5CYII=" media="(min-width: 853px)"><!--[if IE 9]></video><![endif]--><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkqAcAAIUAgUW0RjgAAAAASUVORK5CYII=" data-wrapper="#image-wrapper-1368074709" class="image" alt=""></picture>',
                $this->getPage(),
                $this->getConfig(),
                '<picture><!--[if IE 9]><video style="display: none;"><![endif]--><source srcset="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkqAcAAIUAgUW0RjgAAAAASUVORK5CYII=" media="(min-width: 853px)"><!--[if IE 9]></video><![endif]--><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkqAcAAIUAgUW0RjgAAAAASUVORK5CYII=" data-wrapper="#image-wrapper-1368074709" class="image" alt=""></picture>',
            ],
        ];
    }

    public function getPage(array $data = [])
    {
        $page['id'] = 2;
        $page['language'] = 'de';
        $page['customLineBreakExceptions'] = false;
        $page['rootId'] = 1;
        $page['pid'] = 1;
        $page['hyphenation'] = 'active';

        $data = array_merge($page, $data);

        $GLOBALS['TL_LANGUAGE'] = $data['language'];

        return $data;
    }

    public function getConfig(array $data = [])
    {
        $config['hyphenator_tags'] = 'h1, h1> a, h2, h2 > a, h3, h3 > a, h4, h4 > a, h5, h5 > a, h6, h6 > a, p';
        $config['hyphenator_wordMin'] = 10;
        $config['hyphenator_hyphen'] = '&shy;';
        $config['hyphenator_skipPages'] = [];

        return array_merge($config, $data);
    }

    protected function getFixturesDir(): string
    {
        return __DIR__.\DIRECTORY_SEPARATOR.'..'.\DIRECTORY_SEPARATOR.'Fixtures';
    }
}

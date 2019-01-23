<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HyphenatorBundle\Tests\Hyphenator;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\HyphenatorBundle\Hyphenator\FrontendHyphenator;

class FrontendHyphenatorTest extends ContaoTestCase
{
    /**
     * @var ContaoFrameworkInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $framework;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (!\defined('TL_ROOT')) {
            \define('TL_ROOT', $this->getFixturesDir());
        }

        $this->framework = $this->mockContaoFramework();
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $listener = new FrontendHyphenator($this->framework);

        $this->assertInstanceOf(FrontendHyphenator::class, $listener);
    }

    /**
     * Test hyphenate without page context.
     */
    public function testHyphenateWithoutPageContext()
    {
        $listener = new FrontendHyphenator($this->framework);

        $this->assertSame('test', $listener->hyphenate('test'));
    }

    /**
     * Test hyphenate without page context.
     *
     * @dataProvider hyphenationProvider
     */
    public function testHyphenate($buffer, array $pageData, array $config, $expected)
    {
        $container = $this->mockContainer();

        $container->set('contao.resource_finder', new ResourceFinder([$this->getFixturesDir().'/vendor/contao/core-bundle/src/Resources/contao']));
        $container->setParameter('kernel.cache_dir', $this->getFixturesDir().'/var/cache');

        System::setContainer($container);

        $listener = new FrontendHyphenator($this->framework);

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

        return [
            [
                '<p>We have some really long words in german like sauerstofffeldflasche.</p>',
                $this->getPage(),
                $this->getConfig(['hyphenator_skipPages' => [1]]),
                '<p>We have some really long words in german like sauerstofffeldflasche.</p>',
            ],
            [
                '<p>We have some really long words in german like sauerstofffeldflasche.</p>',
                $this->getPage(),
                $this->getConfig(),
                '<p>We have some really long words in german like sau&shy;er&shy;stoff&shy;feld&shy;fla&shy;sche.</p>',
            ],
            [
                '<p>We have some really long words in german like <a href="http://sauerstofffeldflasche.de" title="sauerstofffeldflasche">sauerstofffeldflasche.</a></p>',
                $this->getPage(),
                $this->getConfig(),
                '<p>We have some really long words in german like <a href="http://sauerstofffeldflasche.de" title="sauerstofffeldflasche">sau&shy;er&shy;stoff&shy;feld&shy;fla&shy;sche.</a></p>',
            ],
            [
                '<!DOCTYPE html><html><head></head><body class="index <esi:include src="/_fragment?_path=insertTag={{ua::class}}&_format=html&_locale=de&_controller=contao.controller.insert_tags:renderAction&clientCache=0&pageId=4&request=de/&_hash=zZYbGzqkVE2ZqKSMgbxxQT6iXorr3OSFLJVqBiou0HE=" onerror="continue" />"><p>We have some really long words in german like sauerstofffeldflasche.</p></body></html>',
                $this->getPage(),
                $this->getConfig(),
                "<!DOCTYPE html>\n<html><head></head><body class='index <esi:include src=\"/_fragment?_path=insertTag={{ua::class}}&_format=html&_locale=de&_controller=contao.controller.insert_tags:renderAction&clientCache=0&pageId=4&request=de/&_hash=zZYbGzqkVE2ZqKSMgbxxQT6iXorr3OSFLJVqBiou0HE=\" onerror=\"continue\"/>'><p>We have some really long words in german like sau&shy;er&shy;stoff&shy;feld&shy;fla&shy;sche.</p></body></html>\n",
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
                '<p><br> Tel: +49 40 123 45 67<br> Fax: +49 40 123 45 68 <br> {{email::test@test.de} <br> <br> <a class="more" href="mailto:{{email_url::test@test.de}}">Kontaktieren Sie uns</a></p>',
                $this->getPage(),
                $this->getConfig(),
                '<p><br> Tel: +49 40 123 45 67<br> Fax: +49 40 123 45 68 <br> {{email::test@test.de} <br><br><a class="more" href="mailto:%7B%7Bemail_url::test@test.de%7D%7D">Kon&shy;tak&shy;tie&shy;ren Sie uns</a></p>',
            ],
            [
                '<h1><a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;&#116;&#x65;&#x73;&#x74;&#64;&#x74;&#101;&#115;&#x74;&#46;&#99;&#x6F;&#109;" title="email obfuscation test for test@test.com">&#116;&#x65;&#x73;&#x74;&#64;&#x74;&#101;&#115;&#x74;&#46;&#99;&#x6F;&#109;</a></h1>',
                $this->getPage(),
                $this->getConfig(),
                '<h1><a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;&#116;&#x65;&#x73;&#x74;&#64;&#x74;&#101;&#115;&#x74;&#46;&#99;&#x6F;&#109;" title="email obfuscation test for test@test.com">&#116;&#x65;&#x73;&#x74;&#64;&#x74;&#101;&#115;&#x74;&#46;&#99;&#x6F;&#109;</a></h1>',
            ],
        ];
    }

    public function getPage(array $data = [])
    {
        $page['id'] = 1;
        $page['language'] = 'de';

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

    /**
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__.\DIRECTORY_SEPARATOR.'..'.\DIRECTORY_SEPARATOR.'Fixtures';
    }
}

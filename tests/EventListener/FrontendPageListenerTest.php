<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HyphenatorBundle\Tests\EventListener;

use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\HyphenatorBundle\EventListener\FrontendPageListener;
use HeimrichHannot\HyphenatorBundle\Hyphenator\FrontendHyphenator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FrontendPageListenerTest extends ContaoTestCase
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

        $this->container = $this->mockContainer();
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $listener = new FrontendPageListener($this->container);

        $this->assertInstanceOf(FrontendPageListener::class, $listener);
    }

    /**
     * Test modifyFrontendPage hook.
     */
    public function testModifyFrontendPage()
    {
        $container = $this->mockContainer();
        $container->set('huh.hyphenator.frontend', new FrontendHyphenator($this->container));

        System::setContainer($container);

        $pageData = ['language' => 'de'];

        global $objPage;
        $objPage = (object) $pageData;

        $listener = new FrontendPageListener($this->container);
        $this->assertSame('test', $listener->modifyFrontendPage('test', 'fe_page'));
    }
}

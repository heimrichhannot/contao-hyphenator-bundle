<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HyphenatorBundle\Tests\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\HyphenatorBundle\EventListener\FrontendPageListener;
use HeimrichHannot\HyphenatorBundle\Hyphenator\FrontendHyphenator;

class FrontendPageListenerTest extends ContaoTestCase
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

        $this->framework = $this->mockContaoFramework();
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $listener = new FrontendPageListener($this->framework);

        $this->assertInstanceOf(FrontendPageListener::class, $listener);
    }

    /**
     * Test modifyFrontendPage hook.
     */
    public function testModifyFrontendPage()
    {
        $container = $this->mockContainer();
        $container->set('huh.hyphenator.frontend', new FrontendHyphenator($this->framework));

        System::setContainer($container);

        $listener = new FrontendPageListener($this->framework);
        $this->assertSame('test', $listener->modifyFrontendPage('test', 'fe_page'));
    }
}

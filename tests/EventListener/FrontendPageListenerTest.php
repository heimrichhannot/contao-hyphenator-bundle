<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HyphenatorBundle\Tests\EventListener;

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
    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->mockContainer();
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $listener = new FrontendPageListener($this->createMock(FrontendHyphenator::class));

        $this->assertInstanceOf(FrontendPageListener::class, $listener);
    }

    /**
     * Test modifyFrontendPage hook.
     */
    public function testModifyFrontendPage()
    {
        $hypenatorMock = $this->createMock(FrontendHyphenator::class);
        $hypenatorMock->method('hyphenate')->willReturn('hyphenated text');

        $listener = new FrontendPageListener($hypenatorMock);
        $this->assertSame('hyphenated text', $listener->modifyFrontendPage('test', 'fe_page'));
    }
}

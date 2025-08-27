<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\UtilsBundle\Tests\DependencyInjection;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\HyphenatorBundle\DependencyInjection\HyphenatorExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class HyphenatorExtensionTest extends ContaoTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $container = new ContainerBuilder(new ParameterBag(['kernel.debug' => false]));
        $extension = new HyphenatorExtension();
        $extension->load([], $container);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $extension = new HyphenatorExtension();
        $this->assertInstanceOf(HyphenatorExtension::class, $extension);
    }
}

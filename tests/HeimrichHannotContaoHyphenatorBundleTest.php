<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HyphenatorBundle\Tests;

use HeimrichHannot\HyphenatorBundle\DependencyInjection\HyphenatorExtension;
use HeimrichHannot\HyphenatorBundle\HeimrichHannotContaoHyphenatorBundle;
use PHPUnit\Framework\TestCase;

class HeimrichHannotContaoHyphenatorBundleTest extends TestCase
{
    public function testGetContainerExtension()
    {
        $bundle = new HeimrichHannotContaoHyphenatorBundle();

        $this->assertInstanceOf(HyphenatorExtension::class, $bundle->getContainerExtension());
    }
}

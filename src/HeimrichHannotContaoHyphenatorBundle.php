<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HyphenatorBundle;

use HeimrichHannot\HyphenatorBundle\DependencyInjection\HyphenatorExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoHyphenatorBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new HyphenatorExtension();
    }
}

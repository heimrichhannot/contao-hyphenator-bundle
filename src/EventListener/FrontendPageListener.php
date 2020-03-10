<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HyphenatorBundle\EventListener;

use Contao\System;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FrontendPageListener
{
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

    /**
     * Listen on modifyFrontendPage hook.
     *
     * @param $strBuffer
     * @param $strTemplate
     *
     * @return mixed
     */
    public function modifyFrontendPage($strBuffer, $strTemplate)
    {
        return System::getContainer()->get('huh.hyphenator.frontend')->hyphenate($strBuffer);
    }
}

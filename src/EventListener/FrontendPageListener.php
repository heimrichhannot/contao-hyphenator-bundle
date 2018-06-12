<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HyphenatorBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;

class FrontendPageListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * Request constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
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

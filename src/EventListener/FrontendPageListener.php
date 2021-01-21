<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\HyphenatorBundle\EventListener;

use HeimrichHannot\HyphenatorBundle\Hyphenator\FrontendHyphenator;

class FrontendPageListener
{
    /**
     * @var FrontendHyphenator
     */
    protected $frontendHyphenator;

    /**
     * Request constructor.
     */
    public function __construct(FrontendHyphenator $frontendHyphenator)
    {
        $this->frontendHyphenator = $frontendHyphenator;
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
        return $this->frontendHyphenator->hyphenate($strBuffer);
    }
}

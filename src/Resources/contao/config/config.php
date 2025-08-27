<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package ${CARET}
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

use Contao\System;
use HeimrichHannot\HyphenatorBundle\EventListener\FrontendPageListener;
use HeimrichHannot\UtilsBundle\Util\Utils;

/**
 * Config
 */

$GLOBALS['TL_CONFIG']['hyphenator_tags']             = 'h1:not(:empty):not(.hyphen-none), h2:not(:empty):not(.hyphen-none), h3:not(:empty):not(.hyphen-none), h4:not(:empty):not(.hyphen-none), h5:not(:empty):not(.hyphen-none), h6:not(:empty):not(.hyphen-none), p:not(:empty):not(.hyphen-none), a:not(:empty):not(.hyphen-none), dt:not(:empty):not(.hyphen-none), dd:not(:empty):not(.hyphen-none)';
$GLOBALS['TL_CONFIG']['hyphenator_wordMin']          = 10;
$GLOBALS['TL_CONFIG']['hyphenator_hyphenedLeftMin']  = 6;
$GLOBALS['TL_CONFIG']['hyphenator_hyphenedRightMin'] = 6;
$GLOBALS['TL_CONFIG']['hyphenator_hyphen']           = '&shy;';
$GLOBALS['TL_CONFIG']['hyphenator_skipPages']        = [];
$GLOBALS['TL_CONFIG']['hyphenator_enableCache']      = true;
// map page language to .tex files
$GLOBALS['TL_CONFIG']['hyphenator_locale_language_mapping']['en'] = 'en-us';
$GLOBALS['TL_CONFIG']['hyphenator_locale_language_mapping']['cz'] = 'cs';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['modifyFrontendPage'][] = [FrontendPageListener::class, 'modifyFrontendPage'];

/**
 * Css
 */
if (System::getContainer()->get(Utils::class)->container()->isFrontend()) {
    $GLOBALS['TL_USER_CSS']['hyphenator'] = 'bundles/heimrichhannotcontaohyphenator/css/hyphenator.min.css|static';
}

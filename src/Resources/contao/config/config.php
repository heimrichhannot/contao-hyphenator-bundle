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

/**
 * Config
 */
$GLOBALS['TL_CONFIG']['hyphenator_tags']        = 'h1:not(:empty), h2:not(:empty), h3:not(:empty), h4:not(:empty), h5:not(:empty), h6:not(:empty), p:not(:empty), a:not(:empty)';
$GLOBALS['TL_CONFIG']['hyphenator_wordMin']     = 10;
$GLOBALS['TL_CONFIG']['hyphenator_hyphen']      = '&shy;';
$GLOBALS['TL_CONFIG']['hyphenator_skipPages']   = [];
$GLOBALS['TL_CONFIG']['hyphenator_enableCache'] = true;

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['modifyFrontendPage'][] = ['huh.hyphenator.frontendPageListener', 'modifyFrontendPage'];

/**
 * Css
 */
if (System::getContainer()->get('huh.utils.container')->isFrontend()) {
    $GLOBALS['TL_USER_CSS']['hyphenator'] = 'bundles/heimrichhannotcontaohyphenator/css/hyphenator.min.css|static';
}
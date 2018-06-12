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
$GLOBALS['TL_CONFIG']['hyphenator_tags']       = 'h1, h1> a, h2, h2 > a, h3, h3 > a, h4, h4 > a, h5, h5 > a, h6, h6 > a, p';
$GLOBALS['TL_CONFIG']['hyphenator_wordMin']    = 10;
$GLOBALS['TL_CONFIG']['hyphenator_leftMin']    = 5;
$GLOBALS['TL_CONFIG']['hyphenator_rightMin']   = 5;
$GLOBALS['TL_CONFIG']['hyphenator_quality']    = 9;
$GLOBALS['TL_CONFIG']['hyphenator_hyphen']     = '&shy;';
$GLOBALS['TL_CONFIG']['hyphenator_filter']     = 'Simple';
$GLOBALS['TL_CONFIG']['hyphenator_tokenizers'] = ['Whitespace', 'Punctuation'];
$GLOBALS['TL_CONFIG']['hyphenator_skipPages']  = [];


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
<?php

$lang = &$GLOBALS['TL_LANG']['tl_page'];

/**
 * Fields
 */
$lang['hyphenation']                = ['Hyphenation', 'Enable, disable, or leave blank for inheritance use by the parent pages.'];
$lang['customLineBreakExceptions']  = ['Custom exceptions for line breaks', 'Add custom exceptions for line breaks, to keep words together.'];
$lang['lineBreakExceptions']        = ['Line break exceptions', 'Add white space search patterns (also regular expressions allowed) to prevent a line break between these words.'];
$lang['lineBreakExceptions_search'] = ['Search pattern', 'Assign a search pattern (regular expressions allowed). Beispiel: "My Company GmbH", (\s\w{1})(\s)'];

/**
 * Legends
 */
$lang['hyphenator_legend'] = 'Hyphenation & line breaks';

/**
 * References
 */
$lang['reference']['hyphenation']['active']   = 'Enabled';
$lang['reference']['hyphenation']['inactive'] = 'Disabled';
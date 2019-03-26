<?php

$dc = &$GLOBALS['TL_DCA']['tl_page'];

/**
 * Selectors
 */
$dc['palettes']['__selector__'][] = 'customLineBreakExceptions';


/**
 * Palettes
 */
$dc['palettes']['root']    = str_replace('{layout_legend', '{hyphenator_legend},hyphenation,customLineBreakExceptions;{layout_legend', $dc['palettes']['root']);
$dc['palettes']['regular'] = str_replace('{layout_legend', '{hyphenator_legend},hyphenation,customLineBreakExceptions;{layout_legend', $dc['palettes']['regular']);

/**
 * Subpalettes
 */
$dc['subpalettes']['customLineBreakExceptions'] = 'lineBreakExceptions';

$fields = [
    'hyphenation'               => [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['hyphenation'],
        'inputType' => 'select',
        'default'   => 'inactive',
        'options'   => ['active', 'inactive'],
        'reference' => $GLOBALS['TL_LANG']['tl_page']['reference']['hyphenation'],
        'eval'      => ['includeBlankOption' => true, 'tl_class' => 'w50'],
        'sql'       => "char(8) NOT NULL default ''",
    ],
    'customLineBreakExceptions' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['customLineBreakExceptions'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr'],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'lineBreakExceptions'       => [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['lineBreakExceptions'],
        'inputType' => 'multiColumnEditor',
        'eval'      => [
            'tl_class'          => 'long clr',
            'multiColumnEditor' => [
                'fields' => [
                    'search' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_page']['lineBreakExceptions_search'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        'eval'      => ['tl_class' => 'w50', 'mandatory' => true, 'groupStyle' => 'width: 80%;'],
                    ],
                ],
            ],
        ],
        'sql'       => "blob NULL"
    ]
];

$dc['fields'] = array_merge($dc['fields'], $fields);
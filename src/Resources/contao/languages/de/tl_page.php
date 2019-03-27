<?php

$lang = &$GLOBALS['TL_LANG']['tl_page'];

/**
 * Fields
 */
$lang['hyphenation']                = ['Silbentrennung', 'Silbentrennung auf dieser Seite aktivieren, deaktivieren oder leer lassen für die Verwendung der Vererbung von den übergeordneten Seiten.'];
$lang['customLineBreakExceptions']  = ['Benutzerdefinierte Ausnahmen für Zeilenumbrüche hinzufügen', 'Fügen Sie benutzerdefinierte Ausnahmen für Zeilenumbrüche hinzu, um Wörter zusammenzuhalten.'];
$lang['lineBreakExceptions']        = ['Ausnahmen für Zeilenumbrüche', 'Hinzufügen von Leerraum-Suchmustern (auch reguläre Ausdrücke zulässig) um einen Zeilenumbruch zwischen diesen Wörtern zu verhindern.'];
$lang['lineBreakExceptions_search'] = ['Suchmuster', 'Vergeben Sie ein Suchmuster (reguläre sind Ausdrücke erlaubt). Beispiel: "Meine Firma GmbH", (\d)(\s)(\w)'];
$lang['lineBreakExceptions_replace'] = ['Ersetzungsmuster (nur für reguläre Ausdrücke)', 'Vergeben Sie ein Ersetzungsmuster. Beispiel: $1[nbsp]$3'];

/**
 * Legends
 */
$lang['hyphenator_legend'] = 'Silbentrennung & Zeilenumbrüche';


/**
 * References
 */
$lang['reference']['hyphenation']['active']   = 'Aktiviert';
$lang['reference']['hyphenation']['inactive'] = 'Deaktiviert';
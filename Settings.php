<?php
/*
 * Global settings
 */

// See https://www.mediawiki.org/wiki/Manual:$wgUrlProtocols
$wgUrlProtocols[] = 'magnet:';

/*
 * Namespace aliases
 */

$wgNamespaceAliases['MDF'] = 1000;
$wgNamespaceAliases['MDF_TALK'] = 1001;

$wgNamespaceAliases['U'] = 2;
$wgNamespaceAliases['F'] = 6;
$wgNamespaceAliases['T'] = 10;
$wgNamespaceAliases['H'] = 12;
$wgNamespaceAliases['C'] = $wgNamespaceAliases['CAT'] = 14;

$DFReleases = array(
    1 => 110,
    2 => 106,
    3 => 112,
    4 => 114,
    5 => 116,
);
$DFReleaseAliases = array('Rel', 'V', 'R');

foreach ($DFReleases as $id => $ns) {
    foreach ($DFReleaseAliases as $a) {
        $wgNamespaceAliases[$a . $id] = $ns;
        $wgNamespaceAliases[$a . $id . '_talk'] = $ns + 1;
    }
}

/*
 * Extension settings
 */

$wgAutoRedirectNamespaces = array(
    '' => array('DF2014', 'v0.34', 'v0.31', '40d', '23a'),
);


$wgAutoWelcomeUserText = '{{subst:welcome user}}';
$wgAutoWelcomeUserAuthor = 'LethosorBot';

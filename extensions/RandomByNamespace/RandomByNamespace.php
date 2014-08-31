<?php

$wgExtensionCredits['specialpage'][] = array(
    'path' => __FILE__,
    'name' => 'RandomByNamespace',
    'author' => 'Lethosor',
    'url' => 'https://github.com/lethosor/DFWikiFunctions',
    'descriptionmsg' => 'random-by-namespace-desc',
    'version' => '0.3',
);

$wgExtensionMessagesFiles['RandomByNamespace'] = __DIR__ . '/RandomByNamespace.i18n.php';
$wgExtensionMessagesFiles['RandomByNamespaceAlias'] = __DIR__ . '/RandomByNamespace.alias.php';
$wgAutoloadClasses['SpecialRandomByNamespace'] = __DIR__ . '/SpecialRandomByNamespace.php';
$wgSpecialPages['RandomByNamespace'] = 'SpecialRandomByNamespace';

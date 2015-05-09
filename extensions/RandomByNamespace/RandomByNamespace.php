<?php

$wgExtensionCredits['specialpage'][] = array(
    'path' => __FILE__,
    'name' => 'RandomByNamespace',
    'author' => 'Lethosor',
    'url' => 'https://github.com/lethosor/DFWikiFunctions',
    'descriptionmsg' => 'randombynamespace-desc',
    'version' => '0.4',
);

$wgExtensionMessagesFiles['RandomByNamespace'] = __DIR__ . '/RandomByNamespace.i18n.php';
$wgExtensionMessagesFiles['RandomByNamespaceAlias'] = __DIR__ . '/RandomByNamespace.alias.php';
$wgAutoloadClasses['SpecialRandomByNamespace'] = __DIR__ . '/SpecialRandomByNamespace.php';
$wgSpecialPages['RandomByNamespace'] = 'SpecialRandomByNamespace';

$wgResourceModules['ext.RandomByNamespace'] = array(
    'scripts' => array(
        'js/randombynamespace.js'
    ),
    'localBasePath' => __DIR__,
    //'remoteExtPath' => 'DFWikiFunctions/extensions/RandomByNamespace',
);

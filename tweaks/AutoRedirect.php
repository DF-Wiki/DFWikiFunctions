<?php

/**
 * AutoRedirect: Automatically redirects to pages.
 * Supports redirecting to pages in different namespaces and/or altering titles
 * (e.g. changing to lowercase automatically)
 */

$wgExtensionCredits['AutoRedirect'][] = array(
    'path' => __FILE__,
    'name' => 'AutoRedirect',
    'author' =>'Lethosor',
    'url' => 'https://github.com/DF-Wiki/DFWikiFunctions',
    'description' => 'Automatically fixes issues with double redirects',
    'version'  => '3.0-alpha',
);

// (ns name) => [(ns name), ...]
$wgAutoRedirectNamespaces = array();

class AutoRedirect {
    private static $NsConfig = null;

    static function getNsConfig () {
        if (self::$NsConfig == null) {
            global $wgAutoRedirectNamespaces;
            $config = array();
            foreach ($wgAutoRedirectNamespaces as $ns => $list) {
                if (!is_array($list)) $list = array($list);
                $ns = self::toNamespace($ns);
                foreach ($list as $k => $v) {
                    $list[$k] = self::toNamespace($v);
                }
                $config[$ns] = $list;
            }
            self::$NsConfig = $config;
        }
        return self::$NsConfig;
    }

    static function toNamespace ($text) {
        return Title::newFromText("$text:Dummy")->getNamespace();
    }

    static function redirect ($title) {
        $config = self::getNsConfig();
        if (array_key_exists($title->getNamespace(), $config) && $title->exists()) {
            $content = Revision::newFromTitle($title)->getContent();
            if (strpos($content->getNativeData(), "#SUPERAUTOREDIRECT") === 0) {
                return Title::makeTitleSafe($config[$title->getNamespace()][0], $title->getBaseText());
            }
        }
    }
}

$wgHooks['InitializeArticleMaybeRedirect'][] = function($title, $request, &$ignoreRedirect, &$target, &$article) {
    // Handles redirects

    $new = AutoRedirect::redirect($title);

    if ($new) {
        $target = $new;
        $ignoreRedirect = false;
    }
    return true;
};

$wgHooks['BeforeParserFetchTemplateAndtitle'][] = function($parser, $title, &$skip, &$id) {
    // Handles transclusions
    $new = AutoRedirect::redirect($title);
    if ($new) {
        $id = $new->getLatestRevID();
    }
    return true;
};

$wgHooks['InternalParseBeforeLinks'][] = function($parser, &$text) {
    $new = AutoRedirect::redirect($parser->mTitle);
    if ($new) {
        $text = str_replace("#SUPERAUTOREDIRECT", "#SUPERAUTOREDIRECT [[{$new->getFullText()}]]", $text);
    }
    return true;
};

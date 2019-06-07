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
    'url' => 'https://github.com/lethosor/DFWikiFunctions',
    'description' => 'Automatically redirects pages to more appropriate titles',
    'version'  => '1.1.0',
);

$wgAutoRedirectNamespaces = array();
$wgAutoRedirectChecks = array();
$wgAutoRedirectUsername = 'AutoRedirectBot';

class AutoRedirect {
    private static $NsConfig = null;
    static function toString ($s) {
        return (string) $s;
    }
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
    static function findNextTitle ($title, $config, $configNs=null) {
        /**
         * Returns the next title that can be reached from $title, or
         * false if none can be found.
         *
         * $title: Title object
         * $config: configuration to use (e.g. AutoRedirect::getNsConfig())
         * $ns: If set, use as the key for $config instead of $title->getNamespace()
         */
        global $wgAutoRedirectChecks;
        array_unshift($wgAutoRedirectChecks, 'AutoRedirect::toString');
        // Namespace ID and text of original title
        $titleNs = $title->getNamespace();
        $titleText = $title->getBaseText();
        if ($configNs === null) {
            $configNs = $titleNs;
        }
        if (array_key_exists($configNs, $config)) {
            foreach ($config[$configNs] as $ns) {
                foreach ($wgAutoRedirectChecks as $func) {
                    $newText = call_user_func($func, $titleText);
                    $new = Title::makeTitleSafe($ns, $newText);
                    if (!$new) continue;
                    if ($new->isRedirect()) {
                                    $content = ContentHandler::getContentText(WikiPage::factory($new)->getRevision()->getContent(Revision::RAW));
                        $content = ContentHandler::makeContent($content, null, CONTENT_MODEL_WIKITEXT);
                                    $new = $content->getRedirectTarget();
                    if ($new) return $new;
                        else return false;
                    }
                    if ($new->exists()) {
                        return $new;
                    }
                }
            }
        }
        return false;
    }
    static function findDestinationTitle ($title, $limit=2) {
        /**
         * Calls findNextTitle with $title until it returns false
         */
        $originalNs = $title->getNamespace();
        $config = self::getNsConfig();
        $new = $title;
        while ($new && $limit-- > 0) {
            $title = $new;
            $new = self::findNextTitle($title, $config, $originalNs);
        }
        return $title;
    }
    static function redirect ($title, $createLink = true) {
        /**
         * Takes a Title and returns a new Title to redirect to, or false if
         * the current title is acceptable.
         */
        global $wgMaxRedirects, $wgAutoRedirectUsername;
        if ($title->exists()) {
            $rev = Revision::newFromTitle($title);
            $content = $rev->getContent();

            if ($content->getNativeData() !== "#SUPERAUTOREDIRECT") {
                return false;
            }
        }

        if ($title->getFragment() && !($title->getText())) {
            // skip [[#Section]] links
            return false;
        }

        if (!($title instanceof Title)) {
            $title = Title::newFromText($title);
        }
        $limit = max(2, $wgMaxRedirects);
        $new = self::findDestinationTitle($title, $limit);
        if ($new->getFullText() == $title->getFullText()) {
            return false;
        } else {
            if ($createLink && !$title->exists()) {
                $page = WikiPage::factory($title);
                $content = ContentHandler::makeContent( "#SUPERAUTOREDIRECT", $title );
                $page->doEditContent($content, "", EDIT_FORCE_BOT, false, User::newFromName($wgAutoRedirectUsername));
            }
            return $new;
        }
    }
    static function PrefixSearchBackend ($namespaces, $search, $limit, &$results) {
        // Based on PrefixSearch::defaultSearchBackend
        global $wgAutoRedirectNamespaces;
        if ($namespaces[0] == NS_MAIN) {
            $namespaces = $wgAutoRedirectNamespaces[''];
        }
        $srchres = array();
        foreach ($namespaces as $ns) {
            if (count($srchres) > $limit)
                break;
            $ns = self::toNamespace($ns);
            $req = new FauxRequest( array(
                'action' => 'query',
                'list' => 'allpages',
                'apnamespace' => $ns,
                'aplimit' => $limit,
                'apprefix' => $search
            ));
            // Execute
            $module = new ApiMain($req);
            $module->execute();
            $data = $module->getResultData();
            foreach ((array)$data['query']['allpages'] as $pageinfo) {
                // Note: this data will not be printable by the xml engine
                // because it does not support lists of unnamed items
                $srchres[] = $pageinfo['title'];
            }
        }
        $results = $srchres;
        return false;
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
$wgHooks['TitleIsAlwaysKnown'][] = function($title, &$result) {
    // Handles links (prevents them from appearing as redlinks when they actually work)
    $new = AutoRedirect::redirect($title, false);
    if ($new) {
        $result = true;
    }
    return true;
};

$wgHooks['UserGetReservedNames'][] = function(&$usernames) {
    global $wgAutoRedirectUsername;
    $usernames[] = $wgAutoRedirectUsername;
};

$wgHooks['PrefixSearchBackend'][] = 'AutoRedirect::PrefixSearchBackend';

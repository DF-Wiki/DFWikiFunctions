<?php

/**
 * CVRedirect
 * Treats nonexistent mainspace pages as redirects to the cv: page with the
 * same name if it exists.
 */

$wgExtensionCredits['CVRedirect'][] = array(
	'path' => __FILE__,
	'name' => 'CVRedirect',
	'author' =>'Lethosor',
	'url' => 'https://github.com/lethosor/DFWikiFunctions',
	'description' => 'Automatically redirects pages in the main namespace to versioned pages',
	'version'  => '0.1',
);

$wgAutoRedirectNamespaces = array();
$wgAutoRedirectChecks = array();

class CVRedirect {
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
	static function findNextTitle ($title, $config) {
		/**
		 * Returns the next title that can be reached from $title, or
		 * false if none can be found.
		 */
		global $wgAutoRedirectChecks;
		array_unshift($wgAutoRedirectChecks, 'CVRedirect::toString');
		// Namespace ID and text of original title
		$titleNs = $title->getNamespace();
		$titleText = $title->getText();
		if (array_key_exists($titleNs, $config)) {
			foreach ($config[$titleNs] as $ns) {
				foreach ($wgAutoRedirectChecks as $func) {
					$newText = call_user_func($func, $titleText);
					$new = Title::makeTitle($ns, $newText);
					var_dump(array($func, $ns, $newText));
					if ($new->exists()) {
						var_dump(true);
						return $new;
					}
					var_dump(false);
				}
			}
		}
		return false;
	}
	static function findDestinationTitle ($title, $limit=2) {
		/**
		 * Calls findNextTitle with $title until it returns false
		 */
		$config = self::getNsConfig();
		$new = $title;
		while ($new && $limit-- > 0) {
			$title = $new;
			$new = self::findNextTitle($title, $config);
		}
		return $title;
	}
	static function redirect ($title) {
		/**
		 * Takes a Title and returns a new Title to redirect to, or false if
		 * the current title is acceptable.
		 */
		global $wgMaxRedirects;
		$limit = min(2, $wgMaxRedirects);
		$new = self::findDestinationTitle($title, $limit);
		if ($new->getFullText() == $title->getFullText()) return false;
		else return $new;
	}
	static function oldredirect ($title, $limit=null) {
		if ($limit === null) {
			global $wgMaxRedirects;
			// 2 redirects are required for this to be useful on the DF wiki;
			// Require at least 2 to be followed, but limit to $wgMaxRedirects
			$limit = min(2, $wgMaxRedirects);
		}
		if ($limit < 0) {
			// Prevent infinite recursion
			return $title;
		}
		if ($title->mNamespace == NS_MAIN && !$title->exists()) {
			global $wgNamespaceAliases;
			$new = Title::makeTitle($wgNamespaceAliases['CV'], $title->getFullText());
			if ($new->exists()) {
				while ($new->isRedirect()) {
					$limit--;
					if ($limit < 0) break;
					$content = WikiPage::factory($new)->getText();
					$new = Title::newFromRedirect($content);
				}
				return $new;
			}
		}
		elseif ($title->mNamespace == NS_MAIN && $title->isRedirect()) {
			// Handles mainspace redirects to mainspace pseudo-redirects
			$content = WikiPage::factory($title)->getText();
			$new = CVRedirect(Title::newFromRedirect($content), $limit-1);
			if ($new) return $new;
		}
		return false;
	}
}

$wgHooks['InitializeArticleMaybeRedirect'][] = function($title, $request, &$ignoreRedirect, &$target) {
	// Handles redirects
	$new = CVRedirect::redirect($title);
	if ($new) {
		$target = $new;
		$ignoreRedirect = false;
	}
	return true;
};
$wgHooks['BeforeParserFetchTemplateAndtitle'][] = function($parser, $title, &$skip, &$id) {
	// Handles transclusions
	$new = CVRedirect::redirect($title);
	if ($new) {
		$id = $new->getLatestRevID();
		$ignoreRedirect = false;
	}
	return true;
};
$wgHooks['TitleIsAlwaysKnown'][] = function($title, &$result) {
	// Handles links (prevents them from appearing as redlinks when they actually work)
	$new = CVRedirect::redirect($title);
	if ($new) {
		$result = true;
	}
	return true;
};

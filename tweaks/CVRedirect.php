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

function CVRedirect($title) {
	/**
	 * Takes a Title and returns a new Title to redirect to, or false if
	 * the current title is acceptable.
	 */
	if ($title->mNamespace == NS_MAIN && !$title->exists()) {
		global $wgNamespaceAliases;
		$new = Title::makeTitle($wgNamespaceAliases['CV'], $title->getFullText());
		if ($new->exists()) {
			global $wgMaxRedirects;
			// 2 redirects are required for this to be useful on the DF wiki;
			// Require at least 2 to be followed, but limit to $wgMaxRedirects
			$limit = min(2, $wgMaxRedirects);
			while ($new->isRedirect()) {
				$limit--;
				if ($limit < 0) break;
				$content = WikiPage::factory($new)->getText();
				$new = Title::newFromRedirect($content);
			}
			return $new;
		}
	}
	return false;
}
$wgHooks['InitializeArticleMaybeRedirect'][] = function($title, $request, &$ignoreRedirect, &$target) {
	// Handles redirects
	$new = CVRedirect($title);
	if ($new) {
		$target = $new;
		$ignoreRedirect = false;
	}
	return true;
};
$wgHooks['BeforeParserFetchTemplateAndtitle'][] = function($parser, $title, &$skip, &$id) {
	// Handles transclusions
	$new = CVRedirect($title);
	if ($new) {
		$id = $new->getLatestRevID();
		$ignoreRedirect = false;
	}
	return true;
};
$wgHooks['TitleIsAlwaysKnown'][] = function($title, &$result) {
	// Handles links (prevents them from appearing as redlinks when they actually work)
	$new = CVRedirect($title);
	if ($new) {
		$result = true;
	}
	return true;
};

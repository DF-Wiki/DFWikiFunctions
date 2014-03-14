<?php

/**
 * CVRedirect
 * Treats nonexistent mainspace pages as redirects to the cv: page with the
 * same name if it exists
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
        if ($title->mNamespace == NS_MAIN && !$title->exists()) {
                global $wgNamespaceAliases;
                $new = Title::makeTitle($wgNamespaceAliases['CV'], $title->getFullText());
                if ($new->exists()) {
                        $limit = 100;
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
        $new = CVRedirect($title);
        if ($new) {
                $target = $new;
                $ignoreRedirect = false;
        }
        return true;
};
$wgHooks['BeforeParserFetchTemplateAndtitle'][] = function($parser, $title, &$skip, &$id) {
        $new = CVRedirect($title);
        if ($new) {
                $id = $new->getLatestRevID();
                $ignoreRedirect = false;
        }
        return true;

        //echo $title;
        CVRedirect($title, null, $skip, $id);
        // $id is actually a Title object; convert it to a revision ID
        if ($id) $id = $id->getLatestRevID();
        return true;
};
$wgHooks['TitleIsAlwaysKnown'][] = function($title, &$result) {
        $new = CVRedirect($title);
        if ($new) {
                $result = true;
        }
        return true;
};


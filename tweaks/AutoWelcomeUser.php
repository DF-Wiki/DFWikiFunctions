<?php

/**
 * AutoWelcomeUser: Posts a message to new users' talk pages upon user creation
 */

$wgAutoWelcomeUserText = 'Welcome to this wiki, {{subst:PAGENAME}}!';
$wgAutoWelcomeUserAuthor = 'AutoWelcomeUser';
$wgAutoWelcomeUserEditCount = 1;

$wgExtensionCredits['AutoWelcomeUser'][] = array(
    'path' => __FILE__,
    'name' => 'AutoWelcomeUser',
    'author' =>'Lethosor',
    'url' => 'https://github.com/lethosor/DFWikiFunctions',
    'description' => 'Posts a message to new users\' talk pages',
    'version'  => '0.2',
);

class AutoWelcomeUserHooks {
    public static function PageContentSaveComplete ($article, $user /* , ... */) {
        global $wgAutoWelcomeUserText, $wgAutoWelcomeUserAuthor, $wgAutoWelcomeUserEditCount;
        if ($user->getEditCount() != $wgAutoWelcomeUserEditCount - 1) {
            // Edit count is not increased until after this hook runs
            return true;
        }
        $page = WikiPage::factory($user->getTalkPage());
        if ($page->exists())
            return true;
        $author = User::newFromName($wgAutoWelcomeUserAuthor);
        // Avoid sending welcome messages to welcoming user
        if ($user->getName() == $author->getName())
            return true;
        $page->doEdit(
            $wgAutoWelcomeUserText,
            "Welcoming user " . $user->getName(),
            0, false,  // flags, baseRevId
            $author
        );
        return true;
    }
}
$wgHooks['PageContentSaveComplete'][] = 'AutoWelcomeUserHooks::PageContentSaveComplete';

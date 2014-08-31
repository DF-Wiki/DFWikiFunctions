<?php

/**
 * AutoWelcomeUser: Posts a message to new users' talk pages upon user creation
 */

$wgAutoWelcomeUserText = 'Welcome to this wiki, {{subst:PAGENAME}}!';
$wgAutoWelcomeUserAuthor = 'AutoWelcomeUser';

$wgExtensionCredits['AutoWelcomeUser'][] = array(
    'path' => __FILE__,
    'name' => 'AutoWelcomeUser',
    'author' =>'Lethosor',
    'url' => 'https://github.com/lethosor/DFWikiFunctions',
    'description' => 'Posts a message to new users\' talk pages upon user creation',
    'version'  => '0.1',
);

class AutoWelcomeUserHooks {
    public static function AddNewAccount ($user, $byEmail) {
        global $wgAutoWelcomeUserText, $wgAutoWelcomeUserAuthor;
        $page = WikiPage::factory($user->getTalkPage());
        $author = User::newFromName($wgAutoWelcomeUserAuthor);
        $page->doEdit(
            $wgAutoWelcomeUserText,
            "Welcoming user " . $user->getName(),
            0, false,  // flags, baseRevId
            $author
        );
        return true;
    }
}
$wgHooks['AddNewAccount'][] = 'AutoWelcomeUserHooks::AddNewAccount';

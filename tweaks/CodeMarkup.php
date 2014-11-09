<?php

/**
 * CodeMarkup: Adds Markdown-like `code` syntax
 */

$wgExtensionCredits['AutoWelcomeUser'][] = array(
    'path' => __FILE__,
    'name' => 'CodeMarkup',
    'author' =>'Lethosor',
    'url' => 'https://github.com/lethosor/DFWikiFunctions',
    'description' => 'Support for inline code syntax',
    'version'  => '1.0',
);

class CodeMarkup {
    public static function doBackticks ($text) {
        $arr = preg_split('/(\`)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (count($arr) <= 1) {
            return $text;
        }
        $in_code = false;
        // Backticks are at odd-numbered indexes
        for ($i = 1; $i < count($arr); $i += 2) {
            $in_code = !$in_code;
            $arr[$i] = ($in_code) ? '<code>' : '</code>';
        }
        if ($in_code) {
            $arr[] = '</code>';
        }
        return implode($arr);
    }
    public static function doAllBackticks ($text) {
        // Similar to Parser::doAllQuotes
        $output = '';
        $lines = StringUtils::explode("\n", $text);
        foreach ($lines as $line) {
            $output .= self::doBackticks($line) . "\n";
        }
        return substr($output, 0, -1);
    }
    public static function InternalParseBeforeLinks ($parser, &$text) {
        $text = self::doAllBackticks($text);
        return true;
    }
}
$wgHooks['InternalParseBeforeLinks'][] = 'CodeMarkup::InternalParseBeforeLinks';

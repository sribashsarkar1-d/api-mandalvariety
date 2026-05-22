<?php
namespace Helpers;

class SearchHelper {
    public static function normalize($string) {
        if (!$string) return '';
        $string = strtolower(trim($string));
        // Keep only alphanumeric and space
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        return $string;
    }

    public static function levenshteinDistance($str1, $str2) {
        return levenshtein(strtolower($str1), strtolower($str2));
    }
}

<?php
/**
 * Cyrillic → Latin transliteration for slugs.
 *
 * WordPress' sanitize_title() percent-encodes Cyrillic instead of romanising
 * it, which produces URLs like `%d1%82%d0%be%d0%bf%d0%b0%d0%b7`. Hooking the
 * map into `sanitize_title` makes every NEW slug latin automatically —
 * posts, pages, taxonomy terms and anything else that goes through it.
 */

/**
 * Ukrainian-first letter map (Russian-only letters included as a fallback).
 */
function knot_translit_map(): array {
    return [
        // Ukrainian / shared
        'а' => 'a',  'б' => 'b',  'в' => 'v',  'г' => 'h',  'ґ' => 'g',
        'д' => 'd',  'е' => 'e',  'є' => 'ie', 'ж' => 'zh', 'з' => 'z',
        'и' => 'y',  'і' => 'i',  'ї' => 'i',  'й' => 'i',  'к' => 'k',
        'л' => 'l',  'м' => 'm',  'н' => 'n',  'о' => 'o',  'п' => 'p',
        'р' => 'r',  'с' => 's',  'т' => 't',  'у' => 'u',  'ф' => 'f',
        'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
        'ь' => '',   'ю' => 'iu', 'я' => 'ia', 'ʼ' => '',   '’' => '',
        // Russian-only
        'ё' => 'e',  'ъ' => '',   'ы' => 'y',  'э' => 'e',
    ];
}

/**
 * Transliterate a string to lowercase Latin. Non-Cyrillic input is untouched.
 */
function knot_transliterate(string $text): string {
    if (!preg_match('/[^\x00-\x7F]/', $text)) {
        return $text;
    }

    // Built once per request — this runs on every sanitize_title() call.
    static $map = null;

    if ($map === null) {
        $map = knot_translit_map();

        // Uppercase forms map to the same latin letters.
        foreach (knot_translit_map() as $cyr => $lat) {
            if ($cyr === '') continue;
            $map[mb_strtoupper($cyr, 'UTF-8')] = $lat;
        }
    }

    return strtr($text, $map);
}

/**
 * Romanise slugs as they are generated. Runs before WordPress' own
 * sanitize_title_with_dashes (priority 10), so the result is a clean slug.
 */
function knot_sanitize_title_translit($title, $raw_title = '', $context = 'save') {
    if ($context !== 'save' || !is_string($title) || $title === '') {
        return $title;
    }

    return knot_transliterate($title);
}

add_filter('sanitize_title', 'knot_sanitize_title_translit', 9, 3);

/**
 * Same treatment for uploaded file names (new uploads only).
 */
function knot_sanitize_file_name_translit($filename) {
    return is_string($filename) ? knot_transliterate($filename) : $filename;
}

add_filter('sanitize_file_name', 'knot_sanitize_file_name_translit', 9);

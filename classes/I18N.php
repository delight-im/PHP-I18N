<?php

/**
 * Copyright 2014 www.delight.im <info@delight.im>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * I18N (translation and localization) helper that works with PHP 5.3+ and gettext
 *
 * Source: <https://github.com/delight-im/PHP-I18N>
 */
class I18N {

    const SESSION_LANGUAGE_PREFERENCE = 'i18n_language_preference';

    /**
     * Sets up gettext for the given language with the domain and directory provided
     *
     * @param string $domain the gettext domain to use (usually your project name or <messages>)
     * @param string $directory the path to the directory containing the gettext locale data (without trailing slash)
     * @param string $defaultLanguage the default language identifier for gettext to locate translations by
     * @param array|null $mappings (optional) the mappings from Accept-Language values (as regular expressions) to the appropriate gettext language identifiers
     * @throws Exception if the initialization fails
     */
    public static function init($domain, $directory, $defaultLanguage, $mappings = array()) {
        // first check if we have an active session to load/save the language preference
        if (!self::isSessionActive()) {
            throw new Exception('You must call session_start() before you can use the I18N class');
        }

        // get the language from the preference in session or auto-detect it
        if (isset($_SESSION[self::SESSION_LANGUAGE_PREFERENCE])) {
            $language = $_SESSION[self::SESSION_LANGUAGE_PREFERENCE];
        }
        else {
            $language = self::getAutoDetectedLanguage($defaultLanguage, $mappings);
            $_SESSION[self::SESSION_LANGUAGE_PREFERENCE] = $language;
        }

        // set up gettext for the given configuration
        putenv('LANG='.$language.'.utf8');
        setlocale(LC_MESSAGES, $language.'.utf8');
        bindtextdomain($domain, $directory);
        bind_textdomain_codeset($domain, 'UTF-8');
        textdomain($domain);

        // tell all caches that the page content may vary by <Accept-Language> header
        header('Vary: Accept-Language');
    }

    /**
     * Manually sets the language preference to the given identifier provided by the user (e.g. via language selection in drop-down menu)
     *
     * @param string $newLanguageIdentifier the identifier of the new language to use for all pages
     */
    public static function changeLanguage($newLanguageIdentifier) {
        $_SESSION[self::SESSION_LANGUAGE_PREFERENCE] = htmlspecialchars(trim($newLanguageIdentifier));
    }

    private static function getAutoDetectedLanguage($defaultLanguage, $mappings) {
        // get the preferred language from the browser
        $browserLanguage = self::getBrowserLanguage();

        // if a preferred language has been detected
        if (isset($browserLanguage)) {
            // try to find a matching language identifier for the browser language in the given mappings
            foreach ($mappings as $acceptLanguageRegex => $languageIdentifier) {
                if (preg_match($acceptLanguageRegex, $browserLanguage)) {
                    return $languageIdentifier;
                }
            }
        }

        // if no language could be auto-detected from the browser's language with the given mappings
        return $defaultLanguage;
    }

    /**
     * Returns the user's preferred language from the browser
     *
     * @return string|null the preferred language from the browser or NULL
     */
    public static function getBrowserLanguage() {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }
        else {
            return NULL;
        }
    }

    private static function isSessionActive() {
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            return session_status() === PHP_SESSION_ACTIVE;
        }
        else {
            return session_id() !== '';
        }
    }

}

/**
 * Use two underscores (_) instead of one to add placeholders to the gettext function (syntactic sugar)
 *
 * Use the first argument just like with gettext's standard function but add any number of placeholders inside
 *
 * Insert placeholders for strings (%s), for integers (%d) or for floats (%f) and pass the replacements after that
 *
 * If there is more than one placeholder/replacement, always number the placeholders with %1$s, %2$s, %3$d, %4$d, etc.
 *
 * Example #1: echo __('There are %d monkeys in the tree', 5);
 *
 * Example #2: echo __('There are %1$d monkeys in the %2$s', 5, _('garden'));
 *
 * More info: http://www.php.net/manual/en/function.sprintf.php
 */
function __($str, $r1, $r2 = NULL, $r3 = NULL, $r4 = NULL, $r5 = NULL, $r6 = NULL, $r7 = NULL, $r8 = NULL) {
    return sprintf(_($str), $r1, $r2, $r3, $r4, $r5, $r6, $r7, $r8);
}

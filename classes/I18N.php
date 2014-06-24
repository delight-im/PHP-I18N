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

    const DEFAULT_LANGUAGE = 'en_US';

    /**
     * Sets up gettext for the given language with the domain and directory provided
     *
     * @param string $language the language (<ll_CC>, e.g. en_US or de_DE) to use
     * @param string $domain the gettext domain to use (usually your project name or <messages>)
     * @param string $directory the path to the directory containing the gettext locale data (without trailing slash)
     */
    public static function init($language, $domain, $directory) {
        // sanitize the language name (which may be user input)
        $language = self::clean($language);

        // set up gettext for the given configuration
        putenv('LANG='.$language.'.utf8');
        setlocale(LC_MESSAGES, $language);
        bindtextdomain($domain, $directory);
        bind_textdomain_codeset($domain, 'UTF-8');
        textdomain($domain);

        // tell all caches that the page content may vary with different <Accept-Language> headers
        header('Vary: Accept-Language');
    }

    /**
     * Returns the client's preferred language
     *
     * @return string the client's preferred language
     */
    public static function getClientLanguage() {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $locale = locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if (empty($locale)) {
                return self::DEFAULT_LANGUAGE;
            }
            else {
                return $locale;
            }
        }
        else {
            return self::DEFAULT_LANGUAGE;
        }
    }

    private static function clean($text) {
        return preg_replace('/[^a-zA-Z_]/', '', $text);
    }

} 
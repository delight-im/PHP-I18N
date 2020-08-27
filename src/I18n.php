<?php

/*
 * PHP-I18N (https://github.com/delight-im/PHP-I18N)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace Delight\I18n;

use Delight\I18n\Throwable\EmptyLocaleListError;
use Delight\I18n\Throwable\FormattingError;
use Delight\I18n\Throwable\LocaleNotInstalledError;
use Delight\I18n\Throwable\LocaleNotSupportedException;
use Delight\I18n\Throwable\PathNotFoundError;

/** Internationalization and localization */
final class I18n {

	/** @var string the default module of the application to load translations for */
	const MODULE_DEFAULT = 'messages';

	/** @var string[] the codes of the supported locales as constants from {@see Codes} */
	private $supportedLocales;
	/** @var string|null the directory to load translations from */
	private $directory;
	/** @var string|null the module of the application to load translations for */
	private $module;
	/** @var string|null the name of the locale actually being used */
	private $locale;
	/** @var string|null the name of the locale actually being used as it is known to the operating system */
	private $systemLocale;
	/** @var string|null the field in the session to use for retrieving and storing the preferred locale */
	private $sessionField;
	/** @var string|null the name of the cookie to use for retrieving and storing the preferred locale */
	private $cookieName;
	/** @var int|null the lifetime (in seconds) of the cookie to use for retrieving and storing the preferred locale */
	private $cookieLifetime;

	/**
	 * Attempts to set the locale automatically
	 *
	 * @param int|null $leniency (optional) the desired leniency for the lookup as one of the constants from {@see Leniency}
	 */
	public function setLocaleAutomatically($leniency = null) {
		$host = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : (!empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null);

		if (!empty($host)) {
			$subdomain = \explode('.', $host, 2)[0];

			if (!empty($subdomain)) {
				$subdomainMatches = Http::matchPreferredLocales([ $subdomain ], $this->supportedLocales, $leniency);

				if (!empty($subdomainMatches)) {
					try {
						$this->setLocaleManually($subdomainMatches);

						return;
					}
					catch (LocaleNotSupportedException $ignored) {}
				}
			}
		}

		$path = !empty($_SERVER['REQUEST_URI']) ? \parse_url($_SERVER['REQUEST_URI'], \PHP_URL_PATH) : null;

		if (!empty($path)) {
			$pathPrefix = \explode('/', \trim($path, '/'), 2)[0];

			if (!empty($pathPrefix)) {
				$pathPrefixMatches = Http::matchPreferredLocales([ $pathPrefix ], $this->supportedLocales, $leniency);

				if (!empty($pathPrefixMatches)) {
					try {
						$this->setLocaleManually($pathPrefixMatches);

						return;
					}
					catch (LocaleNotSupportedException $ignored) {}
				}
			}
		}

		$queryString = !empty($_GET['locale']) ? $_GET['locale'] : (!empty($_GET['language']) ? $_GET['language'] : (!empty($_GET['lang']) ? $_GET['lang'] : (!empty($_GET['lc']) ? $_GET['lc'] : null)));

		if (!empty($queryString)) {
			try {
				$this->setLocaleManually($queryString);

				return;
			}
			catch (LocaleNotSupportedException $ignored) {}
		}

		if (!empty($this->sessionField)) {
			if (!empty($_SESSION[$this->sessionField])) {
				try {
					$this->setLocaleManually($_SESSION[$this->sessionField]);

					return;
				}
				catch (LocaleNotSupportedException $ignored) {}
			}
		}

		if (!empty($this->cookieName)) {
			if (!\headers_sent()) {
				\header('Vary: Cookie');
			}

			if (!empty($_COOKIE[$this->cookieName])) {
				try {
					$this->setLocaleManually($_COOKIE[$this->cookieName]);

					return;
				}
				catch (LocaleNotSupportedException $ignored) {}
			}
		}

		if (!\headers_sent()) {
			\header('Vary: Accept-Language');
		}

		$httpClientLanguage = Http::matchClientLanguages($this->supportedLocales, $leniency);

		if ($httpClientLanguage !== null) {
			$this->setLocale($httpClientLanguage);

			return;
		}

		$this->setLocale(
			\reset($this->supportedLocales)
		);
	}

	/**
	 * Attempts to set the locale manually
	 *
	 * @param string $code the locale code as one of the constants from {@see Codes}
	 * @throws LocaleNotSupportedException
	 */
	public function setLocaleManually($code) {
		$code = \str_replace('_', '-', $code);

		foreach ($this->supportedLocales as $supportedLocale) {
			if (\strcasecmp($code, $supportedLocale) === 0) {
				$this->setLocale($supportedLocale);

				if (!empty($this->sessionField)) {
					$_SESSION[$this->sessionField] = $supportedLocale;
				}

				if (!empty($this->cookieName)) {
					if (!\headers_sent()) {
						\setcookie(
							$this->cookieName,
							$supportedLocale,
							!empty($this->cookieLifetime) ? \time() + (int) $this->cookieLifetime : 0,
							'/',
							'',
							false,
							false
						);
					}
				}

				return;
			}
		}

		throw new LocaleNotSupportedException();
	}

	/**
	 * Returns the name of the locale actually being used
	 *
	 * @return string|null
	 */
	public function getLocale() {
		return $this->locale;
	}

	/**
	 * Returns the name of the locale actually being used as it is known to the operating system
	 *
	 * @return string|null
	 */
	public function getSystemLocale() {
		return $this->systemLocale;
	}

	/**
	 * Translates the specified text
	 *
	 * @param string $text
	 * @return string
	 */
	public function translate($text) {
		return \gettext($text);
	}

	/**
	 * Translates the specified text and inserts the given replacements at the designated positions
	 *
	 * This uses the “printf” format string syntax, known from the C language (and also from PHP)
	 *
	 * In order to escape the percent sign (to use it literally), simply double it
	 *
	 * @param string $text
	 * @param array ...$replacements
	 * @return string
	 */
	public function translateFormatted($text, ...$replacements) {
		$translated = \gettext($text);

		return self::format($translated, ...$replacements);
	}

	/**
	 * Translates the specified text and inserts the given replacements at the designated positions
	 *
	 * This uses the ICU “MessageFormat” syntax
	 *
	 * In order to escape curly brackets (to use them literally), wrap them in single quotes
	 *
	 * In order to escape single quotes (to use them literally), simply double them
	 *
	 * If you use single quotes for your string literals in PHP, you also have to escape the inserted single quotes with backslashes
	 *
	 * @param string $text
	 * @param array ...$replacements
	 * @return string
	 */
	public function translateFormattedExtended($text, ...$replacements) {
		$translated = \gettext($text);

		return $this->formatExtended($translated, ...$replacements);
	}

	/**
	 * Translates the specified text with a singular or plural form based on the given number
	 *
	 * @param string $text the text with the singular form (or one generic form)
	 * @param string $alternative the text with the plural form (or an empty string)
	 * @param int $count the number to use for the decision between singular and plural forms
	 * @return string
	 */
	public function translatePlural($text, $alternative, $count) {
		return \ngettext($text, $alternative, $count);
	}

	/**
	 * Translates the specified text with a singular or plural form based on the given number and inserts the given replacements at the designated positions
	 *
	 * This uses the “printf” format string syntax, known from the C language (and also from PHP)
	 *
	 * In order to escape the percent sign (to use it literally), simply double it
	 *
	 * @param string $text the text with the singular form (or one generic form)
	 * @param string $alternative the text with the plural form (or an empty string)
	 * @param int $count the number to use for the decision between singular and plural forms
	 * @param array ...$replacements
	 * @return string
	 */
	public function translatePluralFormatted($text, $alternative, $count, ...$replacements) {
		\array_unshift($replacements, $count);
		$translated = $this->translatePlural($text, $alternative, $count);

		return self::format($translated, ...$replacements);
	}

	/**
	 * Translates the specified text with a singular or plural form based on the given number and inserts the given replacements at the designated positions
	 *
	 * This uses the ICU “MessageFormat” syntax
	 *
	 * In order to escape curly brackets (to use them literally), wrap them in single quotes
	 *
	 * In order to escape single quotes (to use them literally), simply double them
	 *
	 * If you use single quotes for your string literals in PHP, you also have to escape the inserted single quotes with backslashes
	 *
	 * @param string $text the text with the singular form (or one generic form)
	 * @param string $alternative the text with the plural form (or an empty string)
	 * @param int $count the number to use for the decision between singular and plural forms
	 * @param array ...$replacements
	 * @return string
	 */
	public function translatePluralFormattedExtended($text, $alternative, $count, ...$replacements) {
		\array_unshift($replacements, $count);
		$translated = $this->translatePlural($text, $alternative, $count);

		return $this->formatExtended($translated, ...$replacements);
	}

	/**
	 * Translates the specified text based on the given context
	 *
	 * @param string $text
	 * @param string $context the context for this occurrence of the text, e.g. “purchase” or “sorting”
	 * @return string
	 */
	public function translateWithContext($text, $context) {
		$separator = "\x04";
		$input = $context . $separator . $text;
		$output = \gettext($input);

		if ($output !== $input) {
			return $output;
		}
		else {
			return $text;
		}
	}

	/**
	 * Marks the specified text for later translation (no-op)
	 *
	 * This is useful if the text should not be translated immediately but will later be translated from a variable (at the last possible point in time)
	 *
	 * For example, you may want to insert a piece of text into a database and later translate the text from a variable after retrieving it again
	 *
	 * @param string $text
	 * @return string
	 */
	public function markForTranslation($text) {
		return $text;
	}

	public function _($text) {
		return $this->translate($text);
	}

	public function _f($text, ...$replacements) {
		return $this->translateFormatted($text, ...$replacements);
	}

	public function _fe($text, ...$replacements) {
		return $this->translateFormattedExtended($text, ...$replacements);
	}

	public function _p($text, $alternative, $count) {
		return $this->translatePlural($text, $alternative, $count);
	}

	public function _pf($text, $alternative, $count, ...$replacements) {
		return $this->translatePluralFormatted($text, $alternative, $count, ...$replacements);
	}

	public function _pfe($text, $alternative, $count, ...$replacements) {
		return $this->translatePluralFormattedExtended($text, $alternative, $count, ...$replacements);
	}

	public function _c($text, $context) {
		return $this->translateWithContext($text, $context);
	}

	public function _m($text) {
		return $this->markForTranslation($text);
	}

	/**
	 * Returns a human-readable name for the specified locale (formatted in the current locale)
	 *
	 * @param string|null $code (optional) the locale code as one of the constants from {@see Codes}, or `null` for the current locale
	 * @return string|null
	 */
	public function getLocaleName($code = null) {
		return Locale::toName(!empty($code) ? $code : $this->getLocale());
	}

	/**
	 * Returns a human-readable name for the specified locale (formatted in that same locale)
	 *
	 * @param string|null $code (optional) the locale code as one of the constants from {@see Codes}, or `null` for the current locale
	 * @return string|null
	 */
	public function getNativeLocaleName($code = null) {
		return Locale::toNativeName(!empty($code) ? $code : $this->getLocale());
	}

	/**
	 * Returns a human-readable name for the language of the specified locale (formatted in the current locale)
	 *
	 * @param string|null $code (optional) the locale code as one of the constants from {@see Codes}, or `null` for the current locale
	 * @return string|null
	 */
	public function getLanguageName($code = null) {
		return Locale::toLanguageName(!empty($code) ? $code : $this->getLocale());
	}

	/**
	 * Returns a human-readable name for the language of the specified locale (formatted in that same locale)
	 *
	 * @param string|null $code (optional) the locale code as one of the constants from {@see Codes}, or `null` for the current locale
	 * @return string|null
	 */
	public function getNativeLanguageName($code = null) {
		return Locale::toNativeLanguageName(!empty($code) ? $code : $this->getLocale());
	}

	/**
	 * Sets the directory to load translations from
	 *
	 * @param string|null $directory
	 */
	public function setDirectory($directory) {
		if (!empty($directory)) {
			$path = \realpath((string) $directory);

			if (!empty($path)) {
				$this->directory = $path;
			}
			else {
				throw new PathNotFoundError();
			}
		}
		else {
			$this->directory = null;
		}
	}

	/**
	 * Returns the directory to load translations from
	 *
	 * @return string|null
	 */
	public function getDirectory() {
		return $this->directory;
	}

	/**
	 * Sets the module of the application to load translations for
	 *
	 * @param string|null $module
	 */
	public function setModule($module) {
		$this->module = !empty($module) ? (string) $module : null;
	}

	/**
	 * Returns the module of the application to load translations for
	 *
	 * @return string|null
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * @param string[] $supportedLocales the codes of the supported locales as constants from {@see Codes}
	 */
	public function __construct(array $supportedLocales) {
		if (!empty($supportedLocales)) {
			$this->supportedLocales = $supportedLocales;
		}
		else {
			throw new EmptyLocaleListError();
		}
	}

	/**
	 * Returns the list of all supported locales
	 *
	 * @return string[]
	 */
	public function getSupportedLocales() {
		return $this->supportedLocales;
	}

	/**
	 * Sets the field in the session to use for retrieving and storing the preferred locale
	 *
	 * @param string|null $sessionField
	 */
	public function setSessionField($sessionField) {
		$this->sessionField = !empty($sessionField) ? (string) $sessionField : null;
	}

	/**
	 * Returns the field in the session to use for retrieving and storing the preferred locale
	 *
	 * @return string|null
	 */
	public function getSessionField() {
		return $this->sessionField;
	}

	/**
	 * Sets the name of the cookie to use for retrieving and storing the preferred locale
	 *
	 * @param string|null $cookieName
	 */
	public function setCookieName($cookieName) {
		$this->cookieName = !empty($cookieName) ? (string) $cookieName : null;
	}

	/**
	 * Returns the name of the cookie to use for retrieving and storing the preferred locale
	 *
	 * @return string|null
	 */
	public function getCookieName() {
		return $this->cookieName;
	}

	/**
	 * Sets the lifetime (in seconds) of the cookie to use for retrieving and storing the preferred locale
	 *
	 * A value of `null` means that the cookie is to expire at the end of the current browser session
	 *
	 * @param int|null $cookieLifetime
	 */
	public function setCookieLifetime($cookieLifetime) {
		$this->cookieLifetime = !empty($cookieLifetime) ? (int) $cookieLifetime : null;
	}

	/**
	 * Returns the lifetime (in seconds) of the cookie to use for retrieving and storing the preferred locale
	 *
	 * A value of `null` means that the cookie is to expire at the end of the current browser session
	 *
	 * @return int|null
	 */
	public function getCookieLifetime() {
		return $this->cookieLifetime;
	}

	/**
	 * Sets the locale
	 *
	 * @param string $code the locale code as one of the constants from {@see Codes}
	 */
	private function setLocale($code) {
		$systemLocale = \setlocale(
			\defined('LC_MESSAGES') ? \LC_MESSAGES : 5,
			self::createSystemLocaleVariants($code)
		);

		if (!empty($systemLocale)) {
			\setlocale(\LC_NUMERIC, $systemLocale);
			\setlocale(\LC_TIME, $systemLocale);
			\setlocale(\LC_MONETARY, $systemLocale);

			\putenv('LC_MESSAGES=' . $systemLocale);
			\putenv('LC_NUMERIC=' . $systemLocale);
			\putenv('LC_TIME=' . $systemLocale);
			\putenv('LC_MONETARY=' . $systemLocale);

			// \putenv('LANG=' . $systemLocale);

			\bindtextdomain(
				!empty($this->module) ? $this->module : self::MODULE_DEFAULT,
				!empty($this->directory) ? $this->directory : self::makeDefaultDirectory()
			);
			\bind_textdomain_codeset(
				!empty($this->module) ? $this->module : self::MODULE_DEFAULT,
				'UTF-8'
			);
			\textdomain(!empty($this->module) ? $this->module : self::MODULE_DEFAULT);

			$this->locale = $code;
			$this->systemLocale = $systemLocale;
		}
		else {
			throw new LocaleNotInstalledError();
		}
	}

	/**
	 * Creates possible variants of the locale code as it may be known to the operating system
	 *
	 * @param string $code the locale code as one of the constants from {@see Codes}
	 * @return string[]
	 */
	private static function createSystemLocaleVariants($code) {
		$hyphen = \str_replace('_', '-', $code);
		$underscore = \str_replace('-', '_', $hyphen);

		return [
			$hyphen . '.utf8',
			$underscore . '.utf8',
			$hyphen . '.UTF-8',
			$underscore . '.UTF-8',
			$hyphen,
			$underscore
		];
	}

	/**
	 * Inserts the given replacements at the designated positions in the text
	 *
	 * This uses the “printf” format string syntax, known from the C language (and also from PHP)
	 *
	 * In order to escape the percent sign (to use it literally), simply double it
	 *
	 * @param string $text
	 * @param array ...$replacements
	 * @return string
	 */
	private static function format($text, ...$replacements) {
		$formatted = @\sprintf($text, ...$replacements);

		if ($formatted !== false) {
			return $formatted;
		}
		else {
			throw new FormattingError();
		}
	}

	/**
	 * Inserts the given replacements at the designated positions in the text
	 *
	 * This uses the ICU “MessageFormat” syntax
	 *
	 * In order to escape curly brackets (to use them literally), wrap them in single quotes
	 *
	 * In order to escape single quotes (to use them literally), simply double them
	 *
	 * If you use single quotes for your string literals in PHP, you also have to escape the inserted single quotes with backslashes
	 *
	 * @param string $text
	 * @param array ...$replacements
	 * @return string
	 */
	private function formatExtended($text, ...$replacements) {
		$formatted = \MessageFormatter::formatMessage($this->locale, $text, $replacements);

		if ($formatted !== false) {
			return $formatted;
		}
		else {
			throw new FormattingError();
		}
	}

	/**
	 * Determines the default directory to load translations from
	 *
	 * @return string
	 */
	private static function makeDefaultDirectory() {
		return __DIR__ . '/../../../../locale';
	}

}

<?php

/*
 * PHP-I18N (https://github.com/delight-im/PHP-I18N)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace Delight\I18n;

/** Sets of languages, scripts and regions */
final class Locale {

	/**
	 * Returns a human-readable name for the locale (formatted in the current locale)
	 *
	 * @param string $code
	 * @return string|null
	 */
	public static function toName($code) {
		return self::toNameInternal($code, \Locale::getDefault());
	}

	/**
	 * Returns a human-readable name for the locale (formatted in that same locale)
	 *
	 * @param string $code
	 * @return string|null
	 */
	public static function toNativeName($code) {
		return self::toNameInternal($code, $code);
	}

	/**
	 * Returns a human-readable name for the locale (formatted in English)
	 *
	 * @param string $code
	 * @return string|null
	 */
	public static function toEnglishName($code) {
		return self::toNameInternal($code, 'en');
	}

	/**
	 * Returns a human-readable name for the language of the locale (formatted in the current locale)
	 *
	 * @param string $code
	 * @return string|null
	 */
	public static function toLanguageName($code) {
		return self::toLanguageNameInternal($code, \Locale::getDefault());
	}

	/**
	 * Returns a human-readable name for the language of the locale (formatted in that same locale)
	 *
	 * @param string $code
	 * @return string|null
	 */
	public static function toNativeLanguageName($code) {
		return self::toLanguageNameInternal($code, $code);
	}

	/**
	 * Returns a human-readable name for the language of the locale (formatted in English)
	 *
	 * @param string $code
	 * @return string|null
	 */
	public static function toEnglishLanguageName($code) {
		return self::toLanguageNameInternal($code, 'en');
	}

	/**
	 * Returns a human-readable name for the script of the locale (formatted in the current locale)
	 *
	 * @param string $code
	 * @return string|null
	 */
	public static function toScriptName($code) {
		return self::toScriptNameInternal($code, \Locale::getDefault());
	}

	/**
	 * Returns a human-readable name for the script of the locale (formatted in that same locale)
	 *
	 * @param string $code
	 * @return string|null
	 */
	public static function toNativeScriptName($code) {
		return self::toScriptNameInternal($code, $code);
	}

	/**
	 * Returns a human-readable name for the script of the locale (formatted in English)
	 *
	 * @param string $code
	 * @return string|null
	 */
	public static function toEnglishScriptName($code) {
		return self::toScriptNameInternal($code, 'en');
	}

	/**
	 * Returns a human-readable name for the region of the locale (formatted in the current locale)
	 *
	 * @param string $code
	 * @return string|null
	 */
	public static function toRegionName($code) {
		return self::toRegionNameInternal($code, \Locale::getDefault());
	}

	/**
	 * Returns a human-readable name for the region of the locale (formatted in that same locale)
	 *
	 * @param string $code
	 * @return string|null
	 */
	public static function toNativeRegionName($code) {
		return self::toRegionNameInternal($code, $code);
	}

	/**
	 * Returns a human-readable name for the region of the locale (formatted in English)
	 *
	 * @param string $code
	 * @return string|null
	 */
	public static function toEnglishRegionName($code) {
		return self::toRegionNameInternal($code, 'en');
	}

	/**
	 * Returns the machine-readable language code of the locale as per ISO 639
	 *
	 * @param string $code
	 * @return string|null
	 */
	public static function toLanguageCode($code) {
		$languageCode = \Locale::getPrimaryLanguage($code);

		return !empty($languageCode) ? $languageCode : null;
	}

	/**
	 * Returns the machine-readable script code of the locale as per ISO 15924
	 *
	 * @param string $code
	 * @return string|null
	 */
	public static function toScriptCode($code) {
		$scriptCode = \Locale::getScript($code);

		return !empty($scriptCode) ? $scriptCode : null;
	}

	/**
	 * Returns the machine-readable region code of the locale as per ISO 3166-1
	 *
	 * @param string $code
	 * @return string|null
	 */
	public static function toRegionCode($code) {
		$regionCode = \Locale::getRegion($code);

		return !empty($regionCode) ? $regionCode : null;
	}

	/**
	 * Returns whether the locale has a script of right-to-left (RTL) directionality
	 *
	 * @param string $code the code of the locale to check
	 * @return bool
	 */
	public static function isRtl($code) {
		return \in_array(
			self::toLanguageCode($code),
			[ 'ar', 'dv', 'ha', 'he', 'fa', 'ps', 'ur', 'yi' ],
			true
		);
	}

	/**
	 * Returns whether the locale has a script of left-to-right (LTR) directionality
	 *
	 * @param string $code the code of the locale to check
	 * @return bool
	 */
	public static function isLtr($code) {
		return !self::isRtl($code);
	}

	/**
	 * Returns a human-readable name for the locale
	 *
	 * @param string $subjectCode the code of the locale to return the name for
	 * @param string|null $formatCode the code of the locale to format the name in
	 * @return string|null
	 */
	private static function toNameInternal($subjectCode, $formatCode = null) {
		$name = \Locale::getDisplayName($subjectCode, $formatCode);

		return !empty($name) ? $name : null;
	}

	/**
	 * Returns a human-readable name for the language of the locale
	 *
	 * @param string $subjectCode the code of the locale to return the name of the language for
	 * @param string|null $formatCode the code of the locale to format the name of the language in
	 * @return string|null
	 */
	private static function toLanguageNameInternal($subjectCode, $formatCode = null) {
		$languageName = \Locale::getDisplayLanguage($subjectCode, $formatCode);

		return !empty($languageName) ? $languageName : null;
	}

	/**
	 * Returns a human-readable name for the script of the locale
	 *
	 * @param string $subjectCode the code of the locale to return the name of the script for
	 * @param string|null $formatCode the code of the locale to format the name of the script in
	 * @return string|null
	 */
	private static function toScriptNameInternal($subjectCode, $formatCode = null) {
		$scriptName = \Locale::getDisplayScript($subjectCode, $formatCode);

		return !empty($scriptName) ? $scriptName : null;
	}

	/**
	 * Returns a human-readable name for the region of the locale
	 *
	 * @param string $subjectCode the code of the locale to return the name of the region for
	 * @param string|null $formatCode the code of the locale to format the name of the region in
	 * @return string|null
	 */
	private static function toRegionNameInternal($subjectCode, $formatCode = null) {
		$regionName = \Locale::getDisplayRegion($subjectCode, $formatCode);

		return !empty($regionName) ? $regionName : null;
	}

	private function __construct() {}

}

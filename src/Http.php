<?php

/*
 * PHP-I18N (https://github.com/delight-im/PHP-I18N)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace Delight\I18n;

/** Handles aspects of the HTTP protocol related to locales */
final class Http {

	/** @var string a regular expression matching significant HTTP “language ranges” or “language tags” */
	const LANGUAGE_RANGE_OR_TAG_REGEX = '/^([a-z]{2,3})(?:-([a-z]{4}))?(?:-([a-z]{2}|[0-9]{3}))?$/i';

	/**
	 * Finds the best match for the languages accepted by the HTTP client in the list of supported languages
	 *
	 * @param string[] $supportedLanguages
	 * @param int|null $leniency (optional) the desired leniency for the lookup as one of the constants from {@see Leniency}
	 * @param string|null $httpAcceptLanguage (optional) the value of the HTTP `Accept-Language` request header
	 * @return string|null
	 */
	public static function matchClientLanguages(array $supportedLanguages, $leniency = null, $httpAcceptLanguage = null) {
		$leniency = !empty($leniency) ? (int) $leniency : Leniency::MODERATE;

		foreach (self::determineClientLanguages($httpAcceptLanguage) as $httpClientLanguage) {
			$desired = self::parseClientLanguage($httpClientLanguage);
			$bestMatchCode = null;
			$bestMatchAffinity = null;

			foreach ($supportedLanguages as $supported) {
				$affinity = Affinity::calculate(
					$desired[0],
					$desired[1],
					$desired[2],
					Locale::toLanguageCode($supported),
					Locale::toScriptCode($supported),
					Locale::toRegionCode($supported)
				);

				if (empty($bestMatchCode) || $affinity > $bestMatchAffinity) {
					$bestMatchCode = $supported;
					$bestMatchAffinity = $affinity;
				}
			}

			if ($bestMatchAffinity >= $leniency) {
				return $bestMatchCode;
			}
		}

		return null;
	}

	/**
	 * Returns the list of languages preferred by the HTTP client in the order of descending priority
	 *
	 * @param string|null $httpAcceptLanguage (optional) the value of the HTTP `Accept-Language` request header
	 * @return string[]
	 */
	public static function determineClientLanguages($httpAcceptLanguage = null) {
		$httpAcceptLanguage = !empty($httpAcceptLanguage) ? (string) $httpAcceptLanguage : (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null);

		if (!empty($httpAcceptLanguage)) {
			$httpAcceptLanguage = preg_replace('/\s+/', '', $httpAcceptLanguage);
			$entries = \explode(',', $httpAcceptLanguage);

			$entries = \array_map(function ($each) {
				$each = \explode(';q=', $each, 2);

				if (isset($each[1])) {
					$each[1] = (float) $each[1];

					if ($each[1] > 1) {
						$each[1] = 1;
					}
					elseif ($each[1] < 0) {
						$each[1] = 0;
					}
				}
				else {
					$each[1] = 1;
				}

				return $each;
			}, $entries);

			\usort($entries, function ($a, $b) {
				return ($b[1] * 1000) - ($a[1] * 1000);
			});

			$entries = \array_map(function ($each) {
				return $each[0];
			}, $entries);

			return $entries;
		}
		else {
			return [];
		}
	}

	/**
	 * Parses a language provided by the HTTP client and returns its components
	 *
	 * @param string $language a “language range” or “language tag”
	 * @return string[] the language, script and region “subtags”
	 */
	public static function parseClientLanguage($language) {
		if (\preg_match(self::LANGUAGE_RANGE_OR_TAG_REGEX, $language, $matches)) {
			\array_shift($matches);
		}
		else {
			$matches = [];
		}

		if (empty($matches[0])) {
			$matches[0] = null;
		}

		if (empty($matches[1])) {
			$matches[1] = null;
		}

		if (empty($matches[2])) {
			$matches[2] = null;
		}

		return $matches;
	}

	private function __construct() {}

}

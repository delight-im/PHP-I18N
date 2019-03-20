<?php

/*
 * PHP-I18N (https://github.com/delight-im/PHP-I18N)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace Delight\I18n;

/** Degree of resemblance between two locales */
final class Affinity {

	/** @var int full resemblance between two locales */
	const FULL = 11010;
	/** @var int extremely high resemblance between two locales */
	const EXTREMELY_HIGH = 11001;
	/** @var int very high resemblance between two locales */
	const VERY_HIGH = 11000;
	/** @var int high resemblance between two locales */
	const HIGH = 10110;
	/** @var int moderate resemblance between two locales */
	const MODERATE = 10101;
	/** @var int low resemblance between two locales */
	const LOW = 10100;
	/** @var int very low resemblance between two locales */
	const VERY_LOW = 10010;
	/** @var int extremely low resemblance between two locales */
	const EXTREMELY_LOW = 10001;
	/** @var int no resemblance between two locales */
	const NONE = 10000;

	/**
	 * Calculates the affinity between two locales
	 *
	 * @param string $firstLanguage
	 * @param string $firstScript
	 * @param string $firstRegion
	 * @param string $secondLanguage
	 * @param string $secondScript
	 * @param string $secondRegion
	 * @return int
	 */
	public static function calculate($firstLanguage, $firstScript, $firstRegion, $secondLanguage, $secondScript, $secondRegion) {
		$affinity = 0;

		if (\strcasecmp($firstLanguage, $secondLanguage) === 0) {
			$affinity += 10000;
		}

		if (\strcasecmp($firstScript, $secondScript) === 0) {
			$affinity += 1000;
		}
		elseif (empty($firstScript) !== empty($secondScript)) {
			$affinity += 100;
		}

		if (\strcasecmp($firstRegion, $secondRegion) === 0) {
			$affinity += 10;
		}
		elseif (empty($firstRegion) !== empty($secondRegion)) {
			$affinity += 1;
		}

		return $affinity;
	}

	private function __construct() {}

}

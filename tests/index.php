<?php

/*
 * PHP-I18N (https://github.com/delight-im/PHP-I18N)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

/*
 * WARNING:
 *
 * Do *not* use these files from the `tests` directory as the foundation
 * for the usage of this library in your own code. Instead, please follow
 * the `README.md` file in the root directory of this project.
 */

// enable error reporting
\error_reporting(\E_ALL);
\ini_set('display_errors', 'stdout');

\header('Content-type: text/plain; charset=utf-8');

require __DIR__.'/../vendor/autoload.php';

// BEGIN ALIASES

function _f($text, ...$replacements) { global $i18n; return $i18n->translateFormatted($text, ...$replacements); }

function _fe($text, ...$replacements) { global $i18n; return $i18n->translateFormattedExtended($text, ...$replacements); }

function _p($text, $alternative, $count) { global $i18n; return $i18n->translatePlural($text, $alternative, $count); }

function _pf($text, $alternative, $count, ...$replacements) { global $i18n; return $i18n->translatePluralFormatted($text, $alternative, $count, ...$replacements); }

function _pfe($text, $alternative, $count, ...$replacements) { global $i18n; return $i18n->translatePluralFormattedExtended($text, $alternative, $count, ...$replacements); }

function _c($text, $context) { global $i18n; return $i18n->translateWithContext($text, $context); }

function _m($text) { global $i18n; return $i18n->markForTranslation($text); }

// END ALIASES

// BEGIN TESTS

try {
	$i18n = new \Delight\I18n\I18n([]);
}
catch (\Delight\I18n\Throwable\EmptyLocaleListError $e) {
	$i18n = null;
}
($i18n === null) or \fail(__LINE__);


$i18n = new \Delight\I18n\I18n([
	\Delight\I18n\Codes::EN_US,
	\Delight\I18n\Codes::DA_DK,
	\Delight\I18n\Codes::ES_AR,
	\Delight\I18n\Codes::ES,
	\Delight\I18n\Codes::KO_KR,
	\Delight\I18n\Codes::KO,
	\Delight\I18n\Codes::SW,
	\Delight\I18n\Codes::RU_RU
]);
($i18n instanceof \Delight\I18n\I18n) or \fail(__LINE__);


try {
	$i18n->setDirectory(__DIR__ . '/../language');
	$invalidPath = false;
}
catch (\Delight\I18n\Throwable\PathNotFoundError $e) {
	$invalidPath = true;
}
($invalidPath === true) or \fail(__LINE__);


$i18n->setDirectory(__DIR__ . '/../locale');
($i18n->getDirectory() === \realpath(__DIR__ . '/../locale')) or \fail(__LINE__);


($i18n->getModule() === null) or \fail(__LINE__);
$i18n->setModule('messages');
($i18n->getModule() === 'messages') or \fail(__LINE__);
$i18n->setModule(null);
($i18n->getModule() === null) or \fail(__LINE__);


($i18n->getSessionField() === null) or \fail(__LINE__);
$i18n->setSessionField('locale');
($i18n->getSessionField() === 'locale') or \fail(__LINE__);
$i18n->setSessionField(null);
($i18n->getSessionField() === null) or \fail(__LINE__);


($i18n->getCookieName() === null) or \fail(__LINE__);
$i18n->setCookieName('lc');
($i18n->getCookieName() === 'lc') or \fail(__LINE__);
$i18n->setCookieName(null);
($i18n->getCookieName() === null) or \fail(__LINE__);


($i18n->getCookieLifetime() === null) or \fail(__LINE__);
$i18n->setCookieLifetime(60 * 60 * 24);
($i18n->getCookieLifetime() === (60 * 60 * 24)) or \fail(__LINE__);
$i18n->setCookieLifetime(null);
($i18n->getCookieLifetime() === null) or \fail(__LINE__);


try {
	$i18n->setLocaleManually('eS_Mx');
	$invalidLocale = false;
}
catch (\Delight\I18n\Throwable\LocaleNotSupportedException $e) {
	$invalidLocale = true;
}
($invalidLocale === true) or \fail(__LINE__);
($i18n->getLocale() === null) or \fail(__LINE__);


try {
	$i18n->setLocaleManually('eS_Ar');
	$invalidLocale = false;
}
catch (\Delight\I18n\Throwable\LocaleNotSupportedException $e) {
	$invalidLocale = true;
}
($invalidLocale === false) or \fail(__LINE__);
($i18n->getLocale() === \Delight\I18n\Codes::ES_AR) or \fail(__LINE__);


$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'pt-BR, pt;q=0.8, en;q=0.4, jp;q=0.6, *;q=0.2';
$i18n->setLocaleAutomatically();
($i18n->getLocale() === \Delight\I18n\Codes::EN_US) or \fail(__LINE__);


echo 'Locale:' . "\t\t\t" . $i18n->getLocale() . "\n";
echo 'System locale:' . "\t\t" . $i18n->getSystemLocale() . "\n";


(\strftime("%A %e %B %Y", -14182916) === 'Sunday 20 July 1969') or \fail(__LINE__);


(_('Welcome to our online store!') === 'Welcome to our online store!') or \fail(__LINE__);
(_('You have been successfully logged out.') === 'You have been successfully logged out.') or \fail(__LINE__);


(_f('Hello %s!', 'Jane') === 'Hello Jane!') or \fail(__LINE__);
(_f('%1$s is %2$d years old.', 'John', 30) === 'John is 30 years old.') or \fail(__LINE__);
(_f('%1$d %% of the market is controlled by %2$s.', 65, 'Acme Corporation') === '65 % of the market is controlled by Acme Corporation.') or \fail(__LINE__);
(_f('Languages like %s use the curly brackets { and } to denote blocks.', 'C') === 'Languages like C use the curly brackets { and } to denote blocks.') or \fail(__LINE__);


$filename1 = 'example.csv';
$filename2 = 'example.json';


$rw = true;
(_f('File %1$s has %2$s protection', $filename1, ($rw ? _('write') : _('read'))) === 'File example.csv has write protection') or \fail(__LINE__);
(($rw ? _f('File %1$s has write protection', $filename1) : _f('File %1$s has read protection', $filename1)) === 'File example.csv has write protection') or \fail(__LINE__);
$rw = false;
(_f('File %1$s has %2$s protection', $filename1, ($rw ? _('write') : _('read'))) === 'File example.csv has read protection') or \fail(__LINE__);
(($rw ? _f('File %1$s has write protection', $filename1) : _f('File %1$s has read protection', $filename1)) === 'File example.csv has read protection') or \fail(__LINE__);


((_('Replace ') . $filename1 . _(' with ') . $filename2) === 'Replace example.csv with example.json') or \fail(__LINE__);
(_f('Replace %1$s with %2$s', $filename1, $filename2) === 'Replace example.csv with example.json') or \fail(__LINE__);


(_fe('Hello {0}!', 'Jane') === 'Hello Jane!') or \fail(__LINE__);
(_fe('{0} is {1, number} years old.', 'John', 30) === 'John is 30 years old.') or \fail(__LINE__);
(_fe('{0, number} % of the market is controlled by {1}.', 65, 'Acme Corporation') === '65 % of the market is controlled by Acme Corporation.') or \fail(__LINE__);
(_fe('Languages like {0} use the curly brackets \'{\' and \'}\' to denote blocks.', 'C') === 'Languages like C use the curly brackets { and } to denote blocks.') or \fail(__LINE__);


$rw = true;
(_fe('File {0} has {1} protection', $filename1, ($rw ? _('write') : _('read'))) === 'File example.csv has write protection') or \fail(__LINE__);
(($rw ? _fe('File {0} has write protection', $filename1) : _fe('File {0} has read protection', $filename1)) === 'File example.csv has write protection') or \fail(__LINE__);
$rw = false;
(_fe('File {0} has {1} protection', $filename1, ($rw ? _('write') : _('read'))) === 'File example.csv has read protection') or \fail(__LINE__);
(($rw ? _fe('File {0} has write protection', $filename1) : _fe('File {0} has read protection', $filename1)) === 'File example.csv has read protection') or \fail(__LINE__);


((_('Replace ') . $filename1 . _(' with ') . $filename2) === 'Replace example.csv with example.json') or \fail(__LINE__);
(_fe('Replace {0} with {1}', $filename1, $filename2) === 'Replace example.csv with example.json') or \fail(__LINE__);


try {
	_f('Hello %s!');
	$formattingError = false;
}
catch (\Delight\I18n\Throwable\FormattingError $e) {
	$formattingError = true;
}
($formattingError === true) or \fail(__LINE__);


try {
	_f('Hello %s!', 'Jane');
	$formattingError = false;
}
catch (\Delight\I18n\Throwable\FormattingError $e) {
	$formattingError = true;
}
($formattingError === false) or \fail(__LINE__);


try {
	_f('Hello %s!', 'Jane', 'John');
	$formattingError = false;
}
catch (\Delight\I18n\Throwable\FormattingError $e) {
	$formattingError = true;
}
($formattingError === false) or \fail(__LINE__);


try {
	_fe('Hello {0}!');
	$formattingError = false;
}
catch (\Delight\I18n\Throwable\FormattingError $e) {
	$formattingError = true;
}
($formattingError === false) or \fail(__LINE__);


try {
	_fe('Hello {0}!', 'Jane');
	$formattingError = false;
}
catch (\Delight\I18n\Throwable\FormattingError $e) {
	$formattingError = true;
}
($formattingError === false) or \fail(__LINE__);


try {
	_fe('Hello {0}!', 'Jane', 'John');
	$formattingError = false;
}
catch (\Delight\I18n\Throwable\FormattingError $e) {
	$formattingError = true;
}
($formattingError === false) or \fail(__LINE__);


(_p('The file has been saved.', 'The files have been saved.', 1) === 'The file has been saved.') or \fail(__LINE__);
(_p('The file has been saved.', 'The files have been saved.', 2) === 'The files have been saved.') or \fail(__LINE__);
(_p('The file has been saved.', 'The files have been saved.', 3) === 'The files have been saved.') or \fail(__LINE__);


(_pf('There is %d monkey.', 'There are %d monkeys.', 0) === 'There are 0 monkeys.') or \fail(__LINE__);
(_pf('There is %d monkey.', 'There are %d monkeys.', 1) === 'There is 1 monkey.') or \fail(__LINE__);
(_pf('There is %d monkey.', 'There are %d monkeys.', 2) === 'There are 2 monkeys.') or \fail(__LINE__);
(_pf('There is %1$d monkey in %2$s.', 'There are %1$d monkeys in %2$s.', 3, 'Anytown') === 'There are 3 monkeys in Anytown.') or \fail(__LINE__);


(_pf('You have %d new message', 'You have %d new messages', 1) === 'You have 1 new message') or \fail(__LINE__);
(_pf('You have %d new message', 'You have %d new messages', 32) === 'You have 32 new messages') or \fail(__LINE__);


(_pfe('There is {0, number} monkey.', 'There are {0, number} monkeys.', 0) === 'There are 0 monkeys.') or \fail(__LINE__);
(_pfe('There is {0, number} monkey.', 'There are {0, number} monkeys.', 1) === 'There is 1 monkey.') or \fail(__LINE__);
(_pfe('There is {0, number} monkey.', 'There are {0, number} monkeys.', 2) === 'There are 2 monkeys.') or \fail(__LINE__);
(_pfe('There is {0, number} monkey in {1}.', 'There are {0, number} monkeys in {1}.', 3, 'Anytown') === 'There are 3 monkeys in Anytown.') or \fail(__LINE__);


(_pfe('You have {0, number} new message', 'You have {0, number} new messages', 1) === 'You have 1 new message') or \fail(__LINE__);
(_pfe('You have {0, number} new message', 'You have {0, number} new messages', 32) === 'You have 32 new messages') or \fail(__LINE__);


(_c('Order', 'sorting') === 'Order') or \fail(__LINE__);
(_c('Order', 'purchase') === 'Order') or \fail(__LINE__);
(_c('Order', 'mathematics') === 'Order') or \fail(__LINE__);
(_c('Order', 'classification') === 'Order') or \fail(__LINE__);


(_c('Address:', 'location') === 'Address:') or \fail(__LINE__);
(_c('Address:', 'www') === 'Address:') or \fail(__LINE__);
(_c('Address:', 'email') === 'Address:') or \fail(__LINE__);
(_c('Address:', 'letter') === 'Address:') or \fail(__LINE__);
(_c('Address:', 'speech') === 'Address:') or \fail(__LINE__);


(_m('User') === 'User') or \fail(__LINE__);
$text = 'User';
(_($text) === 'User') or \fail(__LINE__);
(_m('Moderator') === 'Moderator') or \fail(__LINE__);
$text = 'Moderator';
(_($text) === 'Moderator') or \fail(__LINE__);
(_m('Administrator') === 'Administrator') or \fail(__LINE__);
$text = 'Administrator';
(_($text) === 'Administrator') or \fail(__LINE__);


($i18n->getLocaleName() === 'English (United States)') or \fail(__LINE__);
($i18n->getLocaleName('fr-BE') === 'French (Belgium)') or \fail(__LINE__);
($i18n->getNativeLocaleName() === 'English (United States)') or \fail(__LINE__);
($i18n->getNativeLocaleName('fr-BE') === 'français (Belgique)') or \fail(__LINE__);
($i18n->getLanguageName() === 'English') or \fail(__LINE__);
($i18n->getLanguageName('fr-BE') === 'French') or \fail(__LINE__);
($i18n->getNativeLanguageName() === 'English') or \fail(__LINE__);
($i18n->getNativeLanguageName('fr-BE') === 'français') or \fail(__LINE__);


(\Delight\I18n\Locale::toName('nb-NO') === 'Norwegian Bokmål (Norway)') or \fail(__LINE__);
(\Delight\I18n\Locale::toName('ru-UA') === 'Russian (Ukraine)') or \fail(__LINE__);
(\Delight\I18n\Locale::toNativeName('nb-NO') === 'norsk bokmål (Norge)') or \fail(__LINE__);
(\Delight\I18n\Locale::toNativeName('ru-UA') === 'русский (Украина)') or \fail(__LINE__);
(\Delight\I18n\Locale::toEnglishName('nb-NO') === 'Norwegian Bokmål (Norway)') or \fail(__LINE__);
(\Delight\I18n\Locale::toEnglishName('ru-UA') === 'Russian (Ukraine)') or \fail(__LINE__);
(\Delight\I18n\Locale::toLanguageName('nb-NO') === 'Norwegian Bokmål') or \fail(__LINE__);
(\Delight\I18n\Locale::toLanguageName('ru-UA') === 'Russian') or \fail(__LINE__);
(\Delight\I18n\Locale::toNativeLanguageName('nb-NO') === 'norsk bokmål') or \fail(__LINE__);
(\Delight\I18n\Locale::toNativeLanguageName('ru-UA') === 'русский') or \fail(__LINE__);
(\Delight\I18n\Locale::toEnglishLanguageName('nb-NO') === 'Norwegian Bokmål') or \fail(__LINE__);
(\Delight\I18n\Locale::toEnglishLanguageName('ru-UA') === 'Russian') or \fail(__LINE__);
(\Delight\I18n\Locale::toScriptName('nb-Latn-NO') === 'Latin') or \fail(__LINE__);
(\Delight\I18n\Locale::toScriptName('ru-Cyrl-UA') === 'Cyrillic') or \fail(__LINE__);
(\Delight\I18n\Locale::toNativeScriptName('nb-Latn-NO') === 'latinsk') or \fail(__LINE__);
(\Delight\I18n\Locale::toNativeScriptName('ru-Cyrl-UA') === 'кириллица') or \fail(__LINE__);
(\Delight\I18n\Locale::toEnglishScriptName('nb-Latn-NO') === 'Latin') or \fail(__LINE__);
(\Delight\I18n\Locale::toEnglishScriptName('ru-Cyrl-UA') === 'Cyrillic') or \fail(__LINE__);
(\Delight\I18n\Locale::toRegionName('nb-NO') === 'Norway') or \fail(__LINE__);
(\Delight\I18n\Locale::toRegionName('ru-UA') === 'Ukraine') or \fail(__LINE__);
(\Delight\I18n\Locale::toNativeRegionName('nb-NO') === 'Norge') or \fail(__LINE__);
(\Delight\I18n\Locale::toNativeRegionName('ru-UA') === 'Украина') or \fail(__LINE__);
(\Delight\I18n\Locale::toEnglishRegionName('nb-NO') === 'Norway') or \fail(__LINE__);
(\Delight\I18n\Locale::toEnglishRegionName('ru-UA') === 'Ukraine') or \fail(__LINE__);
(\Delight\I18n\Locale::toLanguageCode('nb-Latn-NO') === 'nb') or \fail(__LINE__);
(\Delight\I18n\Locale::toLanguageCode('ru-Cyrl-UA') === 'ru') or \fail(__LINE__);
(\Delight\I18n\Locale::toScriptCode('nb-Latn-NO') === 'Latn') or \fail(__LINE__);
(\Delight\I18n\Locale::toScriptCode('ru-Cyrl-UA') === 'Cyrl') or \fail(__LINE__);
(\Delight\I18n\Locale::toRegionCode('nb-Latn-NO') === 'NO') or \fail(__LINE__);
(\Delight\I18n\Locale::toRegionCode('ru-Cyrl-UA') === 'UA') or \fail(__LINE__);
(\Delight\I18n\Locale::isRtl('ln-CD') === false) or \fail(__LINE__);
(\Delight\I18n\Locale::isRtl('ur-PK') === true) or \fail(__LINE__);
(\Delight\I18n\Locale::isLtr('ln-CD') === true) or \fail(__LINE__);
(\Delight\I18n\Locale::isLtr('ur-PK') === false) or \fail(__LINE__);


(\Delight\I18n\Http::parseClientLanguage('hello') == [ null, null, null ]) or \fail(__LINE__);
(\Delight\I18n\Http::parseClientLanguage('am') === [ 'am', null, null ]) or \fail(__LINE__);
(\Delight\I18n\Http::parseClientLanguage('amha') === [ null, null, null ]) or \fail(__LINE__);
(\Delight\I18n\Http::parseClientLanguage('am-ET') === [ 'am', null, 'ET' ]) or \fail(__LINE__);
(\Delight\I18n\Http::parseClientLanguage('am-ETH') === [ null, null, null ]) or \fail(__LINE__);
(\Delight\I18n\Http::parseClientLanguage('am-Ethi') === [ 'am', 'Ethi', null ]) or \fail(__LINE__);
(\Delight\I18n\Http::parseClientLanguage('am-Ethio') === [ null, null, null ]) or \fail(__LINE__);
(\Delight\I18n\Http::parseClientLanguage('am-Ethi-ET') === [ 'am', 'Ethi', 'ET' ]) or \fail(__LINE__);
(\Delight\I18n\Http::parseClientLanguage('am-Ethio-ET') === [ null, null, null ]) or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-Latn-CH', 'de-CH', 'de-Latn-DE', 'de-DE', 'de' ],
	\Delight\I18n\Leniency::NONE,
	'de-DE'
) === 'de-DE') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-Latn-CH', 'de-CH', 'de-Latn-DE', 'de-DE', 'de' ],
	\Delight\I18n\Leniency::VERY_LOW,
	'de-DE'
) === 'de-DE') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-Latn-CH', 'de-CH', 'de-Latn-DE', 'de-DE', 'de' ],
	\Delight\I18n\Leniency::MODERATE,
	'de-DE'
) === 'de-DE') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-Latn-CH', 'de-CH', 'de-Latn-DE', 'de-DE', 'de' ],
	null,
	'de-DE'
) === 'de-DE') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-Latn-CH', 'de-CH', 'de-Latn-DE', 'de-DE', 'de' ],
	\Delight\I18n\Leniency::VERY_HIGH,
	'de-DE'
) === 'de-DE') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-Latn-CH', 'de-CH', 'de-Latn-DE', 'de-DE', 'de' ],
	\Delight\I18n\Leniency::FULL,
	'de-DE'
) === 'de-DE') or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-Latn-CH', 'de-CH', 'de-Latn-DE', 'de' ],
	\Delight\I18n\Leniency::NONE,
	'de-DE'
) === null) or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-Latn-CH', 'de-CH', 'de-Latn-DE', 'de' ],
	\Delight\I18n\Leniency::VERY_LOW,
	'de-DE'
) === 'de') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-Latn-CH', 'de-CH', 'de-Latn-DE', 'de' ],
	\Delight\I18n\Leniency::MODERATE,
	'de-DE'
) === 'de') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-Latn-CH', 'de-CH', 'de-Latn-DE', 'de' ],
	null,
	'de-DE'
) === 'de') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-Latn-CH', 'de-CH', 'de-Latn-DE', 'de' ],
	\Delight\I18n\Leniency::VERY_HIGH,
	'de-DE'
) === 'de') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-Latn-CH', 'de-CH', 'de-Latn-DE', 'de' ],
	\Delight\I18n\Leniency::FULL,
	'de-DE'
) === 'de') or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-DE' ],
	\Delight\I18n\Leniency::NONE,
	'de-Latn-CH, de-CH;q=0.8, de-Latn-DE;q=0.6, de-DE;q=0.4, de;q=0.2'
) === 'de-DE') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-DE' ],
	\Delight\I18n\Leniency::VERY_LOW,
	'de-Latn-CH, de-CH;q=0.8, de-Latn-DE;q=0.6, de-DE;q=0.4, de;q=0.2'
) === 'de-DE') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-DE' ],
	\Delight\I18n\Leniency::MODERATE,
	'de-Latn-CH, de-CH;q=0.8, de-Latn-DE;q=0.6, de-DE;q=0.4, de;q=0.2'
) === 'de-DE') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-DE' ],
	null,
	'de-Latn-CH, de-CH;q=0.8, de-Latn-DE;q=0.6, de-DE;q=0.4, de;q=0.2'
) === 'de-DE') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-DE' ],
	\Delight\I18n\Leniency::VERY_HIGH,
	'de-Latn-CH, de-CH;q=0.8, de-Latn-DE;q=0.6, de-DE;q=0.4, de;q=0.2'
) === 'de-DE') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-DE' ],
	\Delight\I18n\Leniency::FULL,
	'de-Latn-CH, de-CH;q=0.8, de-Latn-DE;q=0.6, de-DE;q=0.4, de;q=0.2'
) === 'de-DE') or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-DE' ],
	\Delight\I18n\Leniency::NONE,
	'de-Latn-CH, de-Latn-DE;q=0.5'
) === null) or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-DE' ],
	\Delight\I18n\Leniency::VERY_LOW,
	'de-Latn-CH, de-Latn-DE;q=0.5'
) === null) or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-DE' ],
	\Delight\I18n\Leniency::MODERATE,
	'de-Latn-CH, de-Latn-DE;q=0.5'
) === 'de-DE') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-DE' ],
	null,
	'de-Latn-CH, de-Latn-DE;q=0.5'
) === 'de-DE') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-DE' ],
	\Delight\I18n\Leniency::VERY_HIGH,
	'de-Latn-CH, de-Latn-DE;q=0.5'
) === 'de-DE') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'de-DE' ],
	\Delight\I18n\Leniency::FULL,
	'de-Latn-CH, de-Latn-DE;q=0.5'
) === 'de-DE') or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'KO-KR, ES-AR;q=0.5, SW;q=0.5, EN;q=0.5, ES;q=0.5, KO;q=0.5, RU-RU;q=0.5, DA-DK;q=0.5'
) === 'ko-KR') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-KR, eS-Ar;q=0.5, sw;q=0.5, eN;q=0.5, Es;q=0.5, KO;q=0.5, RU-ru;q=0.5, Da-dK;q=0.5'
) === 'ko-KR') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-kr, es-ar;q=0.5, sw;q=0.5, en;q=0.5, es;q=0.5, ko;q=0.5, ru-ru;q=0.5, da-dk;q=0.5'
) === 'ko-KR') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'KO-KR,ES-AR;q=0.5,SW;q=0.5,EN;q=0.5,ES;q=0.5,KO;q=0.5,RU-RU;q=0.5,DA-DK;q=0.5'
) === 'ko-KR') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-KR,eS-Ar;q=0.5,sw;q=0.5,eN;q=0.5,Es;q=0.5,KO;q=0.5,RU-ru;q=0.5,Da-dK;q=0.5'
) === 'ko-KR') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-kr,es-ar;q=0.5,sw;q=0.5,en;q=0.5,es;q=0.5,ko;q=0.5,ru-ru;q=0.5,da-dk;q=0.5'
) === 'ko-KR') or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'KO-KR;q=0.5, ES-AR;q=0.5, SW, EN;q=0.5, ES;q=0.5, KO;q=0.5, RU-RU;q=0.5, DA-DK;q=0.5'
) === 'sw') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-KR;q=0.5, eS-Ar;q=0.5, sw, eN;q=0.5, Es;q=0.5, KO;q=0.5, RU-ru;q=0.5, Da-dK;q=0.5'
) === 'sw') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-kr;q=0.5, es-ar;q=0.5, sw, en;q=0.5, es;q=0.5, ko;q=0.5, ru-ru;q=0.5, da-dk;q=0.5'
) === 'sw') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'KO-KR;q=0.5,ES-AR;q=0.5,SW,EN;q=0.5,ES;q=0.5,KO;q=0.5,RU-RU;q=0.5,DA-DK;q=0.5'
) === 'sw') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-KR;q=0.5,eS-Ar;q=0.5,sw,eN;q=0.5,Es;q=0.5,KO;q=0.5,RU-ru;q=0.5,Da-dK;q=0.5'
) === 'sw') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-kr;q=0.5,es-ar;q=0.5,sw,en;q=0.5,es;q=0.5,ko;q=0.5,ru-ru;q=0.5,da-dk;q=0.5'
) === 'sw') or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'KO-KR;q=0.5, ES-AR;q=0.5, SW;q=0.5, EN;q=0.5, ES;q=0.5, KO;q=0.5, RU-RU, DA-DK;q=0.5'
) === 'RU-ru') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-KR;q=0.5, eS-Ar;q=0.5, sw;q=0.5, eN;q=0.5, Es;q=0.5, KO;q=0.5, RU-ru, Da-dK;q=0.5'
) === 'RU-ru') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-kr;q=0.5, es-ar;q=0.5, sw;q=0.5, en;q=0.5, es;q=0.5, ko;q=0.5, ru-ru, da-dk;q=0.5'
) === 'RU-ru') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'KO-KR;q=0.5,ES-AR;q=0.5,SW;q=0.5,EN;q=0.5,ES;q=0.5,KO;q=0.5,RU-RU,DA-DK;q=0.5'
) === 'RU-ru') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-KR;q=0.5,eS-Ar;q=0.5,sw;q=0.5,eN;q=0.5,Es;q=0.5,KO;q=0.5,RU-ru,Da-dK;q=0.5'
) === 'RU-ru') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-kr;q=0.5,es-ar;q=0.5,sw;q=0.5,en;q=0.5,es;q=0.5,ko;q=0.5,ru-ru,da-dk;q=0.5'
) === 'RU-ru') or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'KO-KR;q=0.5, ES-AR;q=0.75, SW;q=0.5, EN;q=0.5, ES;q=0.5, KO;q=0.5, RU-RU;q=0.5, DA-DK;q=0.5'
) === 'eS-Ar') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-KR;q=0.5, eS-Ar;q=0.75, sw;q=0.5, eN;q=0.5, Es;q=0.5, KO;q=0.5, RU-ru;q=0.5, Da-dK;q=0.5'
) === 'eS-Ar') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-kr;q=0.5, es-ar;q=0.75, sw;q=0.5, en;q=0.5, es;q=0.5, ko;q=0.5, ru-ru;q=0.5, da-dk;q=0.5'
) === 'eS-Ar') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'KO-KR;q=0.5,ES-AR;q=0.75,SW;q=0.5,EN;q=0.5,ES;q=0.5,KO;q=0.5,RU-RU;q=0.5,DA-DK;q=0.5'
) === 'eS-Ar') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-KR;q=0.5,eS-Ar;q=0.75,sw;q=0.5,eN;q=0.5,Es;q=0.5,KO;q=0.5,RU-ru;q=0.5,Da-dK;q=0.5'
) === 'eS-Ar') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-kr;q=0.5,es-ar;q=0.75,sw;q=0.5,en;q=0.5,es;q=0.5,ko;q=0.5,ru-ru;q=0.5,da-dk;q=0.5'
) === 'eS-Ar') or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko;q=0.4'
) === 'KO') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-KR;q=0.4'
) === 'ko-KR') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ko-Hang-KR;q=0.4'
) === 'ko-KR') or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'es;q=0.4'
) === 'Es') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'es-AR;q=0.4'
) === 'eS-Ar') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'es-Latn-AR;q=0.4'
) === 'eS-Ar') or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'sw;q=0.4'
) === 'sw') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'sw-KE;q=0.4'
) === 'sw') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'sw-Latn-KE;q=0.4'
) === 'sw') or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'en;q=0.4'
) === 'eN') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'en-GB;q=0.4'
) === 'eN') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'en-Latn-GB;q=0.4'
) === 'eN') or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'es;q=0.4'
) === 'Es') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'es-419;q=0.4'
) === 'Es') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'es-Latn-419;q=0.4'
) === 'Es') or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ru;q=0.4'
) === 'RU-ru') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ru-RU;q=0.4'
) === 'RU-ru') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'ru-Cyrl-RU;q=0.4'
) === 'RU-ru') or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'da;q=0.4'
) === 'Da-dK') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'da-DK;q=0.4'
) === 'Da-dK') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'da-Latn-DK;q=0.4'
) === 'Da-dK') or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'eN', 'Da-dK', 'eS-Ar', 'Es', 'ko-KR', 'KO', 'sw', 'RU-ru' ],
	null,
	'fr-CA'
) === null) or \fail(__LINE__);


(\Delight\I18n\Http::matchClientLanguages(
	[ 'ar', 'en' ],
	null,
	'ar'
) === 'ar') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'ar', 'en' ],
	null,
	'ar-Arab, en-AU'
) === 'ar') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'ar', 'en' ],
	null,
	'ar-Latn, en-AU'
) === 'ar') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'ar', 'en' ],
	null,
	'ar-Arab-EG, en-AU'
) === 'ar') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'ar', 'en' ],
	null,
	'ar-Latn-EG, en-AU'
) === 'ar') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'ar-Arab', 'en' ],
	null,
	'ar'
) === 'ar-Arab') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'ar-Arab', 'en' ],
	null,
	'ar-Arab, en-AU'
) === 'ar-Arab') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'ar-Arab', 'en' ],
	null,
	'ar-Latn, en-AU'
) === 'en') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'ar-Arab', 'en' ],
	null,
	'ar-Arab-EG, en-AU'
) === 'ar-Arab') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'ar-Arab', 'en' ],
	null,
	'ar-Latn-EG, en-AU'
) === 'en') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'ar-Arab-EG', 'en' ],
	null,
	'ar'
) === 'ar-Arab-EG') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'ar-Arab-EG', 'en' ],
	null,
	'ar-Arab, en-AU'
) === 'ar-Arab-EG') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'ar-Arab-EG', 'en' ],
	null,
	'ar-Latn, en-AU'
) === 'en') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'ar-Arab-EG', 'en' ],
	null,
	'ar-Arab-EG, en-AU'
) === 'ar-Arab-EG') or \fail(__LINE__);
(\Delight\I18n\Http::matchClientLanguages(
	[ 'ar-Arab-EG', 'en' ],
	null,
	'ar-Latn-EG, en-AU'
) === 'en') or \fail(__LINE__);


echo 'ALL TESTS PASSED' . "\n";

// END TESTS

function fail($lineNumber) {
	exit('Error in line ' . $lineNumber);
}

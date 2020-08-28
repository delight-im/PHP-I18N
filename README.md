# PHP-I18N

**Internationalization and localization for PHP**

Provide your application in multiple languages, to users in various countries, with different formats and conventions.

## Requirements

 * PHP 5.6.0+
   * GNU gettext extension (`gettext`)
   * Internationalization extension (`intl`)

**Note:** On Windows, you may have to use the non-thread-safe (NTS) version of PHP.

**macOS:** To generate the po and mo files with the packaged bash script, install gnu-sed.
```zsh
$ brew install gnu-sed
$ PATH="$(brew --prefix)/opt/gnu-sed/libexec/gnubin:$PATH"
```

## Installation

 1. Include the library via Composer [[?]](https://github.com/delight-im/Knowledge/blob/master/Composer%20(PHP).md):

    ```bash
    $ composer require delight-im/i18n
    ```

 2. Copy the i18n bash script to the root of your project
     ```bash
    $ cp vendor/delight-im/i18n/i18n.sh .
    ```

 3. Include the Composer autoloader:

    ```php
    require __DIR__ . '/vendor/autoload.php';
    ```

## Usage

 * [What is a locale?](#what-is-a-locale)
 * [Decide on your initial set of supported locales](#decide-on-your-initial-set-of-supported-locales)
 * [Creating a new instance](#creating-a-new-instance)
 * [Directory and file names for translation files](#directory-and-file-names-for-translation-files)
 * [Activating the correct locale for the user](#activating-the-correct-locale-for-the-user)
   * [Automatically](#automatically)
   * [Manually](#manually)
 * [Enabling aliases for translation](#enabling-aliases-for-translation)
 * [Identifying, marking and formatting translatable strings](#identifying-marking-and-formatting-translatable-strings)
   * [Basic strings](#basic-strings)
   * [Strings with formatting](#strings-with-formatting)
   * [Strings with extended formatting](#strings-with-extended-formatting)
   * [Singular and plural forms](#singular-and-plural-forms)
   * [Singular and plural forms with formatting](#singular-and-plural-forms-with-formatting)
   * [Singular and plural forms with extended formatting](#singular-and-plural-forms-with-extended-formatting)
   * [Strings with context](#strings-with-context)
   * [Strings marked for later translation](#strings-marked-for-later-translation)
 * [Extracting and updating translatable strings](#extracting-and-updating-translatable-strings)
 * [Translating the extracted strings](#translating-the-extracted-strings)
 * [Exporting translations to binary format](#exporting-translations-to-binary-format)
 * [Retrieving the active locale](#retrieving-the-active-locale)
 * [Information about locales](#information-about-locales)
   * [Names of locales in the current language](#names-of-locales-in-the-current-language)
   * [Native names of locales](#native-names-of-locales)
   * [English names of locales](#english-names-of-locales)
   * [Names of languages in the current language](#names-of-languages-in-the-current-language)
   * [Native names of languages](#native-names-of-languages)
   * [English names of languages](#english-names-of-languages)
   * [Names of scripts in the current language](#names-of-scripts-in-the-current-language)
   * [Native names of scripts](#native-names-of-scripts)
   * [English names of scripts](#english-names-of-scripts)
   * [Names of regions in the current language](#names-of-regions-in-the-current-language)
   * [Native names of regions](#native-names-of-regions)
   * [English names of regions](#english-names-of-regions)
   * [Language codes](#language-codes)
   * [Script codes](#script-codes)
   * [Region codes](#region-codes)
   * [Directionality of text](#directionality-of-text)
 * [Controlling the leniency for lookups and comparisons of locales](#controlling-the-leniency-for-lookups-and-comparisons-of-locales)

### What is a locale?

Put simply, a locale is a set of user preferences and expectations, shared across larger communities in the world, and varying by geographic region. Notably, this includes a user’s language and their expectation of how numbers, dates and times are to be formatted.

### Decide on your initial set of supported locales

Whatever set of languages, scripts and regions you decide to support at the beginning, you will be able to add or remove locales at any later time. So perhaps you might like to start with just 1–3 locales to get started faster.

You can find a list of various locale codes in the [`Codes`](src/Codes.php) class and use the corresponding constants to refer to the locales, which is the recommended solution. Alternatively, you may copy their string values, which use a subset of IETF BCP 47 (RFC 5646) or Unicode CLDR identifiers.

Prior to using your initial set of languages, you should ensure they’re installed on any machine you’d like to develop or deploy your application on, making sure they are known to the operating system:

```bash
$ locale -a
```

If a certain locale is not installed yet, you can add it like the `es-AR` locale in the following example:

```bash
$ sudo locale-gen es_AR
$ sudo locale-gen es_AR.UTF-8
$ sudo update-locale
$ sudo service apache2 restart
```

**Note:** On Unix-like operating systems, the locale codes used during installation must use underscores.

### Creating a new instance

In order to create an instance of the `I18n` class, just provide your set of supported locales. The only special entry is the very first locale, which also serves as the default locale if no better match can be found for the user.

```php
$i18n = new \Delight\I18n\I18n([
    \Delight\I18n\Codes::EN_US,
    \Delight\I18n\Codes::DA_DK,
    \Delight\I18n\Codes::ES,
    \Delight\I18n\Codes::ES_AR,
    \Delight\I18n\Codes::KO,
    \Delight\I18n\Codes::KO_KR,
    \Delight\I18n\Codes::RU_RU,
    \Delight\I18n\Codes::SW
]);
```

### Directory and file names for translation files

Your translation files will later have to be stored in the following location relative to the root of your project:

```
locale/<LOCALE_CODE>/LC_MESSAGES/messages.po
```

That may be, for example, using the `es-ES` locale:

```
locale/es_ES/LC_MESSAGES/messages.po
```

If you need to change the path to the `locale` directory or want to use a different name for that directory, just specify its path explicitly:

```php
$i18n->setDirectory(__DIR__ . '/../translations');
```

The filename in the `LC_MESSAGES` directory, i.e. `messages.po`, is the name of the application module with the extension for PO (Portable Object) files. There’s usually no need to change that, but if you still want to do that, simply call the following method:

```php
$i18n->setModule('messages');
```

**Note:** On Unix-like operating systems, the locale codes used in the directory names have to use underscores, whereas on Windows, the codes have to use hyphens.

### Activating the correct locale for the user

#### Automatically

The easiest way to pick the most suitable locale for the user is to let this library decide based on various signals and options automatically:

```php
$i18n->setLocaleAutomatically();
```

This will check and decide based on the following factors (in that order):

 1. Subdomain with locale code (e.g. `da-DK.example.com`)
 1. Path prefix with locale code (e.g. `http://www.example.com/pt-BR/welcome.html`)
 1. Query string with locale code
    1. the `locale` parameter
    1. the `language` parameter
    1. the `lang` parameter
    1. the `lc` parameter
 1. Session field defined via `I18n#setSessionField` (e.g. `$i18n->setSessionField('locale');`)
 1. Cookie defined via `I18n#setCookieName` (e.g. `$i18n->setCookieName('lc');`), with an optional lifetime defined via `I18n#setCookieLifetime` (e.g. `$i18n->setCookieLifetime(60 * 60 * 24);`), where a value of `null` means that the cookie is to expire at the end of the current browser session
 1. HTTP request header `Accept-Language` (e.g. `en-US,en;q=0.5`)

You will usually choose a single one of these options to store and transport your locale codes, with other factors (specifically the last one) as fallback options. The first three options (and the last one) may provide advantages in terms of search engine optimization (SEO) and caching.

#### Manually

Of course, you can also specify the locale for your users manually:

```php
try {
    $i18n->setLocaleManually('es-AR');
}
catch (\Delight\I18n\Throwable\LocaleNotSupportedException $e) {
    die('The locale requested by the user is not supported');
}
```

### Enabling aliases for translation

Set up the following aliases in your application code to simplify your work with this library, to make your code more readable, and to enable support for the included tooling and other GNU gettext utilities:

```php
function _f($text, ...$replacements) { global $i18n; return $i18n->translateFormatted($text, ...$replacements); }

function _fe($text, ...$replacements) { global $i18n; return $i18n->translateFormattedExtended($text, ...$replacements); }

function _p($text, $alternative, $count) { global $i18n; return $i18n->translatePlural($text, $alternative, $count); }

function _pf($text, $alternative, $count, ...$replacements) { global $i18n; return $i18n->translatePluralFormatted($text, $alternative, $count, ...$replacements); }

function _pfe($text, $alternative, $count, ...$replacements) { global $i18n; return $i18n->translatePluralFormattedExtended($text, $alternative, $count, ...$replacements); }

function _c($text, $context) { global $i18n; return $i18n->translateWithContext($text, $context); }

function _m($text) { global $i18n; return $i18n->markForTranslation($text); }
```

If the variable holding your global `I18n` instance is not named `$i18n`, you have to adjust each occurrence of `$i18n` in the snippet above accordingly, of course.

### Identifying, marking and formatting translatable strings

In order to internationalize your code base, you have to identify and mark strings that can be translated, and use formatting with more complex strings. Afterwards, these marked strings can be extracted automatically, to be translated outside of the actual code, and will be inserted again during runtime by this library.

In general, you should follow these simple rules when marking strings for translations:

 * Use units of text as large as possible. This could be a single word (e.g. “Save” on a button), several words (e.g. “Create a new account” in a headline), or full sentences (e.g. “Your account has been created.”).
 * Strive to treat entire sentences as atomic units whenever possible, and don’t compose sentences from multiple translated words or parts unless absolutely necessary.
 * Use string formatting via one of the dedicated functions and methods instead of resorting to string concatenation or string interpolation.
 * Handle singular and plural forms using the dedicated functions and methods, which work even for languages with complex plural rules, which are not always as simple as the binary English rule.

#### Basic strings

Wrap the sentences, phrases and labels of your user interface inside of the `_` function:

```php
_('Welcome to our online store!');
// Welcome to our online store!
```

```php
_('Create account');
// Create account
```

```php
_('You have been successfully logged out.');
// You have been successfully logged out.
```

#### Strings with formatting

Wrap the sentences, phrases and labels of your user interface inside of the `_f` function:

```php
_f('This is %1$s.', 'Bob');
// This is Bob.
```

```php
_f('This is %1$d.', 3);
// This is 3.
```

```php
_f('This is %1$05d.', 3);
// This is 00003.
```

```php
_f('This is %1$ 5d.', 3);
// This is     3.
// This is ␣␣␣␣3.
```

```php
_f('This is %1$+d.', 3);
// This is +3.
```

```php
_f('This is %1$+06d.', 3);
// This is +00003.
```

```php
_f('This is %1$+ 6d.', 3);
// This is     +3.
// This is ␣␣␣␣+3.
```

```php
_f('This is %1$f.', 3.14);
// This is 3.140000.
```

```php
_f('This is %1$012f.', 3.14);
// This is 00003.140000.
```

```php
_f('This is %1$010.4f.', 3.14);
// This is 00003.1400.
```

```php
_f('This is %1$ 12f.', 3.14);
// This is     3.140000.
// This is ␣␣␣␣3.140000.
```

```php
_f('This is %1$ 10.4f.', 3.14);
// This is     3.1400.
// This is ␣␣␣␣3.1400.
```

```php
_f('This is %1$+f.', 3.14);
// This is +3.140000.
```

```php
_f('This is %1$+013f.', 3.14);
// This is +00003.140000.
```

```php
_f('This is %1$+011.4f.', 3.14);
// This is +00003.1400.
```

```php
_f('This is %1$+ 13f.', 3.14);
// This is     +3.140000.
// This is ␣␣␣␣+3.140000.
```

```php
_f('This is %1$+ 11.4f.', 3.14);
// This is     +3.1400.
// This is ␣␣␣␣+3.1400.
```

```php
_f('Hello %s!', 'Jane');
// Hello Jane!
```

```php
_f('%1$s is %2$d years old.', 'John', 30);
// John is 30 years old.
```

**Note:** This uses the “printf” format string syntax, known from the C language (and also from PHP). In order to escape the percent sign (to use it literally), simply double it, as in `50 %%`.

**Note:** When your format strings have more than one placeholder and replacement, always number the placeholders to avoid ambiguity and to allow for flexibility during translation. For example, instead of `%s is from %s`, use `%1$s is from %2$s`.

#### Strings with extended formatting

Wrap the sentences, phrases and labels of your user interface inside of the `_fe` function:

```php
_fe('This is {0}.', 'Bob');
// This is Bob.
```

```php
_fe('This is {0, number}.', 1003.14);
// This is 1,003.14.
```

```php
_fe('This is {0, number, percent}.', 0.42);
// This is 42%.
```

```php
_fe('This is {0, date}.', -14182916);
// This is Jul 20, 1969.
```

```php
_fe('This is {0, date, short}.', -14182916);
// This is 7/20/69.
```

```php
_fe('This is {0, date, medium}.', -14182916);
// This is Jul 20, 1969.
```

```php
_fe('This is {0, date, long}.', -14182916);
// This is July 20, 1969.
```

```php
_fe('This is {0, date, full}.', -14182916);
// This is Sunday, July 20, 1969.
```

```php
_fe('This is {0, time}.', -14182916);
// This is 1:18:04 PM.
```

```php
_fe('This is {0, time, short}.', -14182916);
// This is 1:18 PM.
```

```php
_fe('This is {0, time, medium}.', -14182916);
// This is 1:18:04 PM.
```

```php
_fe('This is {0, time, long}.', -14182916);
// This is 1:18:04 PM GMT-7.
```

```php
_fe('This is {0, time, full}.', -14182916);
// This is 1:18:04 PM GMT-07:00.
```

```php
_fe('This is {0, spellout}.', 314159);
// This is three hundred fourteen thousand one hundred fifty-nine.
```

```php
_fe('This is {0, ordinal}.', 314159);
// This is 314,159th.
```

```php
_fe('Hello {0}!', 'Jane');
// Hello Jane!
```

```php
_fe('{0} is {1, number} years old.', 'John', 30);
// John is 30 years old.
```

**Note:** This uses the ICU “MessageFormat” syntax. In order to escape curly brackets (to use them literally), wrap them in single quotes, as in `'{'` or `'}'`. In order to escape single quotes (to use them literally), simply double them, as in `it''s`. If you use single quotes for your string literals in PHP, you also have to escape the inserted single quotes with backslashes, as in `\'{\'`, `\'}\'` or `it\'\'s`.

#### Singular and plural forms

Wrap the sentences, phrases and labels of your user interface inside of the `_p` function:

```php
_p('cat', 'cats', 1);
// cat
```

```php
_p('cat', 'cats', 2);
// cats
```

```php
_p('cat', 'cats', 3);
// cats
```

```php
_p('The file has been saved.', 'The files have been saved.', 1);
// The file has been saved.
```

```php
_p('The file has been saved.', 'The files have been saved.', 2);
// The files have been saved.
```

```php
_p('The file has been saved.', 'The files have been saved.', 3);
// The files have been saved.
```

#### Singular and plural forms with formatting

Wrap the sentences, phrases and labels of your user interface inside of the `_pf` function:

```php
_pf('There is %d monkey.', 'There are %d monkeys.', 0);
// There are 0 monkeys.
```

```php
_pf('There is %d monkey.', 'There are %d monkeys.', 1);
// There is 1 monkey.
```

```php
_pf('There is %d monkey.', 'There are %d monkeys.', 2);
// There are 2 monkeys.
```

```php
_pf('There is %1$d monkey in %2$s.', 'There are %1$d monkeys in %2$s.', 3, 'Anytown');
// There are 3 monkeys in Anytown.
```

```php
_pf('You have %d new message', 'You have %d new messages', 0);
// You have 0 new messages
```

```php
_pf('You have %d new message', 'You have %d new messages', 1);
// You have 1 new message
```

```php
_pf('You have %d new message', 'You have %d new messages', 32);
// You have 32 new messages
```

**Note:** This uses the “printf” format string syntax, known from the C language (and also from PHP). In order to escape the percent sign (to use it literally), simply double it, as in `50 %%`.

#### Singular and plural forms with extended formatting

Wrap the sentences, phrases and labels of your user interface inside of the `_pfe` function:

```php
_pfe('There is {0, number} monkey.', 'There are {0, number} monkeys.', 0);
// There are 0 monkeys.
```

```php
_pfe('There is {0, number} monkey.', 'There are {0, number} monkeys.', 1);
// There is 1 monkey.
```

```php
_pfe('There is {0, number} monkey.', 'There are {0, number} monkeys.', 2);
// There are 2 monkeys.
```

```php
_pfe('There is {0, number} monkey in {1}.', 'There are {0, number} monkeys in {1}.', 3, 'Anytown');
// There are 3 monkeys in Anytown.
```

```php
_pfe('You have {0, number} new message', 'You have {0, number} new messages', 0);
// You have 0 new messages
```

```php
_pfe('You have {0, number} new message', 'You have {0, number} new messages', 1);
// You have 1 new message
```

```php
_pfe('You have {0, number} new message', 'You have {0, number} new messages', 32);
// You have 32 new messages
```

**Note:** This uses the ICU “MessageFormat” syntax. In order to escape curly brackets (to use them literally), wrap them in single quotes, as in `'{'` or `'}'`. In order to escape single quotes (to use them literally), simply double them, as in `it''s`. If you use single quotes for your string literals in PHP, you also have to escape the inserted single quotes with backslashes, as in `\'{\'`, `\'}\'` or `it\'\'s`.

#### Strings with context

Wrap the sentences, phrases and labels of your user interface inside of the `_c` function:

```php
_c('Order', 'sorting');
// or
_c('Order', 'purchase');
// or
_c('Order', 'mathematics');
// or
_c('Order', 'classification');
```

```php
_c('Address:', 'location');
// or
_c('Address:', 'www');
// or
_c('Address:', 'email');
// or
_c('Address:', 'letter');
// or
_c('Address:', 'speech');
```

#### Strings marked for later translation

Wrap the sentences, phrases and labels of your user interface inside of the `_m` function. This is a no-op instruction, i.e. (at first glance), it does nothing. But it marks the wrapped text for later translation. This is useful if the text should not be translated *immediately* but will *later* be translated from a variable, usually at the latest point in time possible:

```php
_m('User');
// User
```

This return value could be inserted into your database, for example, and will always use the original string from the source code. Later, you could then use the following call to translate that string from a variable:

```php
$text = 'User';
_($text);
// User
```

### Extracting and updating translatable strings

In order to extract all translatable strings from your PHP files, you can use the built-in tool for this task:

```bash
# For the `mr-IN` locale, with the default directory, with the default domain, and with fuzzy matching
$ bash ./i18n.sh mr-IN
```

```bash
# For the `sq-MK` locale, with the directory 'translations', with the default domain, and with fuzzy matching
$ bash ./i18n.sh sq-MK translations
```

```bash
# For the `yo-NG` locale, with the default directory, with the domain 'plugin', and with fuzzy matching
$ bash ./i18n.sh yo-NG "" plugin
```

```bash
# For the `fr-FR` locale, with the default directory, with the default domain, and without fuzzy matching
$ bash ./i18n.sh fr-FR "" "" nofuzzy
```

This creates or updates a PO (Portable Object) file for the specified language, which you can then translate, share with your translation team, or send to external translators.

If you only need a generic POT (Portable Object Template) file with all extracted strings, which is not specific to a certain language, just leave out the argument with the locale code (or set it to an empty string):

```bash
# With the default directory, with the default domain, and with fuzzy matching
$ bash ./i18n.sh
```

```bash
# With the directory 'translations', with the default domain, and with fuzzy matching
$ bash ./i18n.sh "" translations
```

```bash
# With the default directory, with the domain 'plugin', and with fuzzy matching
$ bash ./i18n.sh "" "" plugin
```

```bash
# With the default directory, with the default domain, and without fuzzy matching
$ bash ./i18n.sh "" "" "" nofuzzy
```

### Translating the extracted strings

Whoever handles the actual task of translating the extracted strings, whether it’s you, your translation team, or external translators, the people in charge will need the PO (Portable Object) file for their language, or, in some cases, the generic POT (Portable Object Template) file.

Just open the file in question and search for strings with `msgstr ""` below them. These are the strings with empty translations that you still need to work on. In addition to that, any string with `#, fuzzy` above it has had a translation before but the original string in the source code changed, so the translation must be reviewed (and the “fuzzy” flag or comment removed).

### Exporting translations to binary format

After you have worked on your translations and saved the PO (Portable Object) file for a language, you need to run the command from [“Extracting and updating translatable strings”](#extracting-and-updating-translatable-strings) again in order to export these translations to a binary format.

They will then be stored in a MO (Machine Object) file alongside your PO (Portable Object) file, ready to be automatically picked up and inserted in place of the original strings.

### Retrieving the active locale

```php
$i18n->getLocale();
// en-US
```

```php
$i18n->getSystemLocale();
// en_US.utf8
```

### Information about locales

#### Names of locales in the current language

```php
$i18n->getLocaleName();
// English (United States)
```

```php
$i18n->getLocaleName('fr-BE');
// French (Belgium)
```

```php
\Delight\I18n\Locale::toName('nb-NO');
// Norwegian Bokmål (Norway)
```

#### Native names of locales

```php
$i18n->getNativeLocaleName();
// English (United States)
```

```php
$i18n->getNativeLocaleName('fr-BE');
// français (Belgique)
```

```php
\Delight\I18n\Locale::toNativeName('nb-NO');
// norsk bokmål (Norge)
```

#### English names of locales

```php
\Delight\I18n\Locale::toEnglishName('nb-NO');
// Norwegian Bokmål (Norway)
```

#### Names of languages in the current language

```php
$i18n->getLanguageName();
// English
```

```php
$i18n->getLanguageName('fr-BE');
// French
```

```php
\Delight\I18n\Locale::toLanguageName('nb-NO');
// Norwegian Bokmål
```

#### Native names of languages

```php
$i18n->getNativeLanguageName();
// English
```

```php
$i18n->getNativeLanguageName('fr-BE');
// français
```

```php
\Delight\I18n\Locale::toNativeLanguageName('nb-NO');
// norsk bokmål
```

#### English names of languages

```php
\Delight\I18n\Locale::toEnglishLanguageName('nb-NO');
// Norwegian Bokmål
```

#### Names of scripts in the current language

```php
\Delight\I18n\Locale::toScriptName('nb-Latn-NO');
// Latin
```

#### Native names of scripts

```php
\Delight\I18n\Locale::toNativeScriptName('nb-Latn-NO');
// latinsk
```

#### English names of scripts

```php
\Delight\I18n\Locale::toEnglishScriptName('nb-Latn-NO');
// Latin
```

#### Names of regions in the current language

```php
\Delight\I18n\Locale::toRegionName('nb-NO');
// Norway
```

#### Native names of regions

```php
\Delight\I18n\Locale::toNativeRegionName('nb-NO');
// Norge
```

#### English names of regions

```php
\Delight\I18n\Locale::toEnglishRegionName('nb-NO');
// Norway
```

#### Language codes

```php
\Delight\I18n\Locale::toLanguageCode('nb-Latn-NO');
// nb
```

#### Script codes

```php
\Delight\I18n\Locale::toScriptCode('nb-Latn-NO');
// Latn
```

#### Region codes

```php
\Delight\I18n\Locale::toRegionCode('nb-Latn-NO');
// NO
```

#### Directionality of text

```php
\Delight\I18n\Locale::isRtl('ur-PK');
// true
```

```php
\Delight\I18n\Locale::isLtr('ln-CD');
// true
```

### Controlling the leniency for lookups and comparisons of locales

When using `I18n#setLocaleAutomatically` to determine and activate the correct locale for the user automatically, you can control which locales to consider similar or related. Thus you can control the way lookups and comparisons of locales work.

If the default behavior doesn’t work for you, simply provide the optional first argument, called `$leniency`, to `I18n#setLocaleAutomatically`. The following table lists the minimum leniency value that is required to match the two locale codes in question:

|                            | `sr`                       | `sr-RS`                    | `sr-BA`                    | `sr-Cyrl`                  | `sr-Latn`                  | `sr-Cyrl-RS`               | `sr-Cyrl-BA`               | `sr-Latn-RS`               | `sr-Latn-BA`               |
| :------------------------: | :------------------------: | :------------------------: | :------------------------: | :------------------------: | :------------------------: | :------------------------: | :------------------------: | :------------------------: | :------------------------: |
| `sr`                       | `Leniency::NONE`           | `Leniency::EXTREMELY_LOW`  | `Leniency::EXTREMELY_LOW`  | `Leniency::LOW`            | `Leniency::LOW`            | `Leniency::MODERATE`       | `Leniency::MODERATE`       | `Leniency::MODERATE`       | `Leniency::MODERATE`       |
| `sr_RS`                    | `Leniency::EXTREMELY_LOW`  | `Leniency::NONE`           | `Leniency::VERY_LOW`       | `Leniency::MODERATE`       | `Leniency::MODERATE`       | `Leniency::LOW`            | `Leniency::HIGH`           | `Leniency::LOW`            | `Leniency::HIGH`           |
| `sr_BA`                    | `Leniency::EXTREMELY_LOW`  | `Leniency::VERY_LOW`       | `Leniency::NONE`           | `Leniency::MODERATE`       | `Leniency::MODERATE`       | `Leniency::HIGH`           | `Leniency::LOW`            | `Leniency::HIGH`           | `Leniency::LOW`            |
| `sr_Cyrl`                  | `Leniency::LOW`            | `Leniency::MODERATE`       | `Leniency::MODERATE`       | `Leniency::NONE`           | `Leniency::VERY_HIGH`      | `Leniency::EXTREMELY_LOW`  | `Leniency::EXTREMELY_LOW`  | `Leniency::EXTREMELY_HIGH` | `Leniency::EXTREMELY_HIGH` |
| `sr_Latn`                  | `Leniency::LOW`            | `Leniency::MODERATE`       | `Leniency::MODERATE`       | `Leniency::VERY_HIGH`      | `Leniency::NONE`           | `Leniency::EXTREMELY_HIGH` | `Leniency::EXTREMELY_HIGH` | `Leniency::EXTREMELY_LOW`  | `Leniency::EXTREMELY_LOW`  |
| `sr_Cyrl_RS`               | `Leniency::MODERATE`       | `Leniency::LOW`            | `Leniency::HIGH`           | `Leniency::EXTREMELY_LOW`  | `Leniency::EXTREMELY_HIGH` | `Leniency::NONE`           | `Leniency::VERY_LOW`       | `Leniency::VERY_HIGH`      | `Leniency::FULL`           |
| `sr_Cyrl_BA`               | `Leniency::MODERATE`       | `Leniency::HIGH`           | `Leniency::LOW`            | `Leniency::EXTREMELY_LOW`  | `Leniency::EXTREMELY_HIGH` | `Leniency::VERY_LOW`       | `Leniency::NONE`           | `Leniency::FULL`           | `Leniency::VERY_HIGH`      |
| `sr_Latn_RS`               | `Leniency::MODERATE`       | `Leniency::LOW`            | `Leniency::HIGH`           | `Leniency::EXTREMELY_HIGH` | `Leniency::EXTREMELY_LOW`  | `Leniency::VERY_HIGH`      | `Leniency::FULL`           | `Leniency::NONE`           | `Leniency::VERY_LOW`       |
| `sr_Latn_BA`               | `Leniency::MODERATE`       | `Leniency::HIGH`           | `Leniency::LOW`            | `Leniency::EXTREMELY_HIGH` | `Leniency::EXTREMELY_LOW`  | `Leniency::FULL`           | `Leniency::VERY_HIGH`      | `Leniency::VERY_LOW`       | `Leniency::NONE`           |

## Troubleshooting

 * Translations are usually cached, so it may be necessary to restart the web server for any changes to take effect.

## Contributing

All contributions are welcome! If you wish to contribute, please create an issue first so that your feature, problem or question can be discussed.

## License

This project is licensed under the terms of the [MIT License](https://opensource.org/licenses/MIT).

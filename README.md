# PHP-I18N

Guide to internationalization (I18N) in PHP with simple wrapper class

## Requirements

 * PHP 5.3+
 * gettext
 * [Poedit](http://poedit.net/)

## Example usage

If you're familiar with gettext already, just use our class as indicated in the example below and make your normal calls to gettext. Our class comes with full documentation, so just refer to the PHP docs of the methods that are used.

If you're new to gettext, please read the tutorial below. If you have any questions, feel free to open a new issue in this repository.

### Initializing gettext with auto-detected language

```
@session_start();
require_once('./classes/I18N.php');
I18N::init('messages', './i18n', 'en_US', array(
    '/(^de(-.*?)?$)/i' => 'de_DE',
    '/(^en(-.*?)?$)/i' => 'en_US',
    '/(^es(-.*?)?$)/i' => 'es_ES'
));
```

The code above initializes gettext for the domain `messages` and searches for translations in folder `i18n`. It will auto-detect the user's language using the given mappings and default to `en_US` if no language could be detected.

### Overriding the auto-detected language with a manual user selection

```
if (isset($_GET['setLocale'])) {
    I18N::changeLanguage($_GET['setLocale']);
}
```

Let the user change their language manually by providing a link such as `<a href="/?setLocale=es_ES">Español</a>` and including the code above immediately after `require_once('./classes/I18N.php');`.

## Tutorial

### Start using gettext to reference strings

 * Instead of writing strings in PHP directly (e.g. `'This is some text'`), start using `_('This is some text')` everywhere. At first, you won't see any differences. But later, you can easily add translations for any language and even let third-party translation services do the work without touching the code.
 * For your texts, use units as large as possible. This could be a single word (e.g. `Save` on a button), several words (e.g. `Sign up` in a headline) or full sentences (e.g. `Your account has been created.`).
 * If you need to insert variable numbers or strings inside of your translations, please refer to [the related part of our tutorial below](#use-gettext-with-sprintf-style-formatting).

### Add new languages with Poedit

 1. Run Poedit
 2. Choose `File` and then `New...`
 3. Choose a language (usually in combination with a country) from the list and hit `OK`
 4. Now that the new project is open, you should see a message stating that `There are no translations`
 5. Go to `File` and choose `Save as...`
 6. Navigate to the local directory where your source code is located
 7. In the source code's root folder, create a new folder path `i18n/ll_CC/LC_MESSAGES` where `ll_CC` is the language and country name of the language that you are working on
 8. In that new folder, save the Poedit project with the name `messages` (extension `.po`) and hit `Save`
 9. Go to `Catalogue` at the top and choose `Properties...`
 10. Enter your project name (e.g. `My project`) and make sure both `Charset` and `Source code charset` are set to `UTF-8`
 11. Switch to the `Sources paths` tab, click the second button (`New item`), enter `../../../` (three directories up) and hit `Enter`
 12. Close the settings dialog by pressing `OK`
 13. Go to `Catalogue` at the top again and click `Update from sources` (save changes if prompted)
 14. Wait for Poedit to finish scanning your source code
 15. Enter your translations for the given source texts
 16. Press `Save`
 17. If your configuration for Poedit is correct, both a `*.po` and `*.mo` file should have been created for your new language
 18. Upload both files to the server (to `i18n/ll_CC/LC_MESSAGES/messages.[po|mo]`
 19. Include the class `I18N.php` at the top of your PHP files as indicated in the [example usage](#example-usage)

### Update existing languages with Poedit

 1. Open one of the existing `*.po` files from the `i18n` directory in your source code's root folder
 2. Go to `Catalogue` at the top and choose `Update from sources`
 3. Enter your translations for the given source texts
 4. Press `Save`

### Use gettext with sprintf-style formatting

Instead of concatenating single parts of a sentence with gettext, use two underscores (`__(...)`) instead of one (`_(...)`) to add placeholders and formatting to the gettext function.

Use the first argument just like with gettext's standard function but add any number of placeholders inside, either for strings (`%s`), for integers (`%d`) or for floats (`%f`). Pass the replacements in the subsequent arguments.

If there is more than one placeholder/replacement, always number the placeholders with `%1$s`, `%2$d`, `%3$d`, `%4$s`, etc.

Read the [sprintf documentation](http://www.php.net/manual/en/function.sprintf.php) for help with more complex formatting.

**Examples:**

```
// There are 5 monkeys in the tree
echo __('There are %d monkeys in the tree', 4+1);
// There are 3 monkeys in the garden
echo __('There are %1$d monkeys in the %2$s', 3, _('garden'));
// You have 32 new messages
echo __('You have %d new messages', 32);
```

## Notes

 * gettext data is usually cached so it may be necessary to restart the web server for changes to take effect

## License

```
Copyright 2014 www.delight.im <info@delight.im>

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
```
# PHP-I18N

Guide to internationalization (I18N) in PHP with simple wrapper class

## Requirements

 * PHP 5.3+
 * gettext
 * [Poedit](http://poedit.net/)

## Usage

 1. Getting strings from gettext
   * Instead of writing strings in PHP directly (e.g. `'This is some text'`), start using `_('This is some text')` everywhere. At first, you won't see any differences. But later, you can easily add translations for any language and even let third-party translation services do the work without touching the code.
   * For your texts, use units as large as possible. This could be a single word (e.g. `Save` on a button), several words (e.g. `Sign up` in a headline) or full sentences (e.g. `Your account has been created.`).
   * If you need to insert variable numbers or strings inside of your translations, add placeholders (e.g. `You have %d new messages`) and wrap the call to gettext inside `sprintf(...)`, e.g. `sprintf(_('You have %d new messages'), 32)`
 2. Translating the source code with Poedit
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
   19. Include the class `I18N.php` in your PHP files (at the top) and call `I18N::init($lang, 'messages', './i18n')` where `$lang` is your `ll_CC` string (e.g. `en_US`) that defines the language of your page

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
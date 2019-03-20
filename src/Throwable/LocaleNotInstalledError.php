<?php

/*
 * PHP-I18N (https://github.com/delight-im/PHP-I18N)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace Delight\I18n\Throwable;

/** Error that is thrown when an attempt has been made to use a locale that is not known to the operating system */
class LocaleNotInstalledError extends Error {}

#!/bin/bash

### PHP-I18N (https://github.com/delight-im/PHP-I18N)
### Copyright (c) delight.im (https://www.delight.im/)
### Licensed under the MIT License (https://opensource.org/licenses/MIT)

set -eu

# Switch to the directory where the current script is located
cd "${BASH_SOURCE%/*}" || exit 1

echo "Extracting and updating translations"

LOCALE_CODE="${1:-}"
LOCALE_PARENT_DIR="${2:-locale}"
LOCALE_DOMAIN="${3:-messages}"

if [ ! -d "${LOCALE_PARENT_DIR}" ]; then
	echo " * Error: Target directory “${LOCALE_PARENT_DIR}” not found"
	exit 2
fi

if [ ! -w "${LOCALE_PARENT_DIR}" ]; then
	echo " * Error: Target directory “${LOCALE_PARENT_DIR}” not writable"
	exit 3
fi

if [ -z "${LOCALE_CODE}" ]; then
	echo " * Creating generic POT (Portable Object Template) file"
fi

find . -iname "*.php" | xargs xgettext --output="${LOCALE_DOMAIN}.pot" --output-dir="${LOCALE_PARENT_DIR}" --language=PHP --from-code=UTF-8 --force-po --no-location --no-wrap --sort-output --copyright-holder="" --keyword --keyword="_:1,1t" --keyword="_f:1" --keyword="_fe:1" --keyword="_p:1,2,3t" --keyword="_pf:1,2" --keyword="_pfe:1,2" --keyword="_c:1,2c,2t" --keyword="_m:1,1t" --flag="_f:1:php-format" --flag="_fe:1:no-php-format" --flag="_pf:1:php-format" --flag="_pfe:1:no-php-format"
sed -i '/# SOME DESCRIPTIVE TITLE./d' "${LOCALE_PARENT_DIR}/${LOCALE_DOMAIN}.pot"
sed -i '/# This file is put in the public domain./d' "${LOCALE_PARENT_DIR}/${LOCALE_DOMAIN}.pot"
sed -i '/# FIRST AUTHOR <EMAIL@ADDRESS>, YEAR./d' "${LOCALE_PARENT_DIR}/${LOCALE_DOMAIN}.pot"
sed -i '0,/#, fuzzy/{s/#, fuzzy//}' "${LOCALE_PARENT_DIR}/${LOCALE_DOMAIN}.pot"
sed -i '/\"Project-Id-Version: PACKAGE VERSION\\n\"/d' "${LOCALE_PARENT_DIR}/${LOCALE_DOMAIN}.pot"
sed -i '/\"Report-Msgid-Bugs-To: \\n\"/d' "${LOCALE_PARENT_DIR}/${LOCALE_DOMAIN}.pot"
sed -i '/\"POT-Creation-Date: /d' "${LOCALE_PARENT_DIR}/${LOCALE_DOMAIN}.pot"
sed -i '/\"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n\"/d' "${LOCALE_PARENT_DIR}/${LOCALE_DOMAIN}.pot"
sed -i '/\"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n\"/d' "${LOCALE_PARENT_DIR}/${LOCALE_DOMAIN}.pot"
sed -i '/\"Language-Team: LANGUAGE <LL@li.org>\\n\"/d' "${LOCALE_PARENT_DIR}/${LOCALE_DOMAIN}.pot"
sed -i '0,/\"Language: \\n\"/{s/\"Language: \\n\"/\"Language: xx\\n\"/}' "${LOCALE_PARENT_DIR}/${LOCALE_DOMAIN}.pot"
sed -i '0,/\"Content-Type: text\/plain; charset=CHARSET\\n\"/{s/\"Content-Type: text\/plain; charset=CHARSET\\n\"/\"Content-Type: text\/plain; charset=UTF-8\\n\"/}' "${LOCALE_PARENT_DIR}/${LOCALE_DOMAIN}.pot"

if [ ! -z "${LOCALE_CODE}" ]; then
	LOCALE_CONTENTS_DIR="${LOCALE_PARENT_DIR}/${LOCALE_CODE}/LC_MESSAGES"

	mkdir --parents "${LOCALE_CONTENTS_DIR}"

	echo " * Creating PO (Portable Object) file for “${LOCALE_CODE}”"

	if [ -f "${LOCALE_CONTENTS_DIR}/${LOCALE_DOMAIN}.po" ]; then
		msgmerge --update --backup=none --suffix=".bak" --previous --force-po --no-location --no-wrap --sort-output "${LOCALE_CONTENTS_DIR}/${LOCALE_DOMAIN}.po" "${LOCALE_PARENT_DIR}/${LOCALE_DOMAIN}.pot"
	else
		msginit --input="${LOCALE_PARENT_DIR}/${LOCALE_DOMAIN}.pot" --output-file="${LOCALE_CONTENTS_DIR}/${LOCALE_DOMAIN}.po" --locale="${LOCALE_CODE}" --no-translator --no-wrap
		sed -i '/\"Project-Id-Version: /d' "${LOCALE_CONTENTS_DIR}/${LOCALE_DOMAIN}.po"
		sed -i '/\"Last-Translator: Automatically generated\\n\"/d' "${LOCALE_CONTENTS_DIR}/${LOCALE_DOMAIN}.po"
		sed -i '/\"Language-Team: none\\n\"/d' "${LOCALE_CONTENTS_DIR}/${LOCALE_DOMAIN}.po"
	fi

	echo " * Creating MO (Machine Object) file for “${LOCALE_CODE}”"

	msgfmt --output-file="${LOCALE_CONTENTS_DIR}/${LOCALE_DOMAIN}.mo" --check-format --check-domain "${LOCALE_CONTENTS_DIR}/${LOCALE_DOMAIN}.po"

	rm "${LOCALE_PARENT_DIR}/${LOCALE_DOMAIN}.pot"
fi

echo "Done"

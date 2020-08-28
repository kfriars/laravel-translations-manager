# Laravel Translations Manager

![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/kfriars/laravel-translations-manage?color=%234ccd98&label=php&logo=php&logoColor=%23fff)
![Laravel Version Support](https://img.shields.io/badge/laravel-6.x--7.x-%2343d399?logo=laravel&logoColor=%23ffffff)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/kfriars/laravel-translations-manager.svg?color=%234ccd98&style=flat-square)](https://packagist.org/packages/kfriars/laravel-translations-manager)
[![Total Downloads](https://img.shields.io/packagist/dt/kfriars/laravel-translations-manager.svg?color=%234ccd98&style=flat-square)](https://packagist.org/packages/kfriars/laravel-translations-manager)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/kfriars/laravel-translations-manager/Tests?color=%234ccd98&label=Tests&logo=github&logoColor=%23fff)](https://github.com/kfriars/php-array-to-file/actions?query=workflow%3ATests)
[![Code Climate coverage](https://img.shields.io/codeclimate/coverage/kfriars/laravel-translations-manager?color=%234ccd98&label=test%20coverage&logo=code-climate&logoColor=%23fff)](https://codeclimate.com/github/kfriars/laravel-translations-manager/test_coverage)
[![Code Climate maintainability](https://img.shields.io/codeclimate/maintainability/kfriars/laravel-translations-manager?color=%234ccd98&label=maintainablility&logo=code-climate&logoColor=%23fff)](https://codeclimate.com/github/kfriars/laravel-translations-manager/maintainability)


## Why use this Package?

Have you ever worked on a project with multiple locales to be supported, created a new branch, worked for a few days, then wondered exactly what translations you have added or changed? If so, you know it is time intensive and error prone for developers to be managing what translations need to be added or updated.

This package's intended purpose is to make this entire process a breeze. Want to know what has been updated, deleted and added? Simple. Want to export everything needing to be translated to a file that can be sent to a translator? No problem. Want to automatically update all of the files that were translated so there are no more errors? Done. 

&nbsp;  
## Contents
- [Laravel Translations Manager](#laravel-translations-manager)
  - [Why use this Package?](#why-use-this-package)
  - [Contents](#contents)
  - [Installation](#installation)
  - [Reference Locale](#reference-locale)
  - [Workflow](#workflow)
    - [1)&nbsp;&nbsp;Check for errors](#1check-for-errors)
    - [2)&nbsp;&nbsp;Clean Up Dead Translations](#2clean-up-dead-translations)
    - [3)&nbsp;&nbsp;Ignoring Errors](#3ignoring-errors)
    - [4)&nbsp;&nbsp;Fix-File Generation](#4fix-file-generation)
    - [5)&nbsp;&nbsp;Fixing Files](#5fixing-files)
    - [6)&nbsp;&nbsp;Validating Translations](#6validating-translations)
    - [7)&nbsp;&nbsp;Locking Translations](#7locking-translations)
    - [8)&nbsp;&nbsp;Create PR and Merge](#8create-pr-and-merge)
  - [Commands](#commands)
    - [Validate](#validate)
    - [Errors](#errors)
    - [Clean](#clean)
    - [Ignore](#ignore)
    - [Unignore](#unignore)
    - [Generate Fixes](#generate-fixes)
    - [Fix](#fix)
    - [Status](#status)
  - [Testing](#testing)
  - [Changelog](#changelog)
  - [Contributing](#contributing)
  - [Security](#security)
  - [Credits](#credits)
  - [License](#license)
  
&nbsp;  
## Installation

You can install the package via composer:

```bash
composer require kfriars/laravel-translations-manager
```

This package should work out of the box without any changes to configuration. However, you can publish the config file using:
```bash
php artisan vendor:publish --provider="Kfriars\TranslationsManager\TranslationsManagerServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Laravel Lang Directory
    |--------------------------------------------------------------------------
    |
    | This value is the path to laravels 'lang' folder. This value is used to
    | find your applications translations files using __() and Trans
    |
    */

    'lang_dir' => resource_path('lang'),

    /*
    |--------------------------------------------------------------------------
    | Translations Lock Files Path
    |--------------------------------------------------------------------------
    |
    | This is the folder where this package will store its version locks for
    | your translations files. This folder must not be in .gitignore to have this
    | package function correctly.
    |
    */

    'lock_dir' => storage_path('translations/lock'),

    /*
    |--------------------------------------------------------------------------
    | Translations Ignore File
    |--------------------------------------------------------------------------
    |
    | This is the file where this package will store which translations errors
    | will be ignored.
    |
    */

    'ignores' => storage_path('translations/ignores.php'),

    /*
    |--------------------------------------------------------------------------
    | Reference Locales
    |--------------------------------------------------------------------------
    |
    | This is the locale that the development team or a developer works in.
    | This setting assumes all changes to translations files are being made in
    | this locale.
    |
    */

    'reference_locale' => config('app.locale'),

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | These are the locales supported by your application. By default, the
    | Supported locales are all folders listed in the lang directory. You can
    | override the setting if you do not want all locales to be validated using
    | this package.
    |
    */

    // 'supported_locales' => [],

    /*
    |--------------------------------------------------------------------------
    | Fix Files
    |--------------------------------------------------------------------------
    |
    | The fix files are used to fix errors in the translations.
    |
    | 'fixes_dir' is where fix file will be generated with the strings that
    | require translations.
    |
    | 'fixed_dir' is where you place translated fix files to be parsed by the
    | package and automatically fix your files.
    |
    | 'fix_name_format' is the way fix files will be named. The currently
    | supported formats are 'git' and 'date'.
    |
    | 'git'  format is 'fixes-{locale}-{git branch name}.txt'
    | 'date' format is 'fixes-{locale}-{Y-m-d}.txt'
    |
    */
    'fixes_dir' => storage_path('translations/fixes'),
    'fixed_dir' => storage_path('translations/fixed'),
    'fix_name_format' => 'git',
];
```

&nbsp;  
## Reference Locale

The reference locale is the language your development team uses for development. This package is built around the idea that the current reference locale's translations are the correct version of the translations for the project. 

For example:

You are working on a project for a French company but the project also supports English, German and Spanish. The mock-ups are given in French, so you make French the default app locale, and create translations files in French first. Then everything is translated to English, German and Spanish after the fact. French would be the reference locale.

&nbsp;  
## Workflow
*Assume standard git-flow is used, where the ```master``` branch gets deployed to production and the ```develop``` branch should be kept in a deployable state.*

This package is intended to be used as part of a project's workflow. For a branch to be in a deploayble state there should be no translation errors.

When a ```feature``` branch is completed, and ready to make a pull request to ```develop```, the following steps should be taken to ensure there are no translations errors.

&nbsp;  
### 1)&nbsp;&nbsp;Check for errors
This command will list all translation errors in your project.

```bash
php artisan translations:errors
```

Example output:

```Shell
There are 9 error(s) in the translations files:

+-------------+-------------------------------+
| de/common                                   |
+-------------+-------------------------------+
| Key         | Message                       |
+-------------+-------------------------------+
| company     | translation_missing           |
+-------------+-------------------------------+


+-------------+-------------------------------+
| de/contact                                  |
+-------------+-------------------------------+
| Key         | Message                       |
+-------------+-------------------------------+
| email       | translation_missing           |
+-------------+-------------------------------+
| straße      | no_reference_translation      |
+-------------+-------------------------------+
| phone       | reference_translation_updated |
+-------------+-------------------------------+

+------------+----------------------+
| de/admin                          |
+------------+----------------------+
| Key        | Message              |
+------------+----------------------+
| FILE_ERROR | file_not_translated  |
+------------+----------------------+

+-------------+-------------------------------+
| en/contact                                  |
+-------------+-------------------------------+
| Key         | Message                       |
+-------------+-------------------------------+
| phone       | reference_translation_updated |
+-------------+-------------------------------+

+------------+----------------------+
| en/admin                          |
+------------+----------------------+
| Key        | Message              |
+------------+----------------------+
| FILE_ERROR | file_not_translated  |
+------------+----------------------+

+-------------+---------------------+
| es/contact                        |
+-------------+---------------------+
| Key         | Message             |
+-------------+---------------------+
| FILE_ERROR  | file_not_translated |
+-------------+---------------------+

+------------+----------------------+
| es/admin                          |
+------------+----------------------+
| Key        | Message              |
+------------+----------------------+
| FILE_ERROR | file_not_translated  |
+------------+----------------------+
``` 

**Error Types:**

| Error                             | Description                                                                                                                                                                                                                                                 |
|-----------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **translation_missing**           | This error is when a translation is present in the reference locale, but not present in one of the supported locales.                                                                                                                                       |
| **file_not_translated**           | This error is when an entire translations file from the reference locale is not present in one of the supported locales.                                                                                                                                    |
| **reference_translation_updated** | This error occurs when a locked translation of the reference locale does not match the current translation in the reference locale. This error helps you know what you have changed in your current branch, so it can be updated in the supported locales.  | 
| **no_reference_translation**      | This error occurs when a supported locale has a translation that cannot be found in the reference locale. This error is for eliminating dead translations.                                                                                                  |
| **incorrect_translation_type**    | This error occurs when translation keys match in the reference and supported locales, but their types are different                                                                                                                                         |


&nbsp;  
### 2)&nbsp;&nbsp;Clean Up Dead Translations
```bash
php artisan translations:clean de en es
```
The ```no_reference_translation``` errors likely indicate dead translations from removing keys in the reference locale, and forgetting to remove them in the supported locales. After you have inspected all of these errors and ensured they are in fact dead translations, you can run the clean command to remove all of these keys from your the supported locales.


&nbsp;  
### 3)&nbsp;&nbsp;Ignoring Errors
```bash
php artisan translations:ignore locale file key?
```

If there are any translations that do not need to be maintained in the supported locales, then those errors can be ignored. In the example output from ```#1```, it is likely the admin interface's translations do need to be translated.

As such, they can be ignored using: 
```bash
php artisan translations:ignore de admin
php artisan translations:ignore en admin
php artisan translations:ignore es admin
```

&nbsp;  
### 4)&nbsp;&nbsp;Fix-File Generation
```bash
php artisan translations:generate-fixes de en es
```
Now that all remaining errors require some action, fix-files can be generated containing all translations required to fix the errors in each locale. This file is intended to be sent to a translator, and returned in its current format. The files are in JSON since it is human readable, and reliably parseable.

By default, the files are generated with the naming pattern ```fixes-{locale}-{git-branch-name}.txt```. These files are saved in the configured ```fixes_dir```. The default is ```storage/translations/fixes```.

The following fix files would be generated using French as the reference locale.

```fixes-de-feature-xzy.txt```
```json
{
    "reference": "fr",
    "locale": "de",
    "files": [{
        "file": "common",
        "translations": {
            "company": "Compagnie"
        }
    }, {
        "file": "contact",
        "translations": {
            "email": "Adresse électronique",
            "phone": "Numéro de téléphone",
        }
    }]
}
```

```fixes-en-feature-xzy.txt```
```json
{
    "reference": "fr",
    "locale": "en",
    "files": [{
        "file": "contact",
        "translations": {
            "phone": "Numéro de téléphone"
        }
    }],
}
```

```fixes-es-feature-xzy.txt```
```json
{
    "reference": "fr",
    "locale": "es",
    "files": [{
        "file": "contact",
        "translations": {
            "first_name": "Prénom",
            "last_name": "Nom de famille",
            "email": "Adresse électronique",
            "phone": "Numéro de téléphone",
        }
    }],
}
```

&nbsp;  
### 5)&nbsp;&nbsp;Fixing Files
When the fix files have been completed and returned by the translator, you can place them in the configured ```fixed_dir```. The default is ```storage/translations/fixed```. Once the files have been placed in the directory you can run the following command.

```bash
php artisan translations:fix de en es
```

It is important to note, that this command will remove all translations with ```no_reference_translation``` errors from the supported locales.

Also, any translations that have ```reference_translations_updated``` and were included in the fix file, will have their lockfile updated with the current reference locale translation. 

&nbsp;  
### 6)&nbsp;&nbsp;Validating Translations
Now you can test whether there are any translation errors by running the following command:

```bash
php artisan translations:validate
```

If the output is ```Validation Passed```, then you can move on to the next step. I strongly recommend you add this command and ensure it passes in your ci/cd flow for deployments to production.

&nbsp;  
### 7)&nbsp;&nbsp;Locking Translations
If validation has failed due to ```reference_translation_updated``` errors, but you are satisfied with the state of the supported locales translations, you can lock the current state of the reference locale's translations by running the following command:

```
php artisan translations:lock
```

This will eliminate all ```reference_translation_updated``` errors.

&nbsp;  
### 8)&nbsp;&nbsp;Create PR and Merge
Once translations validation passes, you are ready to merge your code!

&nbsp;  
## Commands
### Validate
Signature: ```php artisan translations:validate locales?* --no-ignore```

Determine if there are any errors present in the specified locales. If no locales are provided all supported locales will be validated.

| Argument  | Required    | Description                                   | Example Values  |
|-----------|:-----------:|-----------------------------------------------|-----------------|
| locales   |      ✗      | The locales you want to ensure are valid.     | de, en, es, fr  |

| Options   | Description                                    | Example Values  |
|-----------|------------------------------------------------|-----------------|
| no-ignore | Do not filter ignored errors from the results. | --no-ignore     |

&nbsp;  
### Errors
Signature: ```php artisan translations:errors locales?* --no-ignore```

List any errors present in the specified locales.

| Argument  | Required    | Description                                   | Example Values  |
|-----------|:-----------:|-----------------------------------------------|-----------------|
| locales   |      ✗      | The locales you want to ensure are valid.     | de, en, es, fr  |

| Options   | Description                                    | Example Values  |
|-----------|------------------------------------------------|-----------------|
| no-ignore | Do not filter ignored errors from the results. | --no-ignore     |

&nbsp;  
### Clean
Signature: ```php artisan translations:clean locales?*```

Clean the dead translations -- errors with the message 'no_reference_translation' -- from the specified locales.

| Argument  | Required    | Description                                    | Example Values      |
|-----------|:-----------:|----------------------------------------------- |---------------------|
| locales   |      ✗      | The locales of the translations to be cleaned. |  de, en, es, fr     |


&nbsp;  
### Ignore
Signature: ```php artisan translations:ignore locale file key?```

Ignore a translations error. Omitting the key argument from the command will ignore all errors from the file. Ignoring an error allows the Validate command to pass if there are errors you do not wish to address.

| Argument  | Required    | Description                                                                                                                   | Example Values      |
|-----------|:-----------:|-------------------------------------------------------------------------------------------------------------------------------|---------------------|
| locale    |      ✓      | The locale of the translations to be ignored.                                                                                 | de, en, es, fr      |                                                           |
| file      |      ✓      | The file of the translation to be ignored. This should be specified as the path from the base of the locale's lang folder.    | path/to/file        |
| key       |      ✗      | The key to be ignored in [dot notation](https://laravel.com/docs/7.x/helpers#method-array-dot).                               | keys.to.translation |

&nbsp;  
### Unignore
Signature: ```php artisan translations:unignore locale file key?```

Unignore a translations error. Omitting the key argument from the command will unignore all errors from the file.

| Argument  | Required    | Description                                                                                                                     | Example Values      |
|-----------|:-----------:|---------------------------------------------------------------------------------------------------------------------------------|---------------------|
| locale    |      ✓      | The locale of the translations to be unignored.                                                                                 | de, en, es, fr      |                                                           |
| file      |      ✓      | The file of the translation to be unignored. This should be specified as the path from the base of the locale's lang folder.    | path/to/file        |
| key       |      ✗      | The key to be unignored in [dot notation](https://laravel.com/docs/7.x/helpers#method-array-dot).                               | keys.to.translation |

&nbsp;  
### Generate Fixes
Signature: ```php artisan translations:generate-fixes locales?*```

Generate fix files for the locales specified. If no locales are provided, all supported locales will have fix files generated. Fix files are generated to the configured ```fixes_dir```.

| Argument  | Required    | Description                                     | Example Values  |
|-----------|:-----------:|-------------------------------------------------|-----------------|
| locales   |      ✗      | The locales you want to generate fix files for. | de, en, es, fr  |

&nbsp;  
### Fix
Signature: ```php artisan translations:fix locales?*```

Fix the specified locales using the fix files. If no locales are provided, all supported locales will be fixed. The comand looks for the fix files in the the configured ```fixed_dir```.

| Argument  | Required    | Description                                         | Example Values  |
|-----------|:-----------:|-----------------------------------------------------|-----------------|
| locales   |      ✗      | The locales you want to be fix using the fix files. | de, en, es, fr  |

&nbsp;  
### Status
Signature: ```php artisan translations:status locales?*```

Get a complete listing of the status of the translations manager. The status shows the state of every translation file's error and ignore information for every local specified. If no locales are provided, all supported locales will have their status listed.

| Argument  | Required    | Description                                         | Example Values  |
|-----------|:-----------:|-----------------------------------------------------|-----------------|
| locales   |      ✗      | The locales you want included in the status.        | de, en, es, fr  |

&nbsp;  
## Testing

```bash
composer test
```

&nbsp;  
## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

&nbsp;  
## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

&nbsp;  
## Security

If you discover any security related issues, please email kfriars@gmail.com instead of using the issue tracker.

&nbsp;  
## Credits

- [Kurt Friars](https://github.com/kfriars)
- [All Contributors](../../contributors)

&nbsp;  
## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

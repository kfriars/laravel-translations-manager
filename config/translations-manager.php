<?php

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
    | 'formatter' is the class that will be used to write and parse the fix files.
    | The default format is a JSON format, since it is easily readable by humans
    | and reliably parseable. You can implement your own formatter as long as it
    | implements the FormatterContract.
    |
    | 'fix_name_format' is the way fix files will be named. The currently
    | supported formats are 'git' and 'date'.
    |
    | 'git'  format is 'fixes-{locale}-{git branch name}.txt'
    | 'date' format is 'fixes-{locale}-{Y-m-d}.txt'
    |
    */
    'formatter' => Kfriars\TranslationsManager\TranslationsFixesJSONFormatter::class,
    'fix_name_format' => 'git',
];

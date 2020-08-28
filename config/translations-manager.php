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
    | Translations Lock Files Path
    |--------------------------------------------------------------------------
    |
    | This is the folder where this package will store its version locks for
    | your translations files. This folder must not be in .gitignore to have this
    | package function correctly.
    |
    */

    'lock_dir' => storage_path('translations'.DIRECTORY_SEPARATOR.'lock'),

    /*
    |--------------------------------------------------------------------------
    | Translations Ignore File
    |--------------------------------------------------------------------------
    |
    | This is the file where this package will store which translations errors
    | will be ignored.
    |
    */

    'ignores' => storage_path('translations'.DIRECTORY_SEPARATOR.'ignores.php'),

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
    'fixes_dir' => storage_path('translations'.DIRECTORY_SEPARATOR.'fixes'),
    'fixed_dir' => storage_path('translations'.DIRECTORY_SEPARATOR.'fixed'),
    'fix_name_format' => 'git',
];

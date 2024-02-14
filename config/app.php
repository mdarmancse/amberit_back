<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [


    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Asia/Dhaka',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => 'file',
        // 'store' => 'redis',
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        OwenIt\Auditing\AuditingServiceProvider::class,
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    //New service account for GCP contents and images
    'cloud_assetBucketName' => 'tsports-cms-assets',
    'cloud_videoBucketName' => 'tsports-ugc-storage/demo',

    'google_privateKeyFileContent' => '{
        "type": "service_account",
        "project_id": "t-sports-361206",
        "private_key_id": "c68c8e0850578c88b328aa9a9c8047f06e1ef47a",
        "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCvrbP+/sFeTWQC\nxLsQ0dQYURnxGtMAvmuDnXOEcHbKxBoaZX2wx+eq+03XrG8Q65ksr7JSDYfpfcCj\nBBemBVHnkFugQaq9h1GqokwLgExJ4jBN/UnVTi/dDMWeZR1q05nir3nd5IhLMej9\nRA26LxGpiTlRMFbXl26vgrTewY8A4O7u6Ei/uidUTvHH2+XHjw65hqKqVXmbx97k\ni75KjFOQAsT8c+A+FL0YY+rYq5X2u34yduxFYjJ95ubxdI3KDep4ibU0lS+wUOEG\ndkFCkkOLjeU5KGgTojwQMoOyQIvpf6yA+rA0juf+Qcdalq8AEmkl4AF81/+Uo88+\nz3v+W4rHAgMBAAECggEAH3pxeSxuu5YEaJaVqlLevSizDEexLT3xh9jLnRyFvJlc\n0AQFfIqlUTbMYOvNzutYO/CTdC5mfwkAXnVaQ25KmcJ9GH8LuAAqOvfvxlPL16VG\n4IKVGzpySxqBwnr9xWl69mpY3NCZN1gPFmp+RzEt6gHjmk+eD+1bcOlrvUpTdGWN\niq0dKmk1hcG1DVdSz4VDeZEr5M8U+HSmSvfsdpWSsPR0PgtCKz76sBay90JYR5Ry\nJAgDuPgSHokAsqCMxa/tbNSIHaKyokqrY545osQZWqNqbdW/FSC0Izz1Ro99r8aa\nj5xwAJKK45kKhKbA+LHmulVyVWS1NwO1kGHrrW3q2QKBgQDU2LEssvt7NsqJiXOG\nar3LZuvtJzKC7BSb+11ZSKwcXs+dpsvJhtinnnM3t0AV/m+coolkNj2VXqtkDI/d\nT7DoOOJZQTpcSKeKQirAjUe4dxH7U7XKKT7wmu5VKY99bqPWTiRagHIZTq1RkNTQ\n5hHZ5AMszmYSTyqa45i4km3/+wKBgQDTS+UZv9NRjtRrDhGEYfuDDGS+UemGXCBg\ngIkS9kb/ZNdkaG/9+7pPAXElO9tYVrgafnZmDRkqre8eRbl4/cKCFyZeUrwdQ6mm\nrhxX5foTd8oAJhp1ZEcOxHx7BQX8HknmEIB+cq2dvcX82OF23iQ8wpbQ04a7mEAk\nBqPqb7VKpQKBgQCdIN8jDXqDin5YrIUBXTmBuBhBOeebKSdJw3Y1XGXah8+jJahZ\nvNeiXmHWZszDr9K7gm4E8dnitEG4mWF3UsZZmOzYUUQBLo2ojpI7FdE1h4lZzYFt\nnadePjHl4smQIqaGpadGvH3+2ssuZMoG8WGKRj5lzHUj3Repwo1S7J6SEwKBgQCA\nQCnQBkoaz+oJDOH/C4YhFg20I33uKs8QJBSPaOLRNAE1FdscO4XrXIGzyvApX4Gh\nuy7cZIjpCegA5OteEBDW63xDdk5uKNq5rCbSB2pSFLyMeJRIutQSl5rDBNqMHEpY\nlZ2YorzU6RJalb3Ma6ttIeNu32WBSjlsZIdrzeApbQKBgAEVKs84ONb9eVociiXc\neDTppQFEf5/GhfK4k6i79JU9JeI09UDjAtMoQIfxq1Yj/xvc4G2KJ/Wjrx5HW7q6\nUjuW/GW+dPgEHooVz2djI51IKVeBkJ7z/y1u8VNA0ZHEvSu1APab0I5zV5ndk3zN\nQC4IMS0zTC79eyfQSWRg4tue\n-----END PRIVATE KEY-----\n",
        "client_email": "tsports-asset-cms@t-sports-361206.iam.gserviceaccount.com",
        "client_id": "112365188860894298982",
        "auth_uri": "https://accounts.google.com/o/oauth2/auth",
        "token_uri": "https://oauth2.googleapis.com/token",
        "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
        "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/tsports-asset-cms%40t-sports-361206.iam.gserviceaccount.com",
        "universe_domain": "googleapis.com"
      }',



    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
    ])->toArray(),

];

<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit249e4569acdfd58c47dbd7d4881b68ae
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'PayPal' => 
            array (
                0 => __DIR__ . '/..' . '/paypal/rest-api-sdk-php/lib',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit249e4569acdfd58c47dbd7d4881b68ae::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit249e4569acdfd58c47dbd7d4881b68ae::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit249e4569acdfd58c47dbd7d4881b68ae::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}

<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit00116c29cef5b046903fd990a91a2556
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'DataSync\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'DataSync\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit00116c29cef5b046903fd990a91a2556::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit00116c29cef5b046903fd990a91a2556::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}

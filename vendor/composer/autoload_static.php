<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit80dd95ede5b4fa2c29b44f076917b024
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit80dd95ede5b4fa2c29b44f076917b024::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit80dd95ede5b4fa2c29b44f076917b024::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit80dd95ede5b4fa2c29b44f076917b024::$classMap;

        }, null, ClassLoader::class);
    }
}

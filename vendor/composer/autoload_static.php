<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit322c269d868f0e9a6ee31cbfa06f1bc8
{
    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'LZCompressor\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'LZCompressor\\' => 
        array (
            0 => __DIR__ . '/..' . '/nullpunkt/lz-string-php/src/LZCompressor',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit322c269d868f0e9a6ee31cbfa06f1bc8::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit322c269d868f0e9a6ee31cbfa06f1bc8::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit322c269d868f0e9a6ee31cbfa06f1bc8::$classMap;

        }, null, ClassLoader::class);
    }
}

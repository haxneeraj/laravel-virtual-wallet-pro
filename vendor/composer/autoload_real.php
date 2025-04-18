<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit367a4e0eca32b0378741a995cea55f0a
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit367a4e0eca32b0378741a995cea55f0a', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit367a4e0eca32b0378741a995cea55f0a', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit367a4e0eca32b0378741a995cea55f0a::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}

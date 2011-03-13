<?php

/**
 * Lazy-loading class for teh PHPTracker library.
 */
class PHPTracker_Autoloader
{
    /**
     * Registers PHPTracker_Autoloader as an SPL autoloader.
     *
     * Should be called before starting to use the library.
     */
    static public function register()
    {
        // We autoload while unserializing an unknown object too.
        ini_set( 'unserialize_callback_func', 'spl_autoload_call' );
        spl_autoload_register( array( new self, 'autoload' ) );
    }

    /**
     * Handles autoloading of classes.
     *
     * Only loads classes with namees starting with 'PHPTracker'.
     * Uses PEAR-style naming conventions.
     *
     * @param string $class A class name inside the PHPTracker package.
     */
    static public function autoload( $class )
    {
        if ( 0 !== strpos( $class, 'PHPTracker' ) )
        {
            return;
        }

        if ( file_exists( $file = dirname( __FILE__ ) . '/../' . str_replace( '_', '/', $class ) . '.php' ) )
        {
            require $file;
        }
    }
}

<?php

/**
 * Logger class to serve as stupid interface of logging - no data is saved.
 *
 * @package PHPTracker
 * @subpackage Logger
 */
class PHPTracker_Logger_Blackhole implements PHPTracker_Logger_Interface
{
    /**
     * Implementing constructor, doing nothing.
     *
     * @param PHPTracker_Config_Interface $config
     */
    public function  __construct( PHPTracker_Config_Interface $config = null )
    {
    }

    /**
     * Implementing message logging, doing nothing.
     *
     * @param type $message
     */
    public function logMessage( $message )
    {
    }

    public function logError( $message )
    {
    }
}

?>

<?php

/**
 * Interface used to inject config values to some classes of library.
 *
 * Feel free to implement your own logic for configs or use PHPTracker_Config_Simple.
 */
interface PHPTracker_Config_Interface
{
    /**
     * Gets one config value for a config key.
     *
     * @param string $config_name The name (key) of the config value to return.
     * @param boolean $needed If the config key is missing and $needed is true, this MUST throw PHPTracker_Config_Error_Missing.
     * @param mixed $default If the config key is missing and $needed is false, this MUST return $default.
     * @throws PHPTracker_Config_Error_Missing When the config value is required and missing.
     * @return mixed The config value for the key requested (of its default).
     */
    public function get( $config_name, $needed = true, $default = null );

    /**
     * Gets mutiple config values for a config keys.
     *
     * The $default parameter might be array OR a general value (not array).
     * If $default is an array, the default value of a key is an item in $default
     * with the same index that the key appers in the $config_names.
     * If it's not array, it's used as default value for all keys.
     *
     * @param array $config_names The name (key) of the config value to return.
     * @param boolean $needed If a config key is missing and $needed is true, this MUST throw PHPTracker_Config_Error_Missing.
     * @param mixed $default If a config key is missing and $needed is false, this MUST return the according $default.
     * @throws PHPTracker_Config_Error_Missing When a config value is required and missing.
     * @return mixed The config values for the keys requested (of its default) in the same order of the request.
     */
    public function getMulti( array $config_names, $needed = true, $defaults = null );
}

?>

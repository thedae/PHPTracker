<?php

/**
 * One piece of a decoded bencode container. Could be dictionary or list.
 */
abstract class PHPTracker_Bencode_Value_Container extends PHPTracker_Bencode_Value_Abstract
{
    /**
     * Intializing the object with its parsed value.
     *
     * Value is iterated and its values (and keys) are getting contained by the object.
     *
     * @param array $value
     */
    public function __construct( array $value = null )
    {
        $this->value = array();

        if ( !isset( $value ) )
        {
            return;
        }

        if ( PHPTracker_Bencode_Builder::isDictionary( $value ) )
        {
            foreach( $value as $key => $sub_value )
            {
                $this->contain( $sub_value, new PHPTracker_Bencode_Value_String( $key ) );
            }
        }
        else
        {
            foreach( $value as $sub_value )
            {
                $this->contain( $sub_value );
            }
        }
    }

    /**
     * Represent the value of the object as PHP arrays and scalars.
     */
    public function represent()
    {
        $representation = array();
        foreach ( $this->value as $key => $sub_value )
        {
            $representation[$key] = $sub_value->represent();
        }
        return $representation;
    }

    /**
     * Adds an item to the list/dictionary.
     *
     * @param PHPTracker_Bencode_Value_Abstract $sub_value
     * @param PHPTracker_Bencode_Value_String $key Only used for dictionaries.
     */
    abstract public function contain( PHPTracker_Bencode_Value_Abstract $sub_value, PHPTracker_Bencode_Value_String $key = null );
}

?>
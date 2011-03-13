<?php

/**
 * Decoded bencode string, representing an ordered set of bytes.
 */
class PHPTracker_Bencode_Value_String extends PHPTracker_Bencode_Value_Abstract
{
    /**
     * Intializing the object with its parsed value.
     *
     * @throws PHPTracker_Bencode_Error_InvalidType In the value is not a string.
     * @param string $value
     */
    public function __construct( $value )
    {
        if ( !is_string( $value ) )
        {
            throw new PHPTracker_Bencode_Error_InvalidType( "Invalid string value: $value" );
        }
        $this->value = $value;
    }

    /**
     * Convert the object back to a bencoded string when used as string.
     */
    public function __toString()
    {
        return strlen( $this->value ) . ":" . $this->value;
    }

    /**
     * Represent the value of the object as PHP scalar.
     */
    public function represent()
    {
        return $this->value;
    }
}

?>

<?php

/**
 * Decoded bencode dictionary, consisting of key-value pairs.
 */
class PHPTracker_Bencode_Value_Dictionary extends PHPTracker_Bencode_Value_Container
{
    /**
     * Adds an item to the dictionary.
     *
     * @param PHPTracker_Bencode_Value_Abstract $sub_value
     * @param PHPTracker_Bencode_Value_String $key
     */
    public function contain( PHPTracker_Bencode_Value_Abstract $sub_value, PHPTracker_Bencode_Value_String $key = null )
    {
        if ( !isset( $key ) )
        {
            throw new PHPTracker_Bencode_Error_InvalidType( "Invalid key value for dictionary: $sub_value" );
        }
        if ( isset( $this->value[$key->value] ) )
        {
            throw new PHPTracker_Bencode_Error_InvalidValue( "Duplicate key in dictionary: $key->value" );
        }
        $this->value[$key->value] = $sub_value;
    }

    /**
     * Convert the object back to a bencoded string when used as string.
     */
    public function __toString()
    {
        // All keys must be byte strings and must appear in lexicographical order.
        ksort( $this->value );

        $string_represent = "d";
        foreach ( $this->value as $key => $sub_value )
        {
            $key = new PHPTracker_Bencode_Value_String( $key );
            $string_represent .=  $key . $sub_value;
        }
        return $string_represent . "e";
    }
}

?>

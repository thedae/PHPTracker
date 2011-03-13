<?php

/**
 * Decoded bencode list, consisting of mutiple values.
 */
class PHPTracker_Bencode_Value_List extends PHPTracker_Bencode_Value_Container
{
    /**
     * Adds an item to the list.
     *
     * @param PHPTracker_Bencode_Value_Abstract $sub_value
     * @param PHPTracker_Bencode_Value_String $key Not used here.
     */
    public function contain( PHPTracker_Bencode_Value_Abstract $sub_value, PHPTracker_Bencode_Value_String $key = null )
    {
        $this->value[] = $sub_value;
    }

    /**
     * Convert the object back to a bencoded string when used as string.
     */
    public function __toString()
    {
        $string_representaiton = "l";
        foreach ( $this->value as $key => $sub_value )
        {
            $string_representaiton .= $sub_value;
        }
        return $string_representaiton . "e";
    }
}

?>

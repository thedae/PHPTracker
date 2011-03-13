<?php

/**
 * Test class for PHPTracker_Bencode_Parser.
 */
class PHPTracker_Bencode_ParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider parseableStrings
     */
    public function testParse( $string_to_parse )
    {
       $object = new PHPTracker_Bencode_Parser( $string_to_parse );

       // Parse method returns PHPTracker_Bencode_Value_Abstract objects, and they are converted back to string by calling __toString.
       $this->assertEquals( $string_to_parse, $object->parse() . '' );
    }

    public static function parseableStrings()
    {
        return array(
            array( 'i123e' ), // Integer.
            array( 'i-55e' ), // Integer.
            array( '5:funny' ), // String.
            array( 'li123e5:funnye' ), // List.
            array( 'd5:funnyi555e4:test2:OKe' ), // Dictionary.
            array( 'd7:Address17:1 Time Square, NY6:Phonesli123456e10:0012567890ee' ), // Complex.
        );
    }

    /**
     * @expectedException PHPTracker_Bencode_Error_Parse
     */
    public function testParseErrorInvalidValue()
    {
        $object = new PHPTracker_Bencode_Parser( 'something stupid' );
        $object->parse();
    }

    /**
     * @expectedException PHPTracker_Bencode_Error_Parse
     */
    public function testParseErrorUnstructured()
    {
        $object = new PHPTracker_Bencode_Parser( 'i456ei222e' );
        $object->parse();
    }

    /**
     * @expectedException PHPTracker_Bencode_Error_Parse
     */
    public function testParseErrorIncompleteDictionary()
    {
        $object = new PHPTracker_Bencode_Parser( 'd3:foo3:bar3:baze' );
        $object->parse();
    }

    /**
     * @expectedException PHPTracker_Bencode_Error_Parse
     */
    public function testParseErrorUnbalancedEnding()
    {
        $object = new PHPTracker_Bencode_Parser( 'lee' );
        $object->parse();
    }

    /**
     * @expectedException PHPTracker_Bencode_Error_Parse
     */
    public function testParseErrorMissingIntegerEnding()
    {
        $object = new PHPTracker_Bencode_Parser( 'i222' );
        $object->parse();
    }

    /**
     * @expectedException PHPTracker_Bencode_Error_Parse
     */
    public function testParseErrorInvalidStringLength()
    {
        $object = new PHPTracker_Bencode_Parser( '12abc:string' );
        $object->parse();
    }

    /**
     * @expectedException PHPTracker_Bencode_Error_Parse
     */
    public function testParseErrorMissingStringColon()
    {
        $object = new PHPTracker_Bencode_Parser( '123' );
        $object->parse();
    }

    /**
     * @expectedException PHPTracker_Bencode_Error_Parse
     */
    public function testParseErrorUnendedContainer()
    {
        $object = new PHPTracker_Bencode_Parser( 'ld2:AB2:CDe' );
        $object->parse();
    }

}

?>

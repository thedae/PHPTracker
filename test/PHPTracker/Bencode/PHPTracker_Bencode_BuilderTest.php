<?php

/**
 * Test class for PHPTracker_Bencode_Builder.
 */
class PHPTracker_Bencode_BuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider buildableInputs
     */
    public function testBuild( $input )
    {
       // Parse method returns PHPTracker_Bencode_Value_Abstract objects, and they
       // should return PHP representation of themselves when calling represent.
       $this->assertSame( $input, PHPTracker_Bencode_Builder::build( $input )->represent() );
    }

    public static function buildableInputs()
    {
        return array(
            array( 12345 ), // Integer.
            array( 'foobar' ), // String.
            array( array( 'foo', 'bar', 'baz' ) ), // List.
            array( array( 'foo' => 'bar', 'baz' => 'bat' ) ), // Dictionary.
            array( array( 'foo' => array( 'baz', 'bat' ), 'baz' => 123 ) ), // Complex.
        );
    }

    /**
     * @expectedException PHPTracker_Bencode_Error_Build
     */
    public function testBuildErrorFloat()
    {
        PHPTracker_Bencode_Builder::build( 1.1111 );
    }

    /**
     * @expectedException PHPTracker_Bencode_Error_Build
     */
    public function testBuildErrorObject()
    {
        PHPTracker_Bencode_Builder::build( (object) array( 'attribute' => 'something' ) );
    }

}

?>

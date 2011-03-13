<?php

/**
 * Test class for PHPTracker_Bencode_Value_String.
 */
class PHPTracker_Bencode_Value_StringTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PHPTracker_Bencode_Value_String
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PHPTracker_Bencode_Value_String( 'abcdef' );
    }

    /**
     * Running testcase test__toString().
     */
    public function test__toString()
    {
        $this->assertSame( '6:abcdef', $this->object . '' );
    }

    /**
     * Running testcase testRepresent().
     */
    public function testRepresent()
    {
        $this->assertSame( 'abcdef', $this->object->represent() );
    }

    /**
     * @expectedException PHPTracker_Bencode_Error_InvalidType
     */
    public function testInvalidValue()
    {
        new PHPTracker_Bencode_Value_String( array() );
    }
}

?>

<?php

/**
 * Test class for PHPTracker_Bencode_Value_Integer.
 */
class PHPTracker_Bencode_Value_IntegerTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PHPTracker_Bencode_Value_Integer
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PHPTracker_Bencode_Value_Integer( 111 );
    }

    /**
     * Running testcase test__toString().
     */
    public function test__toString()
    {
        $this->assertSame( 'i111e', $this->object . '' );
    }

    /**
     * Running testcase testRepresent().
     */
    public function testRepresent()
    {
        $this->assertSame( 111, $this->object->represent() );
    }

    /**
     * @expectedException PHPTracker_Bencode_Error_InvalidType
     */
    public function testInvalidValue()
    {
         new PHPTracker_Bencode_Value_Integer( 'abcdef' );
    }

}

?>

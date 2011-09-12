<?php

/**
 * Test class for PHPTracker_Bencode_Value_List.
 */
class PHPTracker_Bencode_Value_ListTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PHPTracker_Bencode_Value_List
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PHPTracker_Bencode_Value_List( array(
            new PHPTracker_Bencode_Value_Integer( 12 ),
            new PHPTracker_Bencode_Value_String( 'abc' ),
        ) );
    }

    /**
     * Running testcase test__toString().
     */
    public function test__toString()
    {
        $this->assertSame( 'li12e3:abce', $this->object . '' );
    }

    /**
     * Running testcase testRepresent().
     */
    public function testRepresent()
    {
        $this->assertSame( array( 12, 'abc' ), $this->object->represent() );
    }

}

?>

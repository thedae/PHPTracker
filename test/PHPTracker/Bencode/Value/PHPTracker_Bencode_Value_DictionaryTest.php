<?php

/**
 * Test class for PHPTracker_Bencode_Value_Dictionary.
 */
class PHPTracker_Bencode_Value_DictionaryTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PHPTracker_Bencode_Value_Dictionary
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PHPTracker_Bencode_Value_Dictionary( array(
            'b' => new PHPTracker_Bencode_Value_Integer( 12 ),
            'a' => new PHPTracker_Bencode_Value_String( 'abc' ),
        ) );
    }

    /**
     * Running testcase test__toString().
     */
    public function test__toString()
    {
        // Keys are ABC ordered.
        $this->assertSame( 'd1:a3:abc1:bi12ee', $this->object . '' );
    }

    /**
     * Running testcase testRepresent().
     */
    public function testRepresent()
    {
        $this->assertSame( array( 'b' => 12, 'a' => 'abc' ), $this->object->represent() );
    }
    
    /**
     * @expectedException PHPTracker_Bencode_Error_InvalidValue
     */
    public function testDuplicate()
    {
        $this->object->contain( new PHPTracker_Bencode_Value_String( 'xxx' ), new PHPTracker_Bencode_Value_String( 'a' ) );
    }

    /**
     * @expectedException PHPTracker_Bencode_Error_InvalidType
     */
    public function testNoKey()
    {
        $this->object->contain( new PHPTracker_Bencode_Value_String( 'xxx' ) );
    }

}

?>

<?php

/**
 * Test class for PHPTracker_Config_Simple.
 */
class PHPTracker_Config_SimpleTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PHPTracker_Config_Simple
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PHPTracker_Config_Simple( array(
            'key1' => 'value1',
            'key2' => 'value2',
        ) );
    }

    /**
     * Running testcase testGet().
     */
    public function testGet()
    {
        $this->assertSame( 'value1', $this->object->get( 'key1' ) );
        $this->assertSame( 'value2', $this->object->get( 'key2' ) );
        // Default value.
        $this->assertSame( 'value3', $this->object->get( 'key3', false, 'value3' ) );
        $this->assertSame( null, $this->object->get( 'key4', false, null ) );
        $this->assertSame( null, $this->object->get( 'key5', false ) );
    }

    /**
     * Running testcase testGetMulti().
     */
    public function testGetMulti()
    {
        $this->assertSame( array( 'value1', 'value2' ), $this->object->getMulti( array( 'key1', 'key2' ) ) );
        $this->assertSame( array( 'default', 'value1', 'value2', 'default' ), $this->object->getMulti( array( 'key0', 'key1', 'key2', 'key3' ), false, 'default' ) );
        $this->assertSame( array( 'value1', 'default2' ), $this->object->getMulti( array( 'key1', 'key3' ), false, array( 'default1', 'default2' ) ) );
    }

    /**
     * @expectedException PHPTracker_Config_Error_Missing
     */
    public function testMissing()
    {
        $this->object->get( 'key6' );
    }
    
    /**
     * @expectedException PHPTracker_Config_Error_Missing
     */
    public function testMissing2()
    {
        $this->object->get( 'key7', true );
    }
    
    /**
     * @expectedException PHPTracker_Config_Error_Missing
     */
    public function testMissingMulti()
    {
        $this->object->getMulti( array( 'key1', 'key8' ) );
    }
}

?>

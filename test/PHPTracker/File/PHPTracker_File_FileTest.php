<?php

/**
 * Test class for PHPTracker_File_File.
 */
class PHPTracker_File_FileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPTracker_File_File
     */
    protected $object;
    
    protected $original_path;

    const TEST_DATA = 'abcdefghijklmnopqrstuvwxyz';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->original_path = sys_get_temp_dir() . 'test_' . md5( uniqid() );
        file_put_contents( $this->original_path, self::TEST_DATA );

        $this->object = new PHPTracker_File_File( $this->original_path );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        // We have to desctory the object to close open handles.
        unset( $this->object );
        // Then we can delete the test file.
        if ( file_exists( $this->original_path ) )
        {
            unlink( $this->original_path );
        }
    }

    /**
     * Running testcase test__toString().
     */
    public function test__toString()
    {
        $this->assertEquals( $this->original_path, $this->object . '' );
    }

    /**
     * Running testcase testSize().
     */
    public function testSize()
    {
        $this->assertEquals( strlen( self::TEST_DATA ), $this->object->size() );
    }

    /**
     * @expectedException PHPTracker_File_Error_NotExits
     */
    public function testNonExistent()
    {
        $non_existent = new PHPTracker_File_File( sys_get_temp_dir() . '/no_way_this_exists' );
    }

    /**
     * Running testcase testGetHashesForPieces().
     */
    public function testGetHashesForPieces()
    {
        // Generating test hash for 1 byte length pieces.
        $expected_hash = '';
        for ( $i = 0; $i < strlen( self::TEST_DATA ); ++$i )
        {
            $expected_hash .= sha1( substr( self::TEST_DATA, $i, 1 ), true );
        }

        $this->assertSame( $expected_hash, $this->object->getHashesForPieces( 1 ) );
    }

    /**
     * @expectedException PHPTracker_File_Error_Unreadable
     */
    public function testGetHashesForPiecesUnreadable()
    {
        unlink( $this->original_path );
        $this->object->getHashesForPieces( 10 );
    }

    public function testBasename()
    {
        $this->assertEquals( basename( $this->original_path ), $this->object->basename() );
    }

    public function testReadBlock()
    {
        $this->assertEquals( substr( self::TEST_DATA, 2, 2 ), $this->object->readBlock( 2, 2 ) );
    }

}

?>

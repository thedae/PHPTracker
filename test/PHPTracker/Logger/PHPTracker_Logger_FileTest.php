<?php

/**
 * Test class for PHPTracker_Logger_File.
 */
class PHPTracker_Logger_FileTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var PHPTracker_Logger_File
     */
    protected $object;
    
    protected $log_path_messages;
    protected $log_path_errors;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->log_path_messages    = sys_get_temp_dir() . 'test_' . md5( uniqid() );
        $this->log_path_errors     = sys_get_temp_dir() . 'test_' . md5( uniqid() );

        $this->object = new PHPTracker_Logger_File( new PHPTracker_Config_Simple( array(
            'file_path_messages'    => $this->log_path_messages,
            'file_path_errors'      => $this->log_path_errors,
        ) ) );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        // Delete the test file.
        if ( file_exists( $this->log_path_messages ) )
        {
            unlink( $this->log_path_messages );
        }
        if ( file_exists( $this->log_path_errors ) )
        {
            unlink( $this->log_path_errors );
        }
    }

    /**
     * Running testLogMessage().
     */
    public function testLogMessage()
    {
        $this->object->logMessage( "I'm a message with a tricky\nnew line." );
        $log_file_contents = file_get_contents( $this->log_path_messages );
        
        $this->assertLogFormat( $log_file_contents );
        // Checking that message is entirely saved and new lines are escaped.
        $this->assertContains( "I'm a message with a tricky\\nnew line.", $log_file_contents );
    }

    /**
     * Running testLogError().
     */
    public function testLogError()
    {
        $this->object->logError( "I'm a message with a tricky\nnew line." );
        $log_file_contents = file_get_contents( $this->log_path_errors );
        
        $this->assertLogFormat( $log_file_contents );
        // Checking that message is entirely saved and new lines are escaped.
        $this->assertContains( "I'm a message with a tricky\\nnew line.", $log_file_contents );
        $this->assertContains( "[ERROR]", $log_file_contents );
    }
    
    protected function assertLogFormat( $log_message )
    {
        // If we have a timestamp.
        $this->assertRegexp( '/
            ^\[         # Opening square bracket for timestamp, in the beginning.
            \d{4}       # Year.
            \-          # Dash after year.
            \d{2}       # Month.
            \-          # Dash after month.
            \d{2}       # Day.
            \x20        # Space after date.
            [0-2]?\d    # Hour.
            \:          # Colon after hour.
            [0-5]?\d    # Minute.
            \:          # Colon after hour.
            [0-5]?\d    # Seconds.
            \]          # Closing bracket.
        /x', $log_message );
        
        // Should end with new line.
        $this->assertRegexp( '/\n$/', $log_message );
    }
}

?>

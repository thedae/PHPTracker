<?php

/**
 * Array-like proxy used to iterate through a MySQL resultset saving some memory.
 *
 * @package PHPTracker
 * @subpackage Persistence
 */
class PHPTracker_Persistence_MysqlResult implements ArrayAccess, Iterator, Countable
{
    /**
     * Mysqli result object.
     *
     * @var MySQLi_Result
     */
    protected $result;

    /**
     * Number of rows in the resultset.
     *
     * @var integer
     */
    protected $num_rows = 0;

    /**
     * Pointer of the currently sought row in the resultset.
     *
     * @var integer
     */
    protected $row_pointer = 0;

    /**
     * Initializing object with the mysqli result object.
     *
     * @param MySQLi_Result $result
     */
    public function __construct( MySQLi_Result $result )
    {
        $this->result       = $result;
        $this->num_rows     = $this->result->num_rows;
    }

    /**
     * ArrayAccess method, tells if an array index exists.
     *
     * @param integer $offset
     * @return boolean
     */
    public function offsetExists( $offset )
    {
        return ( $offset >= 0 ) && ( $offset < $this->num_rows );
    }

    /**
     * ArrayAccess method to set offset. Our array is read-only, so this method issues a warning.
     *
     * @param index $offset
     * @param mixed $value
     */
    public function offsetSet( $offset, $value )
    {
        trigger_error( 'Cannot set keys of read-only array: ' . __CLASS__, E_USER_WARNING );
    }

    /**
     * ArrayAccess method
     *
     * @param <type> $offset
     * @param <type> $step
     * @return <type>
     */
    public function offsetGet( $offset, $step = false )
    {
        if ( $this->offsetExists( $offset ) )
        {
            $this->jumpTo( $offset );
            $return = $this->result->fetch_array();
            ++$this->row_pointer;

            if ( $step )
            {
                $this->jumpTo( $offset );
            }

            return $return;
        }
    }

    /**
     * ArrayAccess method to unset offset. Our array is read-only, so this method issues a warning.
     *
     * @param integer $offset
     */
    public function offsetUnset( $offset )
    {
        trigger_error( 'Cannot unset keys of read-only array: ' . __CLASS__, E_USER_WARNING );
    }

    /**
     * Seek the resultset to a certain index.
     *
     * @param integer $offset Offset to seek to.
     */
    protected function jumpTo( $offset )
    {
        if ( $offset != $this->row_pointer )
        {
            if ( $this->offsetExists( $offset ) )
            {
                $this->result->data_seek( $offset );
            }
            $this->row_pointer = $offset;
        }
    }

    /**
     * Iterator method. Rewinds array to the beginning.
     */
    public function rewind()
    {
        $this->jumpTo( 0 );
    }

    /**
     * Iterator method. Returns the current row.
     *
     * @return array
     */
    public function current()
    {
        return $this->offsetGet( $this->row_pointer, true );
    }

    /**
     * Iterator method. Returns the current key.
     *
     * @return integer
     */
    public function key()
    {
        return $this->row_pointer;
    }

    /**
     * Iterator method. Drives the array to the next key.
     */
    public function next()
    {
        $this->jumpTo( $this->row_pointer + 1 );
    }

    /**
     * Iterator method. Checks if the current key is valid.
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->offsetExists( $this->row_pointer );
    }

    /**
     * Countable method. Returns the number of rows in the resultset.
     *
     * @return integer
     */
    public function count()
    {
        return $this->num_rows;
    }

    /**
     * If the array-like object is not an options and you really need the whole
     * resultset in the memory, you can convert it to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $return = array();
        foreach ( $this as $row )
        {
            $return[] = $row;
        }
        return $return;
    }
}
?>

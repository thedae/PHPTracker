<?php

class PHPTracker_Persistence_Mysql implements PHPTracker_Persistence_Interface, PHPTracker_Persistence_ResetWhenForking
{
    /**
     * Config instance for this class containing database connection data.
     *
     * @var PHPTracker_Config_Interface
     */
    protected $config;

    /**
     * Lazy-initialized database connection.
     *
     * @var mysqli
     */
    protected $connection;

    /**
     * Setting up object instance with the config.
     *
     * @param PHPTracker_Config_Interface $config
     */
    public function __construct( PHPTracker_Config_Interface $config )
    {
        $this->config = $config;
    }

    /**
     * Save all accessible keys of a PHPTracker_Torrent object to be able to recreate it.
     *
     * Use info_hash property as unique key and overwrite attributes when saved
     * multiple times with the same info hash.
     *
     * @param PHPTracker_Torrent $torrent
     */
    public function saveTorrent( PHPTracker_Torrent $torrent )
    {
        $sql = <<<SQL
INSERT INTO
    `phptracker_torrents`
SET
    `info_hash`     = :info_hash,
    `length`        = :length,
    `pieces_length` = :pieces_length,
    `pieces`        = :pieces,
    `name`          = :name,
    `path`          = :path
ON DUPLICATE KEY UPDATE
    `info_hash`     = VALUES( `info_hash` ),
    `length`        = VALUES( `length` ),
    `pieces_length` = VALUES( `pieces_length` ),
    `pieces`        = VALUES( `pieces` ),
    `name`          = VALUES( `name` ),
    `path`          = VALUES( `path` )
SQL;

        $this->query( $sql, array(
            ':info_hash'         => $torrent->info_hash,
            ':length'            => $torrent->length,
            ':pieces_length'     => $torrent->size_piece,
            ':pieces'            => $torrent->pieces,
            ':name'              => $torrent->name,
            ':path'              => $torrent->file_path,
        ) );
    }

    /**
     * Given a 20 bytes info hash, return an intialized PHPTracker_Torrent object.
     *
     * Must return null if the info hash is not found.
     *
     * @param string $info_hash
     * @return PHPTracker_Torrent
     */
    public function getTorrent( $info_hash )
    {
        $sql = <<<SQL
SELECT
    `info_hash`,
    `length`,
    `pieces_length`,
    `pieces`,
    `name`,
    `path`
FROM
    `phptracker_torrents`
WHERE
    `info_hash` = :info_hash
    AND
    `status` = 'active'
SQL;

        $results = $this->query( $sql, array(
            ':info_hash'         => $info_hash,
        ) );

        if ( 1 == count( $results ) )
        {
            return new PHPTracker_Torrent(
                new PHPTracker_File_File( $results[0]['path'] ),
                $results[0]['pieces_length'],
                $results[0]['path'],
                $results[0]['name'],
                $results[0]['length'],
                $results[0]['pieces'],
                $results[0]['info_hash']
            );
        }
        return null;
    }

    /**
     * Saves peer announcement from a client.
     *
     * Majority of the parameters of this method come from GET.
     *
     * @param string $info_hash 20 bytes info hash of the announced torrent.
     * @param string $peer_id 20 bytes peer ID of the announcing peer.
     * @param string $ip Dotted IP address of the client.
     * @param integer $port Port number of the client.
     * @param integer $downloaded Already downloaded bytes.
     * @param integer $uploaded Already uploaded bytes.
     * @param integer $left Bytes left to download.
     * @param string $status Can be complete, incomplete or NULL. Incomplete is default for new rows. If once set to complete, NULL does not set it back on update.
     * @param integer $ttl Time to live in seconds meaning the time after we should consider peer offline (if no more updates come).
     */
    public function saveAnnounce( $info_hash, $peer_id, $ip, $port, $downloaded, $uploaded, $left, $status, $ttl )
    {
        $sql = <<<SQL
INSERT INTO
    `phptracker_peers`
SET
    `info_hash`           = :info_hash,
    `peer_id`             = :peer_id,
    `ip_address`          = INET_ATON( :ip ),
    `port`                = :port,
    `bytes_downloaded`    = :downloaded,
    `bytes_uploaded`      = :uploaded,
    `bytes_left`          = :left,
    `status`              = COALESCE( :status, 'incomplete' ),
    `expires`             = CURRENT_TIMESTAMP + INTERVAL :interval SECOND
ON DUPLICATE KEY UPDATE
    `ip_address`          = VALUES( `ip_address` ),
    `port`                = VALUES( `port` ),
    `bytes_downloaded`    = VALUES( `bytes_downloaded` ),
    `bytes_uploaded`      = VALUES( `bytes_uploaded` ),
    `bytes_left`          = VALUES( `bytes_left` ),
    `status`              = COALESCE( :status, `status` ),
    `expires`             = VALUES( `expires` )
SQL;

        $this->query( $sql, array(
            ':info_hash'    => $info_hash,
            ':peer_id'      => $peer_id,
            ':ip'           => $ip,
            ':port'         => $port,
            ':downloaded'   => $uploaded,
            ':uploaded'     => $downloaded,
            ':left'         => $left,
            ':status'       => $status,
            ':interval'     => isset( $ttl ) ? $ttl : null,
        ) );
    }

    /**
     * Return all the inf_hashes and lengths of the active arrays.
     *
     * @return array An array of arrays having keys 'info_hash' and 'length' accordingly.
     */
    public function getAllInfoHash()
    {
        $sql = <<<SQL
SELECT
    `info_hash`,
    `length`
FROM
    `phptracker_torrents`
WHERE
    `status` = 'active'
SQL;

        return $this->query( $sql, array() );
    }

    /**
     * Gets all the active peers for a torrent.
     *
     * Only considers peers which are not expired (see TTL).
     * Depending on the $compact flag, returns:
     *
     * A.
     * array(
     *  array(
     *      'peer_id' => ... // ID of the peer, if $no_peer_id is false.
     *      'ip' => ... // Dotted IP address of the peer.
     *      'port' => ... // Port number of the peer.
     *  )
     * )
     *
     * B.
     * Nx6 bytes, where each first 4 bytes represent IP address in big-endian long
     * and each last 2 bytes represent port number in big-endian short.
     *
     * @param string $info_hash Info hash of the torrent.
     * @param string $peer_id Peer ID to exclude (peer ID of the client announcing).
     * @param boolean $compact If true, compact peer list format is used.
     * @param bookean $no_peer_id If true, peer is is ommitted from non-compact peer list.
     * @return mixed
     */
    public function getPeers( $info_hash, $peer_id, $compact = false, $no_peer_id = false )
    {
        $sql = <<<SQL
SELECT
    `peer_id`,
    INET_NTOA( `ip_address` ) AS 'ip_address',
    `port`
FROM
    `phptracker_peers`
WHERE
    `info_hash`           = :info_hash
    AND
    `peer_id`             != :peer_id
    AND
    (
        `expires` IS NULL
        OR
        `expires` > CURRENT_TIMESTAMP
    )
SQL;

        $results =  $this->query( $sql, array(
            ':info_hash'    => $info_hash,
            ':peer_id'      => $peer_id,
        ) );

        if ( $compact )
        {
            $return = "";
            foreach ( $results as $row )
            {
                // We cannot select IP long from MySQL directly on 32 bits systems, because in PHP integers are signed.
                $return .= pack( 'N', ip2long( $row['ip_address'] ) );
                $return .= pack( 'n', intval( $row['port'] ) );
            }
        }
        else
        {
            $return = array();
            foreach ( $results as $row )
            {
                $peer = array(
                    'ip'        => $row['ip_address'],
                    'port'      => $row['port'],
                );
                if ( !$no_peer_id )
                {
                    $peer['peer id'] = $row['peer_id'];
                }
                $return[] = $peer;
            }
        }
        return $return;
    }

    /**
     * Returns statistics of seeders and leechers of a torrent.
     *
     * Only considers peers which are not expired (see TTL).
     *
     * @param string $info_hash Info hash of the torrent.
     * @param string $peer_id Peer ID to exclude (peer ID of the client announcing).
     * @return array With keys 'complete' and 'incomplete' having counters for each group.
     */
    public function getPeerStats( $info_hash, $peer_id )
    {
        $sql = <<<SQL
SELECT
    COALESCE( SUM( `status` = 'complete' ), 0 ) AS 'complete',
    COALESCE( SUM( `status` != 'complete' ), 0 ) AS 'incomplete'
FROM
    `phptracker_peers`
WHERE
    `info_hash`           = :info_hash
    AND
    `peer_id`             != :peer_id
    AND
    (
        `expires` IS NULL
        OR
        `expires` > CURRENT_TIMESTAMP
    )
SQL;

        $results =  $this->query( $sql, array(
            ':info_hash'    => $info_hash,
            ':peer_id'      => $peer_id,
        ) );

        return $results[0];
    }

    /**
     * Performs a MySQL database query and returns resultset.
     *
     * @param string $query_string Query string with placeholders.
     * @param array $parameters Parameters to replace placeholders in the query string (keys => values). They get escaped.
     * @throws PHPTracker_Persistence_Error In case of DB error.
     * @return PHPTracker_Persistence_MysqlResult Could also return true if there is not resultset.
     */
    protected function query( $query_string, array $parameters )
    {
        $this->lazyConnect();

        foreach ( $parameters as &$parameter )
        {
            if ( is_null( $parameter ) )
            {
                $parameter = 'NULL';
            }
            elseif ( is_numeric( $parameter ) )
            {
                // Locale unaware number representation.
                $parameter = sprintf( '%.12F', $parameter );
                if ( false !== strpos( $parameter, '.' ) )
                {
                    $parameter = rtrim( rtrim( $parameter, '0' ), '.' );
                }
            }
            else
            {
                $parameter = "'" . mysqli_real_escape_string( $this->connection, $parameter ) . "'";
            }
        }

        $prepared_query = strtr( $query_string, $parameters );

        if ( false === ( $results = $this->connection->query( $prepared_query ) ))
        {
            throw new PHPTracker_Persistence_Error( "Error while performing mysql query.\n" . $this->connection->error . "\n" . $prepared_query );
        }

        if ( is_bool( $results ) )
        {
            return $results;
        }

        return new PHPTracker_Persistence_MysqlResult( $results );
    }

    /**
     * If there is no DB connection established yet, it connects and populates self::$connection attribute.
     *
     * Also "wakes up" connection if it has gone away.
     */
    protected function lazyConnect()
    {
        if ( isset( $this->connection ) )
        {
            // Connection might have gone away.
            $this->connection->ping();
            return;
        }

        list( $host, $user, $password, $database ) = $this->config->getMulti( array(
            'db_host',
            'db_user',
            'db_password',
            'db_name'
        ) );

        $this->connection = new mysqli( $host, $user, $password, $password );

        if ( mysqli_connect_errno() )
        {
            throw new PHPTracker_Persistence_Error( 'Unable to connect to mysql database: ' . mysqli_connect_error() );
        }

        if ( false === $this->connection->select_db( $database ) )
        {
            throw new PHPTracker_Persistence_Error( "Unable to select database: $database.\n" . $this->connection->error );
        }
    }

    /**
     * If the object is used in a forked child process, this method is called after forking.
     *
     * Re-establishes the connection for the fork.
     *
     * @see PHPTracker_Persistence_ResetWhenForking
     */
    public function resetAfterForking()
    {
        $this->connection = null;
    }
}

?>

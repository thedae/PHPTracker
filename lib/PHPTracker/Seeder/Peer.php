<?php

/**
 * Daemon seeding all active torrent files on this server.
 *
 * @package PHPTracker
 * @subpackage Seeder
 */
class PHPTracker_Seeder_Peer extends PHPTracker_Threading_Forker
{
    /**
     * String representation of the address to bind the socket to. Defaults to 127.0.0.1.
     *
     * Used for announcing, ie. clients will try to connect here - should be public.
     *
     * @var string
     */
    public $address;

    /**
     * Port number to bind the socket to. Defaults to 6881.
     *
     * @var integer
     */
    public $port;

    /**
     * Azureus-style peer ID generated from the address and port.
     *
     * @var string
     */
    public $peer_id;

    /**
     * Configuration of this class.
     *
     * @var PHPTracker_Config_Interface
     */
    protected $config;

    /**
     * Persistence class to save/retrieve data.
     *
     * @var PHPTracker_Persistence_Interface
     */
    protected $persistence;

    /**
     * Logger object used to log messages and errors in this class.
     *
     * @var PHPTracker_Logger_Interface
     */
    protected $logger;

    /**
     * Open socket that accepts incoming connections. Child processes share this.
     *
     * @var resource
     */
    protected $listening_socket;

    /**
     * One and only supported protocol name.
     */
    const PROTOCOL_STRING = 'BitTorrent protocol';

    /**
     * Default address to bind the listening socket to.
     */
    const DEFAULT_ADDRESS       = '127.0.0.1';

    /**
     * Default port to bind the listening socket to.
     */
    const DEFAULT_PORT          = 6881;

    /**
     * To prevent possible memory leaks, every fork terminates after X iterations.
     *
     * The fork is automatically recreated by the parent process, so nothing changes.
     * In our case one iterations means one client connection session.
     */
    const STOP_AFTER_ITERATIONS = 20;

    /**
     * Setting up class from config.
     *
     * @param PHPTracker_Config_Interface $config
     */
    public function  __construct( PHPTracker_Config_Interface $config )
    {
        $this->config       = $config;

        $this->persistence           = $this->config->get( 'persistence' );
        $this->logger                = $this->config->get( 'logger', false, new PHPTracker_Logger_Blackhole() );
        $this->external_address      = $this->config->get( 'seeder_address', false, self::DEFAULT_ADDRESS );
        $this->internal_address      = $this->config->get( 'seeder_internal_address', false, $this->external_address );
        $this->port                  = $this->config->get( 'seeder_host', false, self::DEFAULT_PORT );

        $this->peer_id      = $this->generatePeerId();
    }

    /**
     * Called before forking children, intializes the object and sets up listening socket.
     *
     * @return Number of forks to create. If negative, forks are recreated when exiting and absolute values is used.
     */
    public function startParentProcess()
    {
        // Opening socket - file dscriptor will be shared among the child processes.
        $this->startListening();

        // We want this many forks for connections, permanently recreated when failing (-1).
        $peer_forks = $this->config->get( 'peer_forks' );

        if ( $peer_forks < 1 )
        {
            throw new PHPTracker_Seeder_Error( "Invalid peer fork number: $peer_forks. The minimum fork number is 1." );
        }

        $this->logger->logMessage( "Seeder peer started to listen on {$this->internal_address}:{$this->port}. Forking $peer_forks children." );

        return $peer_forks * -1;
    }

    /**
     * Called on child processes after forking. Starts accepting incoming connections.
     *
     * @param integer $slot The slot (numbered index) of the fork. Reused when recreating process.
     */
    public function startChildProcess( $slot )
    {
        // Some persistence providers (eg. MySQL) should create a new connection when the process is forked.
        if ( $this->persistence instanceof PHPTracker_Persistence_ResetWhenForking )
        {
            $this->persistence->resetAfterForking();
        }

        $this->logger->logMessage( "Forked process on slot $slot starts accepting connections." );

        // Waiting for incoming connections.
        $this->communicationLoop();
    }

    /**
     * Generates unique Azuerus style peer ID from the address and port.
     *
     * @return string
     */
    protected function generatePeerId()
    {
        return '-PT0001-' . substr( sha1( $this->external_address . $this->port, true ), 0, 20 );
    }

    /**
     * Setting up listening socket. Should be called before forking.
     *
     * @throws PHPTracker_Seeder_Error_Socket When error happens during creating, binding or listening.
     */
    protected function startListening()
    {
        if ( false === ( $socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP ) ) )
        {
            throw new PHPTracker_Seeder_Error_Socket( 'Failed to create socket: ' . socket_strerror( $socket ) );
        }

        $this->listening_socket = $socket;

        if ( false === ( $result = socket_bind( $this->listening_socket, $this->internal_address, $this->port ) ) )
        {
            throw new PHPTracker_Seeder_Error_Socket( 'Failed to bind socket: ' . socket_strerror( $result ) );
        }

        // We set backlog to 5 (ie. 5 connections can be queued) - to be adjusted.
        if ( false === ( $result = socket_listen( $this->listening_socket, 5 ) ) )
        {
            throw new PHPTracker_Seeder_Error_Socket( 'Failed to listen to socket: ' . socket_strerror( $result ) );
        }
    }

    /**
     * Loop constantly accepting incoming connections and starting to communicate with them.
     *
     * Every incoming connection initializes a PHPTracker_Seeder_Client object.
     */
    protected function communicationLoop()
    {
        $iterations = 0;

        do
        {
            $client = new PHPTracker_Seeder_Client( $this->listening_socket );
            do
            {
                try
                {
                    if ( !isset( $client->peer_id ) )
                    {
                        $this->shakeHand( $client );

                        // Telling the client that we have all pieces.
                        $this->sendBitField( $client );

                        // We are unchoking the client letting it send requests.
                        $client->unchoke();
                    }
                    else
                    {
                        $this->answer( $client );
                    }
                }
                catch ( PHPTracker_Seeder_Error_CloseConnection $e )
                {
                    $this->logger->logMessage( "Closing connection with peer {$client->peer_id} with address {$client->address}:{$client->port}, reason: \"{$e->getMessage()}\". Stats: " . $client->getStats() );
                    unset( $client );

                    // We might wait for another client.
                    break;
                }
            } while ( true );
        } while ( ++$iterations < self::STOP_AFTER_ITERATIONS ); // Memory leak prevention, see self::STOP_AFTER_ITERATIONS.

        $this->logger->logMessage( 'Seeder process fork restarts to prevent memory leaks.' );
        exit( 0 );
    }

    /**
     * Manages handshaking with the client.
     *
     * If seeders_stop_seeding config key is set to a number greater than 0,
     * we check if we have at least N seeders beyond ourselves for the requested
     * torrent and if so, stop seeding (to spare bandwith).
     *
     * @throws PHPTracker_Seeder_Error_CloseConnection In case when the reqeust is invalid or we don't want or cannot serve the requested torrent.
     * @param PHPTracker_Seeder_Client $client
     */
    protected function shakeHand( PHPTracker_Seeder_Client $client )
    {
        $protocol_length = unpack( 'C', $client->socketRead( 1 ) );
        $protocol_length = current( $protocol_length );

        if ( ( $protocol = $client->socketRead( $protocol_length ) ) !== self::PROTOCOL_STRING )
        {
            $this->logger->logError( "Client tries to connect with unsupported protocol: " . substr( $protocol, 0, 100 ) . ". Closing connection." );
            throw new PHPTracker_Seeder_Error_CloseConnection( 'Unsupported protocol.' );
        }

        // 8 reserved void bytes.
        $client->socketRead( 8 );

        $info_hash          = $client->socketRead( 20 );
        $client->peer_id    = $client->socketRead( 20 );

        $info_hash_readable = unpack( 'H*', $info_hash );
        $info_hash_readable = reset( $info_hash_readable );

        $torrent = $this->persistence->getTorrent( $info_hash );
        if ( !isset( $torrent ) )
        {
            throw new PHPTracker_Seeder_Error_CloseConnection( 'Unknown info hash.' );
        }

        $client->torrent = $torrent;

        // If we have X other seeders already, we stop seeding on our own.
        if ( 0 < ( $seeders_stop_seeding = $this->config->get( 'seeders_stop_seeding', false, 0 ) ) )
        {
            $stats = $this->persistence->getPeerStats( $info_hash, $this->peer_id );
            if ( $stats['complete'] >= $seeders_stop_seeding )
            {
                $this->logger->logMessage( "External seeder limit ($seeders_stop_seeding) reached for info hash $info_hash_readable, stopping seeding." );
                throw new PHPTracker_Seeder_Error_CloseConnection( 'Stop seeding, we have others to seed.' );
            }
        }

        // Our handshake signal.
        $client->socketWrite(
            pack( 'C', strlen( self::PROTOCOL_STRING ) ) .  // Length of protocol string.
            self::PROTOCOL_STRING .                         // Protocol string.
            pack( 'a8', '' ) .                              // 8 void bytes.
            $info_hash .                                    // Echoing the info hash that the client requested.
            pack( 'a20', $this->peer_id )                   // Our peer id.
         );

        $this->logger->logMessage( "Handshake completed with peer {$client->peer_id} with address {$client->address}:{$client->port}, info hash: $info_hash_readable." );
    }

    /**
     * Reading messages from the client and answering them.
     *
     * @throws PHPTracker_Seeder_Error_CloseConnection In case of protocol violation.
     * @param PHPTracker_Seeder_Client $client
     */
    protected function answer( PHPTracker_Seeder_Client $client )
    {
        $message_length = unpack( 'N', $client->socketRead( 4 ) );
        $message_length = current( $message_length );

        if ( 0 == $message_length )
        {
            // Keep-alive.
            return;
        }

        $message_type = unpack( 'C', $client->socketRead( 1 ) );
        $message_type = current( $message_type );

        --$message_length; // The length of the payload.

        switch ( $message_type )
        {
            case 0:
                // Choke.
                // We are only seeding, we can ignore this.
                break;
            case 1:
                // Unchoke.
                // We are only seeding, we can ignore this.
                break;
            case 2:
                // Interested.
                // We are only seeding, we can ignore this.
                break;
            case 3:
                // Not interested.
                // We are only seeding, we can ignore this.
                break;
            case 4:
                // Have.
                // We are only seeding, we can ignore this.
                $client->socketRead( $message_length );
                break;
            case 5:
                // Bitfield.
                // We are only seeding, we can ignore this.
                $client->socketRead( $message_length );
                break;
            case 6:
                // Requesting one block of the file.
                $payload = unpack( 'N*', $client->socketRead( $message_length ) );
                $this->sendBlock( $client, /* Piece index */ $payload[1], /* First byte from the piece */ $payload[2], /* Length of the block */ $payload[3] );
                break;
            case 7:
                // Piece.
                // We are only seeding, we can ignore this.
                $client->socketRead( $message_length );
                break;
            case 8:
                // Cancel.
                // We send blocks in one step, we can ignore this.
                $client->socketRead( $message_length );
                break;
            default:
                throw new PHPTracker_Seeder_Error_CloseConnection( 'Protocol violation, unsupported message.' );
        }
    }

    /**
     * Sends one block of a file to the client.
     *
     * @param PHPTracker_Seeder_Client $client
     * @param integer $piece_index Index of the piece containing the block.
     * @param integer $block_begin Beginning of the block relative to the piece in byets.
     * @param integer $length Length of the block in bytes.
     */
    protected function sendBlock( PHPTracker_Seeder_Client $client, $piece_index, $block_begin, $length )
    {
        $message = pack( 'CNN', 7, $piece_index, $block_begin ) . $client->torrent->readBlock( $piece_index, $block_begin, $length );
        $client->socketWrite( pack( 'N', strlen( $message ) ) . $message );

        // Saving statistics.
        $client->addStatBytes( $length, PHPTracker_Seeder_Client::STAT_DATA_SENT );
    }

    /**
     * Sending intial bitfield tot he clint letting it know that we have to entire file.
     *
     * The bitfeild looks like:
     * [11111111-11111111-11100000]
     * Meaning that we have all the 19 pieces (padding bits must be 0).
     *
     * @param PHPTracker_Seeder_Client $client
     */
    protected function sendBitField( PHPTracker_Seeder_Client $client )
    {
        $n_pieces = ceil( $client->torrent->length / $client->torrent->size_piece );

        $message = pack( 'C', 5 );

        while ( $n_pieces > 0 )
        {
            if ( $n_pieces >= 8 )
            {
                $message .= pack( 'C', 255 );
                $n_pieces -= 8;
            }
            else
            {
                // Last byte of the bitfield, like 11100000.
                $message .= pack( 'C', 256 - pow( 2, 8 - $n_pieces ) );
                $n_pieces = 0;
            }
        }

        $client->socketWrite( pack( 'N', strlen( $message ) ) . $message );
    }
}

?>
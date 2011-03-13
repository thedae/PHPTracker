# --------------------------------------------------------
# Server version:               5.1.52
# Server OS:                    redhat-linux-gnu
# --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

# Dumping structure for table phptracker_peers
CREATE TABLE IF NOT EXISTS `phptracker_peers` (
  `peer_id` binary(20) NOT NULL COMMENT 'Peer unique ID.',
  `ip_address` int(10) unsigned NOT NULL COMMENT 'IP address of the client.',
  `port` smallint(20) unsigned NOT NULL COMMENT 'Listening port of the peer.',
  `info_hash` binary(20) NOT NULL COMMENT 'Info hash of the torrent.',
  `bytes_uploaded` int(10) unsigned DEFAULT NULL COMMENT 'Uploaded bytes since started.',
  `bytes_downloaded` int(10) unsigned DEFAULT NULL COMMENT 'Downloaded bytes since started.',
  `bytes_left` int(10) unsigned DEFAULT NULL COMMENT 'Bytes left to download.',
  `status` enum('complete','incomplete') NOT NULL DEFAULT 'incomplete' COMMENT 'Status of the peer (seeder/leecher).',
  `expires` timestamp NULL DEFAULT NULL COMMENT 'Timestamp when peer is considered as expired.',
  PRIMARY KEY (`peer_id`,`info_hash`),
  KEY `Index 2` (`info_hash`),
  KEY `Index 3` (`bytes_left`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Current peers for torrents.';

# Dumping structure for table phptracker_torrents
CREATE TABLE IF NOT EXISTS `phptracker_torrents` (
  `info_hash` binary(20) NOT NULL COMMENT 'Info hash.',
  `length` int(11) unsigned NOT NULL COMMENT 'Size of the contained file in bytes.',
  `pieces_length` int(11) unsigned NOT NULL COMMENT 'Size of one piece in bytes.',
  `name` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL COMMENT 'Basename of the contained file.',
  `pieces` mediumblob NOT NULL COMMENT 'Concatenated hashes of all pieces.',
  `path` varchar(1024) NOT NULL COMMENT 'Full path of the physical file.',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active' COMMENT 'Activity status of the torrent.',
  PRIMARY KEY (`info_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Table to store basic torrent file information upon creation.';

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

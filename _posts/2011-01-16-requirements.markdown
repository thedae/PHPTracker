---
layout: default
title: Requirements
description: PHPTracker PHP Bittorrent library requirements and dependencies.
---
## {{ page.title }} ##

PHPTracker has the following dependencies:

*   PHP 5.2 or later
*   Socket extension for PHP (shipped by default with PHP)
*   PECL hash extension for PHP (shipped with PHP)
*   Process Control support for PHP (enabled by default)
*   MySQLi extension for PHP (shipped by default with PHP, only needed when using MySQL as storage)
*   MySQL database server (when using MySQL as storage)
*   Linux server
*   If you run your seeder server as daemon, you will need php-posix package

  
To run unit tests, you need [PHPUnit][1].

 [1]: https://github.com/sebastianbergmann/phpunit/
# My CakePHP core Code Sniffer

This code works with [phpcs](http://pear.php.net/manual/en/package.php.php-codesniffer.php)
and checks code against a modified version of the coding standards used in CakePHP.

## Installation

### Usage

After installation you can check code compliance to the standard using
`phpcs`:

	phpcs --standard=MyCakePHPCore /path/to/code

### Changes to the CakePHP one

* Detect Yoda conditions.
* Added @return doc block sniff
* LF on Windows are allowed to be \r\n
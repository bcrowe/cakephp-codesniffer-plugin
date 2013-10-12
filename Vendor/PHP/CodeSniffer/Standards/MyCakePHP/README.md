# My CakePHP Code Sniffer

This code works with [phpcs](http://pear.php.net/manual/en/package.php.php-codesniffer.php)
and checks code against a modified version of the coding standards used in CakePHP.

## Installation

### Usage

After installation you can check code compliance to the standard using
`phpcs`:

	phpcs --standard=MyCakePHP /path/to/code

### Changes to the CakePHP one

* Detect Yoda conditions.
* Added IsNull sniff
* Added Type casting sniff
* Added @return doc block sniff
* Added Ternary (incl. short ternary) sniff
* Intentation correct (same level as methods and attributes for classes)
* LF on Windows are allowed to be \r\n

### TODO

* No private methods or attributes (error instead of warning)
* More whitespace sniffs
* Sniff for deprecations

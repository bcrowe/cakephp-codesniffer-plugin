<?php
/**
 * Parses and verifies the doc comments for functions.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: FunctionCommentSniff.php 292513 2009-12-23 00:41:20Z squiz $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

if (class_exists('PHP_CodeSniffer_CommentParser_FunctionCommentParser', true) === false) {
    $error = 'Class PHP_CodeSniffer_CommentParser_FunctionCommentParser not found';
    throw new PHP_CodeSniffer_Exception($error);
}

/**
 * CakePHP_Sniffs_Commenting_FunctionCommentSniff
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer_CakePHP
 * @author    Mark Scherer <dereuromark@gmail.com>
 * @copyright Copyright 2005-2013, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   1.0
 * @link      http://pear.php.net/package/PHP_CodeSniffer_CakePHP
 */
class CakePHP_Sniffs_Commenting_FunctionCommentSniff extends Squiz_Sniffs_Commenting_FunctionCommentSniff {
}
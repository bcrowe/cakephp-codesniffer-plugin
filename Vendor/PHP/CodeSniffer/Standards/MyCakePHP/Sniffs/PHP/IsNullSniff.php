<?php
/**
 * PHP Version 5
 *
 * MyCakePHP_Sniffs_PHP_IsNullSniff
 *
 * @category  PHP
 * @author    Mark Scherer <dereuromark@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   1.0
 */

/**
 * Ensures that strict check "=== null" is used instead of is_null().
 *
 */
class MyCakePHP_Sniffs_PHP_IsNullSniff implements PHP_CodeSniffer_Sniff {

/**
 * Returns an array of tokens this test wants to listen for.
 *
 * @return array
 */
	public function register() {
		return array(T_STRING);
	}

/**
 * Processes this test, when one of its tokens is encountered.
 *
 * Ensures that strict check "=== null" is used instead of is_null().
 *
 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
 * @param integer              $stackPtr  The position of the current token in the
 *                                        stack passed in $tokens.
 * @return void
 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();
		$content = strtolower($tokens[$stackPtr]['content']);
		if ($content !== 'is_null') {
			return;
		}
		$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		if ($tokens[$nextToken]['code'] !== T_OPEN_PARENTHESIS) {
			return;
		}

		$error = 'Usage of ' . $tokens[$stackPtr]['content'] . ' not allowed; use strict null check (=== null) instead';
		$phpcsFile->addError($error, $stackPtr, 'NotAllowed');
	}

}

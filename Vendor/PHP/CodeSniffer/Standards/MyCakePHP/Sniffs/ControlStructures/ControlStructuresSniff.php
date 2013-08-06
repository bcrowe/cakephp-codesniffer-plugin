<?php

class MyCakePHP_Sniffs_ControlStructures_ControlStructuresSniff implements PHP_CodeSniffer_Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(T_IF, T_ELSEIF, T_ELSE);
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * Checks that ELSEIF is used instead of ELSE IF.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int                  $stackPtr  The position of the current token in the
	 *                                        stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();

		$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		if ($tokens[$nextToken]['code'] === T_OPEN_PARENTHESIS) {
			$closer = $tokens[$nextToken]['parenthesis_closer'];
			$diff = $closer - $stackPtr;
			$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + $diff + 1), null, true);
		}
		if ($tokens[$nextToken]['code'] === T_IF) {
			// "else if" is not checked by this sniff, another sniff takes care of that.
			return;
		}
		if ($tokens[$nextToken]['code'] !== T_OPEN_CURLY_BRACKET) {
			$error = 'Curly brackets required for if/elseif/else.';
			$phpcsFile->addError($error, $stackPtr, 'NotAllowed');
		}
	}

}

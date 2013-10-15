<?php

/**
 * MyCakePHP_Sniffs_WhiteSpace_LanguageConstructSpacingSniff.
 *
 * Ensures all language constructs (without brackets) contain a
 * single space between themselves and their content.
 * Also asserts that no parenthesis are used.
 *
 * @author Mark Scherer
 * @license MIT
 */
class MyCakePHP_Sniffs_WhiteSpace_LanguageConstructSpacingSniff implements PHP_CodeSniffer_Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(
			//T_ECHO,
			//T_PRINT,
			//T_RETURN,
			T_INCLUDE,
			T_INCLUDE_ONCE,
			T_REQUIRE,
			T_REQUIRE_ONCE,
			//T_NEW,
			);

	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int                  $stackPtr  The position of the current token in
	 *                                        the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();

		if ($tokens[($stackPtr + 1)]['code'] === T_SEMICOLON) {
			// No content for this language construct.
			return;
		}

		// We don't care about the following whitespace and let another sniff take care of that
		$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);

		// No brackets - everything's fine
		if ($tokens[$nextToken]['code'] !== T_OPEN_PARENTHESIS) {
			return;
		}

		$error = 'Language constructs must be followed by parenthesesis.';
		$phpcsFile->addFixableError($error, $stackPtr, 'IncorrectParenthesis');

		$closingToken = $tokens[$nextToken]['parenthesis_closer'];

		// Do we need to add a space?
		$replacement = '';
		if ($nextToken - $stackPtr === 1) {
			$replacement = ' ';
		}

		if ($phpcsFile->fixer->enabled === true) {
			$phpcsFile->fixer->replaceToken($nextToken, $replacement);
			$phpcsFile->fixer->replaceToken($closingToken, '');
		}
	}

}


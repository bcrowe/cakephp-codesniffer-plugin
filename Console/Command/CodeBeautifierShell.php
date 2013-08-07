<?php
App::uses('AppShell', 'Console/Command');

if (strpos(get_include_path(), VENDORS) === false) {
	set_include_path(get_include_path() . PATH_SEPARATOR . VENDORS);
}
$pluginVendorPath = CakePlugin::path('CodeSniffer') . 'Vendor' . DS;
if (strpos(get_include_path(), $pluginVendorPath) === false) {
	set_include_path(get_include_path() . PATH_SEPARATOR . $pluginVendorPath);
}

/**
 * CakePHP CodeSniffer plugin
 *
 * @copyright Copyright © Mark Scherer
 * @link http://www.dereuromark.de
 * @license MIT License
 */
class CodeBeautifierShell extends AppShell {

	/**
	 * Initialize CodeSnifferShell
	 * + checks if CodeSniffer is installed and offer auto installation option.
	 */
	public function initialize() {

		parent::initialize();
	}

	/**
	 * Welcome message
	 */
	public function startup() {
		$this->out('<info>CodeBeautifier shell</info> for CakePHP', 2);

		parent::startup();
	}

	/**
	 * Catch-all for CodeSniffer commands
	 *
	 * @link http://pear.php.net/manual/en/package.php.php-codesniffer.usage.php
	 * @return void
	 */
	public function run() {
		if (!empty($this->args)) {
			$path = $this->args[0];
			$path = realpath($path);
		}
		if (empty($path)) {
			$this->error('Please select a path');
		}
	}

	/**
	 * Add options from CodeSniffer
	 * or CakePHP's Shell will exit upon unrecognized options.
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		$parser->addOptions(array(
			'help' => array('short' => 'h', 'boolean' => true),
			'quiet' => array('short' => 'q', 'boolean' => true),
			'verbose' => array('short' => 'v', 'boolean' => true),
			'standard' => array(
				'short' => 's',
				'description' => 'Standard to use (defaults to CakePHP)'
			),
		))
		->addSubcommand('run', array(
			'help' => __d('cake_console', 'Run'),
			//'parser' => $parser
		));

		return $parser;
	}

}

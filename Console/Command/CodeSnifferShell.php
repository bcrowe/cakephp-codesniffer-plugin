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
class CodeSnifferShell extends AppShell {

	public $standard = 'CakePHP';

	/**
	 * Directory where CodeSniffer sniffs resides
	 */
	public $sniffsDir;

	/**
	 * Initialize CodeSnifferShell
	 * + checks if CodeSniffer is installed and offer auto installation option.
	 */
	public function initialize() {
		/*
		if (empty($this->sniffsDir)) {
			if (Configure::read('CodeSniffer.phar_dir') !== null) {
				$this->sniffsDir = Configure::read('CodeSniffer.phar_dir');
			} else {
				$this->sniffsDir = dirname(dirname(dirname(__FILE__))).DS.'Vendor'.DS.'CodeSniffer'.DS;
			}
		}

		$this->_checkCodeSnifferPhar();
		$this->_checkCodeSnifferJSON();
		*/
		parent::initialize();
	}

	/**
	 * Welcome message
	 */
	public function startup() {
		$this->out('<info>CodeSniffer plugin</info> for CakePHP', 2);

		if ($standard = Configure::read('CodeSniffer.standard')) {
			$this->standard = $standard;
		}
		parent::startup();
	}

	/**
	 * Catch-all for CodeSniffer commands
	 *
	 * @link http://pear.php.net/manual/en/package.php.php-codesniffer.usage.php
	 * @return void
	 */
	public function run() {
		// for larger PHP files we need some more memory
		ini_set('memory_limit', '256M');

		$path = null;
		if (!empty($this->args)) {
			$path = $this->args[0];
		}
		if (!empty($this->params['plugin'])) {
			$path = CakePlugin::path(Inflector::camelize($this->params['plugin'])) . $path;
		} elseif (empty($path)) {
			$path = APP;
		}
		$path = realpath($path);
		if (empty($path)) {
			$this->error('Please provide a valid path.');
		}

		$_SERVER['argv'] = array();
		$_SERVER['argv'][] = '--encoding=utf8';
		$standard = $this->standard;
		if ($this->params['standard']) {
			$standard = $this->params['standard'];
		}
		$_SERVER['argv'][] = '--standard=' . $standard;
		if ($this->params['sniffs']) {
			$_SERVER['argv'][] = '--sniffs=' . $this->params['sniffs'];
		}

		$_SERVER['argv'][] = '--report-file='.TMP.'phpcs.txt';
		if (!$this->params['quiet']) {
			$_SERVER['argv'][] = '-p';
		}
		if ($this->params['verbose']) {
			$_SERVER['argv'][] = '-v';
			$_SERVER['argv'][] = '-s';
		}
		//$_SERVER['argv'][] = '--error-severity=1';
		//$_SERVER['argv'][] = '--warning-severity=1';
		if ($this->params['ext']) {
			$_SERVER['argv'][] = '--extensions=' . $this->params['ext'];
		}
		$_SERVER['argv'][] = $path;

		$_SERVER['argc'] = count($_SERVER['argv']);


		// Optionally use PHP_Timer to print time/memory stats for the run.
		// Note that the reports are the ones who actually print the data
		// as they decide if it is ok to print this data to screen.
		@include_once 'PHP/Timer.php';
		if (class_exists('PHP_Timer', false) === true) {
		    PHP_Timer::start();
		}

		$this->_process();
		$this->out('For details check the phpcs.txt file in your TMP folder.');
	}

	/**
	 * Tokenize a specific file like `/path/to/file.ext`.
	 * Creates a file `/path/to/file.ext.token` with all token names
	 * added in comment lines.
	 *
	 * @return void
	 */
	public function tokenize() {
		if (!empty($this->args)) {
			$path = $this->args[0];
			$path = realpath($path);
		}
		if (empty($path) || !is_file($path)) {
			$this->error('Please select a path to a file');
		}

		$_SERVER['argv'] = array();
		$_SERVER['argv'][] = '--encoding=utf8';
		$standard = $this->standard;
		if ($this->params['standard']) {
			$standard = $this->params['standard'];
		}
		$_SERVER['argv'][] = '--standard=' . $standard;
		$_SERVER['argv'][] = $path;

		$_SERVER['argc'] = count($_SERVER['argv']);

		$res = array();

		$tokens = $this->_getTokens($path);
		$array = file($path);

		foreach ($array as $key => $row) {
			$res[] = rtrim($row);
			if ($tokenStrings = $this->_tokenize($key + 1, $tokens)) {
				foreach ($tokenStrings as $string) {
					$res[] = '// ' . $string;
				}
			}
		}
		$content = implode(PHP_EOL, $res);
		$this->out('Tokenizing: ' . $path);
		$newPath = dirname($path) . DS . extractPathInfo('basename', $path) . '.token';
		file_put_contents($newPath, $content);
		$this->out('Filename: ' . $newPath);
	}

	/**
	 * CodeSnifferShell::_getTokens()
	 *
	 * @param string $path
	 * @return array $tokens
	 */
	protected function _getTokens($path) {
		include_once('PHP/CodeSniffer.php');
		$phpcs = new PHP_CodeSniffer();
		$phpcs->process(array(), $this->standard, array());

		$file = $phpcs->processFile($path);
		$file->start();
		return $file->getTokens();
	}

	/**
	 * CodeSnifferShell::_tokenize()
	 *
	 * @param int $row
	 * @param array $tokens
	 * @return array
	 */
	protected function _tokenize($row, $tokens) {
		$pieces = array();
		foreach ($tokens as $key => $token) {
			if ($token['line'] > $row) {
				break;
			}
			if ($token['line'] < $row) {
				continue;
			}
			if ($this->params['verbose']) {
				$type = $token['type'];
				unset($token['type']);
				unset($token['content']);
				unset($token['code']);
				$tokenList = array();
				foreach ($token as $k => $v) {
					if (is_array($v)) {
						if (empty($v)) {
							continue;
						}
						$v = json_encode($v);
					}
					$tokenList[] = $k . '=' . $v;
				}
				$pieces[] = $type . ' ('.$key.') ' . implode(', ', $tokenList);
			} else {
				$pieces[] = $token['type'];
			}
		}
		if ($this->params['verbose']) {
			return $pieces;
		}
		return array(implode(' ', $pieces));
	}

	/**
	 * Grabs the latest CodeSniffer.phar from http://getCodeSniffer.org/CodeSniffer.phar
	 * Changeable at CakePHP configuration: CodeSniffer.phar_url
	 *
	 * @return void
	 */
	protected function _setup($version) {
		$csUrl = 'http://download.pear.php.net/package/PHP_CodeSniffer-%s.tgz';
		$csUrl = sprintf($csUrl, $version);

		if (!is_writable($this->sniffsDir)) {
			$this->error("$this->sniffsDir is not writable.");
		}

		$this->out('<info>Setting up CodeSniffer</info>');
		$this->out("Downloading CodeSniffer from $csUrl...");

		$content = file_get_contents($csUrl);
		if ($content === false) {
			$this->error("Download failed");
		}

		$save = file_put_contents($this->sniffsDir . 'CodeSniffer', $content);

		if ($save === false) {
			$this->error("Unable to save to {$this->sniffsDir}CodeSniffer.");
		}

		$this->out("<info>CodeSniffer installed and saved successfully.</info>");
	}

	/**
	 * Convert options to string
	 *
	 * @param array $options Options array
	 * @return string Results
	 */
	protected static function _optionsToString($options) {
		if (empty($options) || !is_array($options)) {
			return '';
		}
		$results = '';
		foreach ($options as $option => $value) {
			if (strlen($results) > 0) {
				$results .= ' ';
			}
			if (empty($value)) {
				$results .= "--$option";
			}
			else {
				$results .= "--$option=$value";
			}
		}

		return $results;
	}

	/**
	 * List all available standards
	 *
	 * @return void
	 */
	public function standards() {
		$_SERVER['argv'] = array();
		$_SERVER['argv'][] = 'phpcs';
		$_SERVER['argv'][] = '-i';
		$this->_process();
	}

	/**
	 * @return void
	 */
	public function test() {
		$this->_checkCodeSniffer();
	}

	/**
	 * CodeSnifferShell::install()
	 *
	 * @return void
	 */
	public function install() {
		$feed = 'http://pear.php.net/feeds/pkg_php_codesniffer.rss';

	}

	/**
	 * Mess detector
	 *
	 * @return void
	 */
	public function phpmd() {
		if (!empty($this->params['version'])) {
			//return passthru('php '.VENDORS."PHP".DS."scripts".DS.'phpmd --help');
		}

		// Allow as much memory as possible by default
		if (extension_loaded('suhosin') && is_numeric(ini_get('suhosin.memory_limit'))) {
		    $limit = ini_get('memory_limit');
		    if (preg_match('(^(\d+)([BKMGT]))', $limit, $match)) {
		        $shift = array('B' => 0, 'K' => 10, 'M' => 20, 'G' => 30, 'T' => 40);
		        $limit = ($match[1] * (1 << $shift[$match[2]]));
		    }
		    if (ini_get('suhosin.memory_limit') > $limit && $limit > -1) {
		        ini_set('memory_limit', ini_get('suhosin.memory_limit'));
		    }
		} else {
		    ini_set('memory_limit', -1);
		}

		// Check php setup for cli arguments
		if (!isset($_SERVER['argv']) && !isset($argv)) {
		    fwrite(STDERR, 'Please enable the "register_argc_argv" directive in your php.ini', PHP_EOL);
		    exit(1);
		}

		$_SERVER['argv'] = array();
		$_SERVER['argv'][] = 'phpcs';
		$_SERVER['argv'][] = VENDORS.'PHP'.DS;
		$_SERVER['argv'][] = 'xml';
		$_SERVER['argv'][] = 'codesize';
		//$_SERVER['argv'][] = '--error-severity=1';
		//$_SERVER['argv'][] = '--warning-severity=1';
		//$_SERVER['argv'][] = '--config-show';

		$_SERVER['argc'] = count($_SERVER['argv']);

		// Load command line utility
		require_once 'PHP/PMD/TextUI/Command.php';

		// Run command line interface
		exit(PHP_PMD_TextUI_Command::main($_SERVER['argv']));
	}


	/**
	 * Check if CodeSniffer.phar is available
	 * Offer to install if it isn't available
	 */
	protected function _checkCodeSniffer() {
		$_SERVER['argv'] = array();
		$_SERVER['argv'][] = 'phpcs';
		$_SERVER['argv'][] = '--version';

		$this->_process();
	}

	/**
	 * CodeSnifferShell::_process()
	 *
	 * @return void
	 */
	protected function _process() {
		include_once 'PHP/CodeSniffer/CLI.php';


		$phpcs = new PHP_CodeSniffer_CLI();
		$phpcs->checkRequirements();

		$numErrors = $phpcs->process();
		if ($numErrors !== 0) {
			$this->err('An error occured during processing.');
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
			'no-interaction' => array('short' => 'n'),
			'standard' => array(
				'short' => 's',
				'description' => 'Standard to use (defaults to CakePHP)',
				'default' => ''
			),
			'plugin' => array(
				'short' => 'p',
				'description' => 'Plugin to use (combined with path subpath of this plugin).',
				'default' => ''
			),
			'ext' => array(
				'short' => 'e',
				'description' => 'Extensions to check (comma separated list).',
				'default' => ''
			),
			'sniffs' => array(
				'description' => 'Checking files for specific sniffs only (comma separated list). E.g.: Generic.PHP.LowerCaseConstant,CakePHP.WhiteSpace.CommaSpacing',
				'default' => ''
			),
		))
		->addSubcommand('test', array(
			'help' => __d('cake_console', 'Test CS and list its installed version.'),
			//'parser' => $parser
		))
		->addSubcommand('standards', array(
			'help' => __d('cake_console', 'List available standards.'),
			//'parser' => $parser
		))
		->addSubcommand('tokenize', array(
			'help' => __d('cake_console', 'Tokenize file as {filename}.token and store it in the same dir.'),
			//'parser' => $parser
		))
		->addSubcommand('run', array(
			'help' => __d('cake_console', 'Run CS on the specified path.'),
			//'parser' => $parser
		));

		return $parser;
	}

}

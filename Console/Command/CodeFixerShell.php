<?php
App::uses('AppShell', 'Console/Command');
App::uses('CodeFixer', 'CodeSniffer.Lib');

/**
 * CakePHP CodeFixer plugin
 *
 * @copyright Copyright © 2012 Mark Scherer
 * @link http://www.dereuromark.de
 * @license MIT License
 */
class CodeFixerShell extends AppShell {

	/**
	 * Directory where CodeFixer sniffs resides
	 */
	public $sniffsDir;

	/**
	 * Initialize CodeFixerShell
	 * + checks if CodeFixer is installed and offer auto installation option.
	 */
	public function initialize() {
		if (empty($this->sniffsDir)) {
			if (Configure::read('CodeFixer.dir') !== null) {
				$this->sniffsDir = Configure::read('CodeFixer.dir');
			} else {
				$this->sniffsDir = dirname(dirname(dirname(__FILE__))).DS.'Vendor'.DS.'CodeFixer'.DS;
			}
		}

		$this->CodeFixer = new CodeFixer();
	}

	/**
	 * Welcome message
	 */
	public function startup() {
		$this->out("<info>CodeFixer plugin</info> for CakePHP", 2);
	}

	public function run() {
		if (!empty($this->_customPaths)) {
			$this->_paths = $this->_customPaths;
		} elseif (!empty($this->params['plugin'])) {
			$pluginpath = App::pluginPath($this->params['plugin']);
			$this->_paths = array($pluginpath);
		} else {
			$this->_paths = array(APP);
		}
		$this->_findFiles('php');
		$this->out(count($this->_files). ' files');
		foreach ($this->_files as $file) {
			$this->out(__d('cake_console', 'Checking %s...', $file), 1, Shell::VERBOSE);
			$this->_updateFile($file);
		}
	}

	protected function _updateFile($file) {
		if (!empty($this->params['dry-run'])) {
			return;
		}
		return $this->CodeFixer->processFile($file);
	}

	/**
	 * Searches the paths and finds files based on extension.
	 *
	 * @param string $extensions
	 * @return void
	 */
	protected function _findFiles($extensions = '') {
		$this->_files = array();
		foreach ($this->_paths as $path) {
			if (!is_dir($path)) {
				continue;
			}
			$Iterator = new RegexIterator(
				new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)),
				'/^.+\.(' . $extensions . ')$/i',
				RegexIterator::MATCH
			);
			foreach ($Iterator as $file) {
				//Iterator processes plugins even if not asked to
				if (empty($this->params['plugin'])) {
					$excludes = array('Plugin', 'plugins');
					$isIllegalPluginPath = false;
					foreach ($excludes as $exclude) {
						if (strpos($file, $path . $exclude . DS) === 0) {
							$isIllegalPluginPath = true;
							break;
						}
					}
					if ($isIllegalPluginPath) {
						continue;
					}
				}

				if ($file->isFile()) {
					$this->_files[] = $file->getPathname();
				}
			}
		}
	}

	/**
	 * Add options from CodeFixer
	 * or CakePHP's Shell will exit upon unrecognized options.
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		$parser->addOptions(array(
			'dry-run' => array(
				'short' => 'd', 'boolean' => true, 'help' => 'Dry Run'
			),
			'plugin' => array('short' => 'p', 'help' => 'Plugin', 'default'=>''),
			//'version' => array('short' => 'V', 'boolean' => true),
			'no-interaction' => array('short' => 'n')
		));

		return $parser;
	}

}

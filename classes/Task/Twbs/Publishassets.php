<?php
/**
 * Publishes the vendored asset files shipped with twitter bootstrap and font awesome
 *
 * The asset files themselves are versioned and imported with composer, and will be in the main application's /vendor
 * folder. These files cannot be served directly from there, so this task will copy the required assets to standard
 * paths under the docroot.
 *
 * You should run this task any time you install or update the composer packages.
 *
 * In a standard application layout the task requires no further options. However, in case you have an unusual directory
 * structure you can provide the following options:
 *
 *  - vendor-path: the absolute filesystem path to composer vendor libraries. Defaults to APPPATH.'../vendor'
 *  - public-path: the absolute filesystem path for assets. Defaults to DOCROOT.'assets';
 *
 * @package   kohana-twbs
 * @category  Tasks
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @copyright 2013 inGenerator Ltd
 * @license   http://kohanaframework.org/license
 */
class Task_Twbs_Publishassets extends Minion_Task {

	/**
	 * Sets the default options for the task - these use expressions so cannot be set in the field initialisation
	 */
	protected function __construct()
	{
		// Initialise the default options
		$this->_options = array(
			'vendor-path' => realpath(APPPATH.'../vendor'),
			'public-path' => DOCROOT.'assets',
		);
		// Call the parent constructor
		parent::__construct();
	}

	/**
	 * Configure validation of command line parameters - must be provided and the vendor path must be a valid path
	 *
	 * @param Validation $validation the validation object to configure
	 *
	 * @return Validation
	 */
	public function build_validation(Validation $validation)
	{
		return parent::build_validation($validation)
			->rule('vendor-path', 'not_empty')
		    ->rule('vendor-path', array('Task_Twbs_Publishassets', 'valid_path'))
			->rule('public-path', 'not_empty');
	}

	/**
	 * Ensure that a path represents a valid existing directory on the filesystem
	 *
	 * @param string $path relative or absolute path
	 *
	 * @return bool TRUE if the path is valid and exists
	 */
	public static function valid_path($path)
	{
		$realpath = realpath($path);
		return ($realpath AND file_exists($realpath) AND is_dir($realpath));
	}

	/**
	 * Execute the task and copy the required files
	 *
	 * @param array $params the command options
	 *
	 * @return void
	 */
	protected function _execute(array $params)
	{
		// Identify the vendor and destination path
		$vendor_path = $params['vendor-path'];
		$public_path = $params['public-path'];

		// Publish the twitter bootstrap js at DOCROOT/assets/js/twbs
		$this->copy_files($vendor_path.'/twbs/bootstrap/js', '/\.js$/', $public_path.'/js/twbs');
		Minion_CLI::write(Minion_CLI::color('Published bootstrap javascripts to '.$public_path.'/js/twbs', 'green'));

		// Publish the font-awesome font files as DOCROOT/assets/font
		$this->copy_files($vendor_path.'/fortawesome/font-awesome/font', '/^fontawesome/i', $public_path.'/font');
		Minion_CLI::write(Minion_CLI::color('Published font-awesome font to '.$public_path.'/font', 'green'));

	}

	/**
	 * Copy files matching a regular expression from one directory to another (without recursion)
	 *
	 * @param string $source_dir   path to the source directory
	 * @param string $file_pattern regular expression pattern that filenames must match to be counted
	 * @param string $dest_dir     path to the destination directory
	 *
	 * @throws Exception if the destination directory cannot be created, or the file cannot be copied.
	 */
	protected function copy_files($source_dir, $file_pattern, $dest_dir)
	{
		// Create the destination directory if required
		if ( ! file_exists($dest_dir) AND ! mkdir($dest_dir, 0755, TRUE))
		{
			throw new Exception("Could not create missing destination directory in '$dest_dir'");
		}

		// Iterate over the source directory and find the files to copy
		$dir = new DirectoryIterator($source_dir);
		foreach ($dir as $file)
		{
			/** @var DirectoryIterator $file */

			// Skip directories and hidden files
			if ($file->isDir() OR $file->isDot())
			{
				continue;
			}

			// Test if the filename matches
			if ( ! preg_match($file_pattern, $file->getFilename()))
			{
				continue;
			}

			// Copy the file
			$dest_file = $dest_dir.'/'.$file->getFilename();
			if ( ! copy($file->getRealPath(), $dest_file))
			{
				throw new Exception("Could not copy '".$file->getRealPath()."' to '$dest_file'");
			}
		}
	}

}

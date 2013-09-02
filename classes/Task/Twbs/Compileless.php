<?php
/**
 * Compiles the site LESS files into CSS for use on the site.
 *
 * This task will compile a full set of LESS files into a single CSS file for production use. By default, it will
 * process the file assets/less/site.bootstrap.less from the CFS and render it to HTDOCS/assets/css/site.bootstrap.css.
 *
 * The CFS paths are passed as include paths to the recess compiler, so you can add import additional dependencies from
 * modules in much the same way as you would call Kohana::find_file from PHP code. Note, however, that recess always
 * searches the directory containing the file with the @import before falling back to check the rest of the include path.
 *
 * Therefore, you can only use transparent extension (replacement) of LESS files if you plan for it. It's not possible
 * to just drop a variables.less in your application path and have it replace the standard bootstrap one, for example.
 *
 * To improve namespacing of vendor files, the fortawesome and bootstrap include paths are set two levels up from their
 * less files. If you want to import a stock bootstrap file, your less should for eg have
 * `@import bootstrap/less/bootstrap.less`.
 *
 * In most cases, you'll be able to achieve what you need by customising your site.bootstrap.less. However, for more
 * advanced customisation or to support multiple css files (eg for print) you can pass the following options:
 *
 *  - vendor-path: the absolute filesystem path to composer vendor libraries. Defaults to APPPATH.'../vendor'
 *  - public-path: the absolute filesystem path for assets. Defaults to DOCROOT.'assets';
 *  - less-input:  the name of the less file to compile - defaults to site.bootstrap(.less)
 *  - css-output:  the name of the css file to output - defaults to site.bootstrap(.css)
 *  - no-compress: don't compress the generated CSS
 *  - lint-only:   just lint the source files, don't output the css
 *  - loop-after:  rebuild the less every X seconds, running in a loop until triggered to quit
 *
 * @package   kohana-twbs
 * @category  Tasks
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @copyright 2013 inGenerator Ltd
 * @license   http://kohanaframework.org/license
 */
class Task_Twbs_Compileless extends Minion_Task {

	/**
	 * Sets the default options for the task - these use expressions so cannot be set in the field initialisation
	 */
	protected function __construct()
	{
		// Initialise the default options
		$this->_options = array(
			'vendor-path' => realpath(APPPATH.'../vendor'),
			'public-path' => DOCROOT.'assets',
			'less-input'  => 'site.bootstrap',
			'css-output'  => 'site.bootstrap',
			'no-compress' => NULL,
			'lint-only'   => NULL,
			'loop-after'  => NULL
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
		       ->rule('public-path', 'not_empty')
		       ->rule('no-compress', 'numeric')
		       ->rule('lint-only',   'numeric')
		       ->rule('loop-after',  'numeric');
	}

	/**
	 * Execute the task and compile the LESS files
	 *
	 * @param array $params the passed command options
	 *
	 * @throws Exception
	 * @return void
	 */
	protected function _execute(array $params)
	{
		// Prepare the options for recess
		$recess_opts = array();

		// Create the list of valid assets/less paths found in the CFS and format them as recess arguments
		$paths = Kohana::include_paths();
		foreach ($paths as $path)
		{
			$path .= 'assets/less';
			if (file_exists($path)) {
				$recess_opts[] = '--includePath '.escapeshellarg($path);
			}
		}

		// Add paths for fontawesome and twitter bootstrap
		// Note that these are deliberately set at organisation level to namespace the less files below
		$recess_opts[] = '--includePath '.escapeshellarg(realpath($params['vendor-path'].'/fortawesome'));
		$recess_opts[] = '--includePath '.escapeshellarg(realpath($params['vendor-path'].'/twbs'));

		// Determine whether to lint or compile, and whether the output should be compressed
		if ($params['lint-only'])
		{
			$output = '';
		}
		else
		{
			$recess_opts[] = '--compile';

			// Find the path for the output file
			$output = ' > '.escapeshellarg($params['public-path'].'/css/'.$params['css-output'].'.css');

			if ( ! $params['no-compress'])
			{
				$recess_opts[] = '--compress';
			}
		}

		// Find the input file to use
		$input = Kohana::find_file('assets/less', $params['less-input'], 'less');
		if ( ! $input)
		{
			throw new Exception("Could not find a valid input file source for {$params['less-input']}");
		}

		// Build and log the recess command
		$command = 'recess '.implode(' ', $recess_opts).' '.escapeshellarg($input).$output;
		Minion_CLI::write(Minion_CLI::color('Preparing to execute recess with following command', 'green'));
		Minion_CLI::write(Minion_CLI::color('> '.$command, 'green'));

		$loop = ($params['loop-after'] > 0);
		do
		{
			Minion_CLI::write(Minion_CLI::color('Building CSS', 'light_gray'));

			// Execute and check the result
			system($command, $exit_code);
			if ($exit_code != 0)
			{
				throw new Exception("Command '".$command.'" failed with exit code '.$exit_code);
			}
			Minion_CLI::write(Minion_CLI::color('Compiled '.$input.' to '.$output, 'green'));

			if ($loop)
			{
				sleep($params['loop-after']);
			}

		}
		while($loop);
	}

}

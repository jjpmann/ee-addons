<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2003 - 2014, EllisLab, Inc.
 * @license		http://ellislab.com/expressionengine/user-guide/license.html
 * @link		http://ellislab.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * ExpressionEngine Output Display Class
 *
 * @package		ExpressionEngine
 * @subpackage	Core
 * @category	Core
 * @author		EllisLab Dev Team
 * @link		http://ellislab.com
 */

class Vim_Output extends EE_Output {

	protected $spl = false;

	var $out_type		= 'webpage';
	var $refresh_msg	= TRUE;			// TRUE/FALSE - whether to show the "You will be redirected in 5 seconds" message.
	var $refresh_time	= 1;			// Number of seconds for redirects

	var $remove_unparsed_variables = FALSE; // whether to remove left-over variables that had bad syntax

	// --------------------------------------------------------------------

	function _display($output = '')
	{
		parent::_display($output);

		if ($this->spl) {
			$EE =& get_instance();
			require_once PATH_THIRD . '/vim_custom/libraries/SlowPageLogger.php';
			$spl = new LMO\SlowPageLogger($EE, $this->spl);
			$spl->run();
		}
		
	}

	public function enableSlowPageLogger($settings)
	{
		$this->spl = $settings;
	}


}
// END CLASS

/* End of file Vim_Output.php */
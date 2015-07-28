<?php

class Vim_EE extends EE {

	/**
	 * Add the template debugger to the output if required and then
	 * run the garbage collection routine.
	 *
	 * @access	private
	 * @return	void
	 */
	function _output($output)
	{
		parent::_output($output);

		die('yay');

	}
}

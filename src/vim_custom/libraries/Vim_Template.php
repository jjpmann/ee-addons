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
 * ExpressionEngine Template Parser Class
 *
 * @package		ExpressionEngine
 * @subpackage	Core
 * @category	Core
 * @author		EllisLab Dev Team
 * @link		http://ellislab.com
 */
class Vim_Template extends EE_Template {

		/**
	 * Fetch Template Data
	 *
	 * Takes a Template Group, Template, and Site ID and will retrieve the Template and its metadata
	 * from the database (or file)
	 *
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @param	int
	 * @return	string
	 */
	public function fetch_template($template_group, $template, $show_default = TRUE, $site_id = '')
	{

		if ($site_id == '' OR ! is_numeric($site_id))
		{
			$site_id = ee()->config->item('site_id');
		}

		$this->log_item("Retrieving Template from Database: ".$template_group.'/'.$template);

		$show_404 = FALSE;
		$template_group_404 = '';
		$template_404 = '';

		/* -------------------------------------------
		/*	Hidden Configuration Variable
		/*	- hidden_template_indicator => '.'
			The character(s) used to designate a template as "hidden"
		/* -------------------------------------------*/

		$hidden_indicator = (ee()->config->item('hidden_template_indicator') === FALSE) ? '_' : ee()->config->item('hidden_template_indicator');

		if ($this->depth == 0
			AND substr($template, 0, 1) == $hidden_indicator
			AND ee()->uri->page_query_string == '') // Allow hidden templates to be used for Pages requests
		{
			/* -------------------------------------------
			/*	Hidden Configuration Variable
			/*	- hidden_template_404 => y/n
				If a hidden template is encountered, the default behavior is
				to throw a 404.  With this set to 'n', the template group's
				index page will be shown instead
			/* -------------------------------------------*/

		}

		if ($template_group == '' && $show_default == FALSE && ee()->config->item('site_404') != '')
		{
			$treq = ee()->config->item('site_404');

			$x = explode("/", $treq);

			if (isset($x[0]) AND isset($x[1]))
			{
				ee()->output->out_type = '404';
				$this->template_type = '404';

				$template_group_404 = ee()->db->escape_str($x[0]);
				$template_404 = ee()->db->escape_str($x[1]);

				ee()->db->where(array(
					'template_groups.group_name'	=> $x[0],
					'templates.template_name'		=> $x[1]
				));

				$show_404 = TRUE;
			}
		}

		ee()->db->select('templates.*, template_groups.group_name')
			->from('templates')
			->join('template_groups', 'template_groups.group_id = templates.group_id')
			->where('template_groups.site_id', $site_id);

		// If we're not dealing with a 404, what template and group do we need?
		if ($show_404 === FALSE)
		{
			// Definitely need a template
			if ($template != '')
			{
				ee()->db->where('templates.template_name', $template);
			}

			// But do we have a template group?
			if ($show_default == TRUE)
			{
				ee()->db->where('template_groups.is_site_default', 'y');
			}
			else
			{
				ee()->db->where('template_groups.group_name', $template_group);
			}
		}

		$query = ee()->db->get();

		// Hmm, no template huh?
		if ($query->num_rows() == 0)
		{
			// is there a file we can automatically create this template from?
			if (ee()->config->item('save_tmpl_files') == 'y' && ee()->config->item('tmpl_file_basepath') != '')
			{
				$t_group = ($show_404) ? $template_group_404 : $template_group;
				$t_template = ($show_404) ? $template_404 : $template;

				if ($t_new_id = $this->_create_from_file($t_group, $t_template, TRUE))
				{
					// run the query again, as we just successfully created it
					$query = ee()->db->select('templates.*, template_groups.group_name')
						->join('template_groups', 'template_groups.group_id = templates.group_id')
						->where('templates.template_id', $t_new_id)
						->get('templates');
				}
				else
				{
					$this->log_item("Template Not Found");
					return FALSE;
				}
			}
			else
			{
				$this->log_item("Template Not Found");
				return FALSE;
			}
		}

		$this->log_item("Template Found");

		// HTTP Authentication
		if ($query->row('enable_http_auth') == 'y')
		{
			$this->log_item("HTTP Authentication in Progress");

			ee()->db->select('member_group');
			ee()->db->where('template_id', $query->row('template_id'));
			$results = ee()->db->get('template_no_access');

			$not_allowed_groups = array();

			if ($results->num_rows() > 0)
			{
				foreach($results->result_array() as $row)
				{
					$not_allowed_groups[] = $row['member_group'];
				}
			}

			ee()->load->library('auth');
			ee()->auth->authenticate_http_basic(
				$not_allowed_groups,
				$this->realm
			);
		}

		// Is the current user allowed to view this template?
		if ($query->row('enable_http_auth') != 'y' && $query->row('no_auth_bounce')  != '')
		{
			$this->log_item("Determining Template Access Privileges");

			if (ee()->session->userdata('group_id') != 1)
			{
				ee()->db->select('COUNT(*) as count');
				ee()->db->where('template_id', $query->row('template_id'));
				ee()->db->where('member_group', ee()->session->userdata('group_id'));
				$result = ee()->db->get('template_no_access');

				if ($result->row('count') > 0)
				{
					if ($this->depth > 0)
					{
						return '';
					}

					$query = ee()->db->select('a.template_id, a.template_data,
						a.template_name, a.template_type, a.edit_date,
						a.save_template_file, a.cache, a.refresh, a.hits,
						a.allow_php, a.php_parse_location, a.protect_javascript,
						b.group_name')
						->from('templates a')
						->join('template_groups b', 'a.group_id = b.group_id')
						->where('template_id', $query->row('no_auth_bounce'))
						->get();
				}
			}
		}

		if ($query->num_rows() == 0)
		{
			return FALSE;
		}

		$row = $query->row_array();

		// Is PHP allowed in this template?
		if ($row['allow_php'] == 'y')
		{
			$this->parse_php = TRUE;

			$this->php_parse_location = ($row['php_parse_location'] == 'i') ? 'input' : 'output';
		}

		// Increment hit counter
		if (($this->hit_lock == FALSE OR $this->hit_lock_override == TRUE) &&
			ee()->config->item('enable_hit_tracking') != 'n')
		{
			$this->template_hits = $row['hits'] + 1;
			$this->hit_lock = TRUE;

			ee()->db->update(
				'templates',
				array('hits' 		=> $this->template_hits),
				array('template_id'	=> $row['template_id'])
			);
		}

		// Set template edit date
		$this->template_edit_date = $row['edit_date'];
		$this->protect_javascript = ($row['protect_javascript'] == 'y') ? TRUE : FALSE;

		// Set template type for our page headers
		if ($this->template_type == '')
		{
			$this->template_type = $row['template_type'];
			ee()->functions->template_type = $row['template_type'];

			// If JS or CSS request, reset Tracker Cookie
			if ($this->template_type == 'js' OR $this->template_type == 'css')
			{
				if (count(ee()->session->tracker) <= 1)
				{
					ee()->session->tracker = array();
				}
				else
				{
					$removed = array_shift(ee()->session->tracker);
				}

				ee()->input->set_cookie('tracker', serialize(ee()->session->tracker), '0');
			}
		}

		if ($this->depth > 0)
		{
			$this->embed_type = $row['template_type'];
		}

		// Cache Override

		// We can manually set certain things not to be cached, like the
		// search template and the member directory after it's updated

		// Note: I think search caching is OK.
		// $cache_override = array('member' => 'U', 'search' => FALSE);

		$cache_override = array('member');

		foreach ($cache_override as $val)
		{
			if (strncmp(ee()->uri->uri_string, "/{$val}/", strlen($val) + 2) == 0)
			{
				$row['cache'] = 'n';
			}
		}

		// Retreive cache
		$this->cache_hash = md5($site_id.'-'.$template_group.'-'.$template);

		if ($row['cache'] == 'y')
		{
			$cache_contents = $this->fetch_cache_file($this->cache_hash, 'template', array('cache' => 'yes', 'refresh' => $row['refresh']));

			if ($this->cache_status == 'CURRENT')
			{
				$row['template_data'] = $cache_contents;

				// -------------------------------------------
				// 'template_fetch_template' hook.
				//  - Access template data prior to template parsing
				//
					if (ee()->extensions->active_hook('template_fetch_template') === TRUE)
					{
						ee()->extensions->call('template_fetch_template', $row);
					}
				//
				// -------------------------------------------

				return $this->convert_xml_declaration($cache_contents);
			}
		}

		// Retrieve template file if necessary
		if ($row['save_template_file'] == 'y')
		{
			$site_switch = FALSE;

			if (ee()->config->item('site_id') != $site_id)
			{
				$site_switch = ee()->config->config;

				if (isset($this->site_prefs_cache[$site_id]))
				{
					ee()->config->config = $this->site_prefs_cache[$site_id];
				}
				else
				{
					ee()->config->site_prefs('', $site_id);
					$this->site_prefs_cache[$site_id] = ee()->config->config;
				}
			}

			if (ee()->config->item('save_tmpl_files') == 'y'
				AND ee()->config->item('tmpl_file_basepath') != '')
			{
				$this->log_item("Retrieving Template from File");
				ee()->load->library('api');
				ee()->api->instantiate('template_structure');

				$basepath = rtrim(ee()->config->item('tmpl_file_basepath'), '/').'/';

				$basepath .= ee()->config->item('site_short_name').'/'
					.$row['group_name'].'.group/'.$row['template_name']
					.ee()->api_template_structure->file_extensions($row['template_type']);

				if (file_exists($basepath))
				{
					$row['template_data'] = file_get_contents($basepath);
				}
			}

			if ($site_switch !== FALSE)
			{
				ee()->config->config = $site_switch;
			}
		}

		// standardize newlines
		$row['template_data'] =  str_replace(array("\r\n", "\r"), "\n", $row['template_data']);

		// -------------------------------------------
		// 'template_fetch_template' hook.
		//  - Access template data prior to template parsing
		//
			if (ee()->extensions->active_hook('template_fetch_template') === TRUE)
			{
				ee()->extensions->call('template_fetch_template', $row);
			}
		//
		// -------------------------------------------

		// remember what template we're on
		$this->group_name = $row['group_name'];
		$this->template_id = $row['template_id'];
		$this->template_name = $row['template_name'];

		return $this->convert_xml_declaration($this->remove_ee_comments($row['template_data']));
	}

}
// END CLASS

/* End of file Vim_Template.php */
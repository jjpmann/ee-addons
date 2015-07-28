<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name' => 'Vim Custom',
	'pi_version' => '1.0.1',
	'pi_author' => 'Vim Interactive',
	'pi_author_url' => 'expressionengine.com',
	'pi_description' => 'Custom Functions',
	'pi_usage' => Vim_custom::usage()
);

class Vim_custom {

	/**
	*
	*/
	function __construct()
	{
		$this->EE =& get_instance();

	}
	// ------------------------------------------------------------------------

	/** TAG - global_vars
	 * splits out global vars
	 */
	public function global_vars() 
	{

		$globals = $this->EE->config->_global_vars;
		
		$_globals = print_r($globals,true);

		echo "<pre>"; var_dump( $this->EE->config ); exit;
		
		$_return = '<pre style="font-size:14px;padding:20px;color:#444;line-height:20px;">'.$_globals.'</pre>';
		
		return $_return;

		//$style = function($str){ return '<pre style="font-size:14px;padding:20px;color:#444;line-height:20px;">'.print_r($str,true).'</pre>'; };
		//return $style($globals);
	}		
	// ------------------------------------------------------------------------
	

	/** TAG - config_vars
	 * splits out config vars
	 */
	public function config_vars() 
	{

		$config = $this->EE->config->config;
		
		$_vars = print_r($config,true);

		//echo "<pre>"; var_dump( $_vars ); exit;
				
		$_return = '<pre style="font-size:14px;padding:20px;color:#444;line-height:20px;">'.$_vars.'</pre>';
		
		return $_return;

		//$style = function($str){ return '<pre style="font-size:14px;padding:20px;color:#444;line-height:20px;">'.print_r($str,true).'</pre>'; };
		//return $style($globals);
	}		
	// ------------------------------------------------------------------------
	


	/** TAG - ENV
	 * 		prints out environment settings
	 */
	public function env_dislay($message_only=false)
	{
		
		if( !defined('ENVIRONMENT') ) return false;
		
		$message = 'Environment: ' . ENVIRONMENT . ' | Your IP: '. $_SERVER['REMOTE_ADDR'];

		if( $message_only ) return $message;

		$return = '<div style="position:absolute; top:0; left:200px; font-size:12px; padding: 6px 10px; background:#6cf72c; color:#000; z-index:999; text-align:center;">';
		$return.= $message;
		$return.= '</div>';

		return $return;
	}
	// ----------------------------------------------------------------
	

	/**
	 * Renders XML for channel entries. Uses the Channel module
	 *
	 * @access public
	 * @return string XML sitemap entries for channel entries
	 **/
	public function entries_xml()
	{
		// Make a couple of suggestions about default return values
		$this->EE->TMPL->tagparams['disable'] = $this->EE->TMPL->fetch_param('disable', "categories|custom_fields|category_fields|member_data|pagination");
		$this->EE->TMPL->tagparams['dynamic'] = $this->EE->TMPL->fetch_param('dynamic', "no");
		$this->EE->TMPL->tagparams['limit'] = $this->EE->TMPL->fetch_param('limit', 500);
		$site_id = isset($tag_params["site_id"]) ? $tag_params["site_id"] : SITE_ID;

		$channelEntries		= $this->_getEntriesWithMetadata();

		// If there are no channel entries found, return FALSE
		if($channelEntries === FALSE || $channelEntries->query->num_rows() == 0)
			return FALSE;

		$site_pages = $this->EE->config->item('site_pages');
		$site_pages = $site_pages[$site_id];

		$use_page_url		= ($this->EE->TMPL->fetch_param('use_page_url') == "no") ? FALSE : TRUE;
		$loc				= $this->EE->TMPL->fetch_param('loc') ? $this->EE->TMPL->fetch_param('loc') : FALSE;
		$wrap_output_with_xml_tags = ($this->EE->TMPL->fetch_param('wrap_output_with_xml_tags') == "yes") ? TRUE : FALSE;
		$format_output 		= ($this->EE->TMPL->fetch_param('format_output') == "yes") ? TRUE : FALSE;

		$ret 		= '';
		$tagdata = "
	<url>
		<loc>{sitemap_entry_loc}</loc>
		<lastmod>{sitemap_last_mod}</lastmod>
		<changefreq>{sitemap_change_frequency}</changefreq>
		<priority>{sitemap_priority}</priority>
	</url>";

		// loop over the results
		foreach ($channelEntries->query->result_array as $key => &$entry)
		{
			// Load the page url
			if ($use_page_url AND isset($site_pages['uris'][$entry['entry_id']]) === TRUE) {
				$entry["sitemap_entry_loc"] = $this->EE->functions->create_url($site_pages['uris'][$entry['entry_id']]);
			} else {
				$entry["sitemap_entry_loc"] = $this->EE->TMPL->parse_variables_row($loc, $entry);
			}

			if($entry["sitemap_entry_loc"] == FALSE)
			{
				unset($channelEntries->query->result_array[$key]);
				continue;
			}

			$entry["sitemap_last_mod"] = date(DATE_W3C, $this->EE->localize->timestamp_to_gmt($entry['edit_date']));
		}

		if($wrap_output_with_xml_tags) {
			$ret = "<?xml version='1.0' encoding='UTF-8'?>\n<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>";
		}

		if(count($channelEntries->query->result_array) > 0) {
			$channelEntries->query->result_array = array_values($channelEntries->query->result_array);
			$ret .= $this->EE->TMPL->parse_variables($tagdata, $channelEntries->query->result_array);
		}

		if($wrap_output_with_xml_tags) {
			$ret .= "\n</urlset>";
		}

		return (!$format_output) ? str_replace(array("\n", "\t"), "", $ret) : $ret;
	}


	/**
	* function
	*	- returns true if user is logged into imis
	*/
	public function events_fix()
	{
		$date1 = date("m d y", $this->EE->TMPL->fetch_param('start'));
		$date2 = date("m d y", $this->EE->TMPL->fetch_param('end'));

	//	echo "<pre>"; var_dump( $date1, $date2 ); exit;

		if( $date1 && $date2 and $date1 != $date2 )
		{
			return $this->EE->TMPL->tagdata;
		}

		// return some data
	}
	// ------------------------------------------------------------------------


	// adfadf
	public function video_nav()
	{

		$tagdata = $this->EE->TMPL->tagdata;

		$options = $this->video_types();

		if( !$options )
		{
			return false;
		}


		$vars = array();

		foreach( $options as $key => $val )
		{
			$vars[] = array( 'key' => $key, 'val' => $val );
		}

		return $this->EE->TMPL->parse_variables($tagdata, $vars);

	}
	// ------------------------------------------------------------------------


	// adfadf
	public function filter_value()
	{

		$key = $this->EE->TMPL->fetch_param('key');

		$options = $this->video_types();

		if( isset($options[$key]) )
		{
			return $options[$key];
		}

		return false;

	}
	// ------------------------------------------------------------------------


	private function video_types()
	{
		$qry = $this->EE->db->where('field_name', 'v_type')
							->from('exp_channel_fields')
							->get();

		if( ! $qry->num_rows > 0 ) return false;

		$settings = $qry->row(0)->field_settings;
		$settings = unserialize( base64_decode($settings) );

		$options = $settings['options'];

		return $options;
	}
	// ------------------------------------------------------------------------

	public function practice_areas()
	{

	}

	// get ids of our_people entries that have videos
	public function practice_video_count()
	{

		$tagdata = $this->EE->TMPL->tagdata;

		$this->get_field_id('item_asin');

		$field 		= 'field_id_'.$this->get_field_id('v_practice_areas');	// Related Practice Areas	{v_practice_areas}
		$type_field = 'field_id_'.$this->get_field_id('v_type');			// Filter Type				{v_type}
		$channel 	= '19';													// Videos					{videos}

		$type = $this->EE->TMPL->fetch_param('type');

		$this->EE->db->select("t.entry_id, $field")
							->from('channel_titles AS t')
							->join('channel_data as d','d.entry_id = t.entry_id')
							->where('t.channel_id', $channel)
							->where('t.status <>','closed')
							->where("d.$field <>",'')
							->where("d.$type_field",'practice-areas');
							
							
		if( $type )
			$this->EE->db->like($field, $type);

		$qry = $this->EE->db->get();

		//echo "<pre>"; var_dump( $this->EE->db->last_query(), $qry->result() ); exit;

		//echo "<pre>"; var_dump( $this->EE->db->last_query(),$qry->result() ); exit;
		if( $qry->num_rows() > 0 )
		{
			$return = array();
			foreach( $qry->result() as $key => $row )  
			{
				$return[] = $row->entry_id;
			}
			return implode('|', $return);
		}
		return 0;

	}
	// ------------------------------------------------------------------------


	// get ids of our_people entries that have videos
	public function people_videos()
	{

		$tagdata = $this->EE->TMPL->tagdata;

		$field = 'field_id_'.$this->get_field_id('p_video'); 	// Profile Video 	{p_video}
		$channel = '5';											// Oup People 		{our_people}

		$vars['vim_entry_ids'] = '';

		$qry = $this->EE->db->select('entry_id')
							->where('channel_id', $channel)
							->where("$field <>", '')
							->get('exp_channel_data');

		//echo "<pre>"; var_dump( $this->EE->db->last_query(),$qry->result() ); exit;

		if( $qry->num_rows() > 0 )
		{
			$ids = array();

			foreach( $qry->result() as $row )
			{
				$ids[] = $row->entry_id;
			}

			$vars['vim_entry_ids']  = implode('|', $ids );
		}

		return $this->EE->TMPL->parse_variables_row($tagdata, $vars, false);

	}
	// ------------------------------------------------------------------------


	// show video sections
	public function sections()
	{
		$tagdata = $this->EE->TMPL->tagdata;

		$exclude = $this->EE->TMPL->fetch_param('exclude');

		$qry = $this->EE->db->where('field_name','v_section')
							->get('exp_channel_fields');

		if( ! $qry->num_rows() > 0 ) return false;

		$row = $qry->row(0);

		$settings =  unserialize( base64_decode( $row->field_settings ) );

		$options = $settings['options'];

		$vars = array();

		foreach ($options as $key => $val)
		{
			if( $key == $exclude ) continue;
			$var['key'] = $key;
			$var['val'] = $val;

			$vars[] = $var;
		}

		//echo "<pre>"; var_dump( $vars ); exit;

		return $this->EE->TMPL->parse_variables($tagdata,$vars);

	}
	// ------------------------------------------------------------------------


	// get video section label
	public function section()
	{

		$var = $this->EE->TMPL->fetch_param('var');

		$qry = $this->EE->db->where('field_name','v_section')
							->get('exp_channel_fields');

		if( $qry->num_rows() != 1 ) return false;

		$row = $qry->row(0);

		$settings =  unserialize( base64_decode( $row->field_settings ) );

		$options = $settings['options'];

		//echo "<pre>"; var_dump( $options ); exit;

		if( isset($options[$var]) ) return $options[$var];

		return '';
	}
	// ------------------------------------------------------------------------


	public function videos_xml()
	{
		$better_meta = new nsm_better_meta_super();
		//echo "<pre>"; var_dump( $better_meta ); exit;

		return $better_meta->entries_xml();
	}
	// ------------------------------------------------------------------------


	/**
	* usage
	*	- description of plugin
	*/
	public function usage()
	{
		return 'Instructions here or Ask steve!';
	}
	// ------------------------------------------------------------------------
	
	
	/**
	 * channel_data - get field id
	 */
	private function get_field_id($field_name) 
	{
		$qry = $this->EE->db->select('field_id')
					->from('channel_fields')
					->where('field_name', $field_name)
					->limit(1)
					->get();

		if ( ! $qry->num_rows() > 0 ) return false;

		return $qry->row(0)->field_id;
		
	}	
	// ------------------------------------------------------------------------


}

require_once PATH_THIRD . 'nsm_better_meta/mod.nsm_better_meta.php';

class nsm_better_meta_super extends Nsm_better_meta {

	/**
	 * Renders XML for channel entries. Uses the Channel module
	 *
	 * @access public
	 * @return string XML sitemap entries for channel entries
	 **/
	public function entries_xml()
	{
		// Make a couple of suggestions about default return values
		$this->EE->TMPL->tagparams['disable'] = $this->EE->TMPL->fetch_param('disable', "categories|custom_fields|category_fields|member_data|pagination");
		$this->EE->TMPL->tagparams['dynamic'] = $this->EE->TMPL->fetch_param('dynamic', "no");
		$this->EE->TMPL->tagparams['limit'] = $this->EE->TMPL->fetch_param('limit', 500);
		$site_id = isset($tag_params["site_id"]) ? $tag_params["site_id"] : SITE_ID;

		$channelEntries		= $this->_getEntriesWithMetadata();

		// If there are no channel entries found, return FALSE
		if($channelEntries === FALSE || $channelEntries->query->num_rows() == 0)
			return FALSE;

		$site_pages = $this->EE->config->item('site_pages');
		$site_pages = $site_pages[$site_id];

		$use_page_url		= ($this->EE->TMPL->fetch_param('use_page_url') == "no") ? FALSE : TRUE;
		$loc				= $this->EE->TMPL->fetch_param('loc') ? $this->EE->TMPL->fetch_param('loc') : FALSE;
		$wrap_output_with_xml_tags = ($this->EE->TMPL->fetch_param('wrap_output_with_xml_tags') == "yes") ? TRUE : FALSE;
		$format_output 		= ($this->EE->TMPL->fetch_param('format_output') == "yes") ? TRUE : FALSE;


		$ret 		= '';
		$tagdata = "
	<url>
		<loc>{sitemap_entry_loc}</loc>
		<lastmod>{sitemap_last_mod}</lastmod>
		<changefreq>{sitemap_change_frequency}</changefreq>
		<priority>{sitemap_priority}</priority>
	</url>";

		$tagdata = $this->EE->TMPL->tagdata;

		// loop over the results
		foreach ($channelEntries->query->result_array as $key => &$entry)
		{
			// Load the page url
			if ($use_page_url AND isset($site_pages['uris'][$entry['entry_id']]) === TRUE) {
				$entry["sitemap_entry_loc"] = $this->EE->functions->create_url($site_pages['uris'][$entry['entry_id']]);
			} else {
				$entry["sitemap_entry_loc"] = $this->EE->TMPL->parse_variables_row($loc, $entry);
			}

			if($entry["sitemap_entry_loc"] == FALSE)
			{
				unset($channelEntries->query->result_array[$key]);
				continue;
			}

			$entry["sitemap_last_mod"] = date(DATE_W3C, $this->EE->localize->timestamp_to_gmt($entry['edit_date']));
		}

		if($wrap_output_with_xml_tags) {
			$ret = "<?xml version='1.0' encoding='UTF-8'?>\n<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>";
		}

		if(count($channelEntries->query->result_array) > 0) {
			$channelEntries->query->result_array = array_values($channelEntries->query->result_array);
			$ret .= $this->EE->TMPL->parse_variables($tagdata, $channelEntries->query->result_array);
		}

		if($wrap_output_with_xml_tags) {
			$ret .= "\n</urlset>";
		}

		$this->EE->load->library('typography');
		$ret = $this->EE->typography->parse_file_paths($ret);

		return (!$format_output) ? str_replace(array("\n", "\t"), "", $ret) : $ret;
	}
}

/* End of file pi.vim_custom.php */
/* Location: ./system/expressionengine/third_party/vim_custom/pi.vim_custom.php */
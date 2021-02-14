<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Selectatron Migrator Module Control Panel File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Iain Urquhart
 * @link		
 */

class Selectatron_migrator_mcp {
	
	public $return_data;
	
	private $_base_url;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{	
		$this->_base_url = ee('CP/URL')->make('addons/settings/selectatron_migrator')->compile();
		ee()->load->library('table');
		ee()->cp->set_right_nav(array(
			'module_home'	=> $this->_base_url,
			// Add more right nav items here.
		));
	}
	
	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @return 	void
	 */
	public function index()
	{
		ee()->view->cp_page_title = lang('selectatron_migrator_module_name');

		$vars = array();
		$vars['base_url'] = $this->_base_url;
		$vars['cleanup_required'] = FALSE;

		// get all our selectatron fields
		$sel_fields = ee()->db->get_where(
			'channel_fields', 
			 array(
			 	'field_type' => 'the_selectatron'
			 )
		)->result_array();

		foreach($sel_fields as $field)
		{
			// find out if the field is storing native relatioships.
			$field['stores_relationships'] = FALSE;

			if($field['field_settings'] != '')
			{
				//decode the settings
				$field['field_settings'] = unserialize(base64_decode($field['field_settings']));

				// storing rels?
				if(isset($field['field_settings']['store_ee_relationships']) && $field['field_settings']['store_ee_relationships'] == 1)
				{
					$field['stores_relationships'] = TRUE;
				}

				// what channels?
				if(isset($field['field_settings']['channel_preferences']) && $field['field_settings']['channel_preferences'] != '')
				{
					$field['channels'] = $field['field_settings']['channel_preferences'];
				}

			}

			$vars['fields'][] = $field;
		}	

		// remove any leftovers, only if the tables have all been converted
		// the native EE upgrade to multi relationships creates a bunch of crap we don't need.
		if(!isset($vars['fields']))
		{

			$munted = ee()->db->get_where('relationships', array('field_id' => 0));

			if ($munted->num_rows() > 0)
			{
				$vars['cleanup_required'] = TRUE;
			}

		}

		return ee()->load->view('index', $vars, TRUE);

	}

	public function cleanup_leftovers()
	{
		ee()->db->delete('relationships', array('field_id' => 0)); 

		ee()->session->set_flashdata('message_success', 'Leftovers are cleaned up.');
		ee()->functions->redirect( $this->_base_url );
	}

	public function update_field()
	{
		$field_id = ee()->input->get('id');
		$channels = explode('|', ee()->input->get('channels') );

		if(!$field_id)
			show_error('No field supplied');

		$field = 'field_id_'.$field_id;

		ee()->db->select('entry_id, channel_id, '.$field);
		ee()->db->where( $field.' !=', ' ');
		$rows = ee()->db->get('channel_data')->result_array();


		foreach($rows as $row)
		{
			$rels = explode('|', $row[ $field ] );

			$order = 0;

			foreach($rels as $rel)
			{
				ee()->db->select('entry_id, channel_id');
				$rel_data = ee()->db->get_where(
					'channel_titles',
					array(
						'entry_id' => $rel
					)
				)->row_array();

				if(isset($rel_data['channel_id']))
				{

					if(in_array($rel_data['channel_id'], $channels))
					{

						ee()->db->where('parent_id', $row['entry_id']);
						ee()->db->where('child_id', $rel_data['entry_id'] );
						$existing_rel = ee()->db->get('relationships');

						// base data for insert or update
						$data = array(
							'field_id' => $field_id,
							'order' => $order++
						);

						// existing rel found
						if ($existing_rel->num_rows() > 0)
						{
							ee()->db->where('parent_id', $row['entry_id']);
							ee()->db->where('child_id', $rel_data['entry_id'] );
							ee()->db->update('relationships', $data);
						}
						// create the rel
						else
						{
							$data['parent_id'] = $row['entry_id'];
							$data['child_id'] = $rel_data['entry_id'];

							ee()->db->insert('relationships', $data);
						}
						
					}
				}
			}

		}

		// prep our new native rel field settings.
		$settings = array(
			'channels' => $channels,
			'expired' => 0,
			'future' => 0,
			'categories' => array(),
			'authors' => array(),
			'statuses' => array(),
			'limit' => 100,
			'order_field' => 'title',
			'order_dir' => 'asc',
			'allow_multiple' => 1,
			'field_show_smileys' => 'n',
			'field_show_glossary' => 'n',
			'field_show_spellcheck' => 'n',
			'field_show_formatting_btns' => 'n',
			'field_show_file_selector' => 'n',
			'field_show_writemode' => 'n'
		);

		$data = array(
			'field_type' => 'relationship',
			'field_settings' => base64_encode(serialize($settings))
		);
		ee()->db->where('field_id', $field_id);
		ee()->db->update('channel_fields', $data);

		ee()->session->set_flashdata('message_success', 'Field Migrated!');
		ee()->functions->redirect( $this->_base_url );

	}

	
}
/* End of file mcp.selectatron_migrator.php */
/* Location: /system/expressionengine/third_party/selectatron_migrator/mcp.selectatron_migrator.php */
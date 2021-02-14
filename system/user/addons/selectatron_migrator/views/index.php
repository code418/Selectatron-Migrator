<?php

	if(isset($fields))
	{
		$this->table->set_template($cp_table_template);
		$this->table->set_heading(
			'ID',
			'Label',
			'Name',
			''
		);
		foreach($fields as $field)
		{	

			$action_url = $base_url.AMP.'method=update_field'.AMP.'id='.$field['field_id'].AMP.'channels='.$field['channels'];

			$action = ($field['stores_relationships']) ? anchor($action_url, 'Convert to EE Relationship Field') : '--';


			$this->table->add_row(
				$field['field_id'],
				$field['field_label'],
				$field['field_name'],
				$action
			);
		}
		echo $this->table->generate();
	}
	else
	{

		if($cleanup_required)
		{
			echo anchor($base_url.AMP.'method=cleanup_leftovers', 'Cleanup leftovers from the migration');
		}
		else
		{
			echo "Migration complete";
		}
		
	}

?>
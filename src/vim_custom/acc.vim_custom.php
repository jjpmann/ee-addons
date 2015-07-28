<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine Developer Accessory
 *
 * @package     Developer
 * @category    Accessory
 * @description Adds functionality for developers
 * @author      Ben Croker
 * @link        http://www.putyourlightson.net/developer
 */
 

class vim_custom_acc
{
    var $name           = 'vim custom';
    var $id             = 'vim';
    var $version        = '0.1';
    var $description    = 'Adds custom JS to CP';
    var $sections       = array();
    
    // --------------------------------------------------------------------
    
    /**
     * Constructor
     */
    function __construct()
    {
        $this->EE =& get_instance();

        // -------------------------------------------
        //  Load the helper
        // -------------------------------------------

        if (! class_exists('Playa_Helper'))
        {
            require_once PATH_THIRD.'playa/helper.php';
        }

        $this->helper = new Playa_Helper();
    } 

    // --------------------------------------------------------------------
    
    /**
    * Set Sections
    */
    function set_sections()
    {
        
        // check if super admin
        if ($this->EE->session->userdata('group_id') != 1)
        {
            return;
        }
        
        // $qry = $this->EE->db->where('field_type','playa')->get('channel_fields');

        // if (!$qry->num_rows() > 0) {
        //     return false;
        // }

        // $playa_fields = array();

        // foreach ($qry->result() as $key => $field) {
        //     $playa_fields[] = $field->field_id;
        // }

        $channel_id = $this->EE->input->get('channel_id');
        $entry_id = $this->EE->input->get('entry_id');

        // get field group from channel
        $qry = $this->EE->db->select('F.*')
                ->from('channels C')
                ->join('channel_fields F','C.field_group = F.group_id')
                ->where('C.channel_id', $channel_id)
                ->where('F.field_type','playa')
                ->get();


        if (!$qry->num_rows() > 0) {
            return false;
        }

        $playa_fields = array();

        $_params = json_decode('{show_expired":"","show_future_entries":"yes","only_show_editable_entries":"","channel_id":["5"],"category":[],"member_groups":[],"author_id":[],"status":["open"],"site_id":[]}', true);

        foreach ($qry->result() as $key => $field) {

            $id = 'field_id_'.$field->field_id;

            $playa_fields[$id] = (array) $field;            
            $playa_fields[$id]['id'] = $id;
            $playa_fields[$id]['entries'] = $this->entries($id, 5, 'field_id_22');

        }


        // field_id_123-option-65
        
        $playa_custom = json_encode($playa_fields);
        
        $playa_custom_fields = <<<EJS
        
        <script type="text/javascript" charset="utf-8">
        
            console.log( 'vim_custom_acc' );
            var playa_custom_fields = $playa_custom;
            
            $(document).ready(function() {
                $.each(playa_custom_fields,function(i,v){
                    
                    $.each(v.entries,function(i,v){
                        var \$li = $('#'+i);
                        if (\$li.html()) {
                            \$li.html( \$li.html().replace(v.title,v.custom) );
                            
                        }
                        
                        
                   });
                });
            });

        </script>   
EJS;
        $this->sections[] = $playa_custom_fields;       
        

    }

    private function entries($id, $channel_id, $custom_field)
    {
        $DB = $this->EE->db;

        $sql = "SELECT T.entry_id, T.title, CONCAT(`T`.`title`,' ',`D`.`$custom_field`) custom 
                FROM exp_channel_titles T 
                JOIN exp_channel_data D ON T.entry_id = D.entry_id 
                WHERE T.channel_id = {$channel_id};";

        $qry = $DB->query($sql);

        if (!$qry->num_rows > 0) {
            return false;
        }  

        $items = array();

        foreach ($qry->result_array()  as $row) {
            $key = $id . '-option-' . $row['entry_id'];
            $items[$key] = $row;
        }

       return $items;
        

    }
    
}
// END CLASS

/* End of file acc.developer.php */
/* Location: ./system/expressionengine/third_party/developer/acc.developer.php */
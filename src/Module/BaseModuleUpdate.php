<?php

namespace EE\Addons\Module;

abstract class BaseModuleUpdate
{
    public $version = '0.0.0';

    /**
     * Installation Method.
     *
     * @return bool
     */
    public function install()
    {
        ee()->db->insert('modules', [
            'module_name'        => 'Test',
            'module_version'     => $this->version,
            'has_cp_backend'     => 'n',
            'has_publish_fields' => 'n',
        ]);

        return true;
    }

    /**
     * Uninstall.
     *
     * @return bool
     */
    public function uninstall()
    {
        ee()->db->where('class', 'Test')->delete('actions');

        $mod_id = ee()->db->select('module_id')->get_where('modules', ['module_name' => 'Test'])->row('module_id');
        ee()->db->where('module_id', $mod_id)->delete('module_member_groups');
        ee()->db->where('module_name', 'Test')->delete('modules');

        // Custom Tables Uninstall
        /*
        ee()->load->dbforge();
        ee()->dbforge->drop_table('table_name');
        */

        return true;
    }

    /**
     * Module Updater.
     *
     * @return bool
     */
    public function update($current = '')
    {
        return true;
    }
}

/* End of file upd.test.php */
/* Location: /system/expressionengine/third_party/test/upd.test.php */

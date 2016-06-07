<?php

namespace EE\Addons\Extension;

abstract class BaseExtension
{
    public $name = 'BaseExtension';
    public $version = '0.0.0';
    public $description = '';
    public $settings_exist = 'n';
    public $docs_url = '';
    public $settings = [];

    protected $settings_default = [];
    protected $package = '';
    protected $hooks = [];
    protected $current_hooks = [];

    protected $EE;
    protected $output;
    protected $spl;

    public function __construct($settings = '')
    {
        $this->package = get_called_class();

        $this->settings = $settings;

        //Allow for config overrides
        $this->applyConfigOverrides();

        // -------------------------------------------
        //  Prepare Hooks
        // -------------------------------------------
        $qry = ee()->db->get_where('extensions', ['class' => $this->package]);

        if ($qry->num_rows()) {
            foreach ($qry->result_array() as $key => $item) {
                $this->current_hooks[$item['hook']] = $item['method'];
            }
        }

        // // -------------------------------------------
        // //  Prepare Cache
        // // -------------------------------------------
        // if (! isset(ee()->session->cache[$this->package])) {
        //     ee()->session->cache[$this->package] = array();
        // }
        // $this->cache =& ee()->session->cache[$this->package];

        // $this->cache['settings'] = $this->settings;
    }

    public function settings_form($current)
    {
        return $this->settingsForm();
    }

    //abstract public function settingsForm();

    public function save_settings()
    {
        return $this->settingsSave();
    }

    //abstract public function settingsSave();

    /**
     * Settings.
     *
     * This function returns the settings for the extensions
     *
     * @return settings array
     */
    public function settings()
    {
       return $settings;
    }

    /**
     * Config Overrides.
     *
     * This function will merge with config overrides
     *
     * @return void
     */
    public function applyConfigOverrides()
    {
        // init
        $config_items = [];

        foreach ($this->settings_default as $key => $value) {
            if (ee()->config->item($key)) {
                $config_items[$key] = ee()->config->item($key);
            }
        }

        if (is_array($this->settings)) {
            $this->settings = array_merge($this->settings, $config_items);
        }
    }

    /**
     * Log to the developer log if the setting is turned on.
     *
     * @return void
     */
    public function _log($message)
    {
        ee()->load->library('logger');
        ee()->load->library('user_agent');

        ee()->logger->developer($this->package.' - '.$message);
    }

    /**
     * Activate Extension.
     *
     * This function enters the extension into the exp_extensions table
     *
     * @see http://codeigniter.com/user_guide/database/index.html for
     * more information on the db class.
     *
     * @return void
     */
    public function activate_extension()
    {
        // Add any new hooks
        $this->add_new_hooks();
    }

    /**
     * Add New Hooks.
     */
    private function add_new_hooks()
    {

        // ADD New Actions
        foreach ($this->hooks as $hook => $method) {
            // check if its not installed
            if (!isset($this->current_hooks[$hook])) {
                $data = [
                    'class'     => $this->package,
                    'method'    => $method,
                    'hook'      => $hook,
                    'settings'  => serialize($this->settings()),
                    'priority'  => 10,
                    'version'   => $this->version,
                    'enabled'   => 'y',
                ];

                ee()->db->insert('extensions', $data);
            }
        }
    }

    // ----------------------------------------------------------------

    /**
     * Update Extension.
     *
     * This function performs any necessary db updates when the extension
     * page is visited
     *
     * @return mixed void on update / false if none
     */
    public function update_extension($current = '')
    {
        if ($current == '' || $current == $this->version) {
            return false;
        }

        if ($current < '0.1') {
            // Update to version 1.0
        }

        // Add any new hooks
        $this->add_new_hooks();

        ee()->db->where('class', $this->package);
        ee()->db->update(
            'extensions',
            ['version' => $this->version]
        );
    }

    /**
     * Disable Extension.
     *
     * This method removes information from the exp_extensions table
     *
     * @return void
     */
    public function disable_extension()
    {
        ee()->db->where('class', $this->package);
        ee()->db->delete('extensions');
    }
}

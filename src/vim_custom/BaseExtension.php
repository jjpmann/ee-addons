<?php

if (!function_exists('ee')) {
    function ee(){
        static $EE;
        if (!EE) {
            $EE =& get_instance();
        }
        return $EE;
    }
}

class BaseExtension
{

    public $name             = 'BaseExtension';
    public $version          = '0.0.1';
    public $description      = '';
    public $settings_exist   = 'n';
    public $docs_url         = '';
    public $settings         = array();

    protected $settings_default = array();
    protected $package          = '';
    protected $hooks            = array();
    protected $current_hooks    = array();

    public function __construct($settings = '')
    {

        $this->EE =& get_instance();
        $this->settings = $settings;

        //Allow for config overrides
        $this->applyConfigOverrides();

        // -------------------------------------------
        //  Prepare Hooks
        // -------------------------------------------
        $qry = $this->EE->db->get_where('extensions', array('class' => $this->package));

        if ($qry->num_rows()) {
            foreach ($qry->result_array() as $key => $item) {
                $this->current_hooks[$item['hook']] = $item['method'];
            }
        }

        // -------------------------------------------
        //  Prepare Cache
        // -------------------------------------------
        if (! isset($this->EE->session->cache[$this->package])) {
            $this->EE->session->cache[$this->package] = array();
        }
        $this->cache =& $this->EE->session->cache[$this->package];

        $this->cache['settings'] = $this->settings;

        // -------------------------------------------
        // Load Log ??
        // -------------------------------------------
    }

    /**
     * Settings
     *
     * This function returns the settings for the extensions
     *
     * @return settings array
     */
    // public function settings()
    // {
    // //   return $settings;
    // }

    /**
     * Config Overrides
     *
     * This function will merge with config overrides
     *
     * @return void
     */
    public function applyConfigOverrides()
    {
        // init
        $config_items = array();

        foreach ($this->settings_default as $key => $value) {
            if ($this->EE->config->item($key)) {
                $config_items[$key] = $this->EE->config->item($key);
            }
        }
        
        if (is_array($this->settings)) {
            $this->settings = array_merge($this->settings, $config_items);
        }
    }

    /**
    * Log to the developer log if the setting is turned on
    *
    * @return void
    */
    public function _log($message)
    {

        $this->EE->load->library('logger');
        $this->EE->load->library('user_agent');

        $this->EE->logger->developer($this->package . ' - ' . $message);
    }


    /**
     * Activate Extension
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
     * Add New Hooks
     *
     */
    private function add_new_hooks()
    {
        
        // ADD New Actions
        foreach ($this->hooks as $hook => $method) {
            // check if its not installed
            if (!isset($this->current_hooks[$hook])) {
                $data = array(
                    'class'     => $this->package,
                    'method'    => $method,
                    'hook'      => $hook,
                    'settings'  => serialize($this->settings()),
                    'priority'  => 10,
                    'version'   => $this->version,
                    'enabled'   => 'y'
                );

                $this->EE->db->insert('extensions', $data);
            }

        }

    }
    // ----------------------------------------------------------------


    /**
     * Update Extension
     *
     * This function performs any necessary db updates when the extension
     * page is visited
     *
     * @return  mixed   void on update / false if none
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

        $this->EE->db->where('class', $this->package);
        $this->EE->db->update(
            'extensions',
            array('version' => $this->version)
        );
    }

    /**
     * Disable Extension
     *
     * This method removes information from the exp_extensions table
     *
     * @return void
     */
    public function disable_extension()
    {
        $this->EE->db->where('class', $this->package);
        $this->EE->db->delete('extensions');
    }
}

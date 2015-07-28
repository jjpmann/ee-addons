<?php

require 'BaseExtension.php';

class Vim_custom_ext extends BaseExtension
{

    public $name            = 'Vim Custom';
    public $version         = '0.0.4';
    public $description     = 'Logs page benchmarks.';
    public $settings_exist  = 'y';
    public $docs_url        = '';
    public $settings        = array();
    
    protected $settings_default = array(
        'execution_time'    => 1,
        'memory_usage'      => 20,
        'total_queries'     => 60
    );
    protected $package      = '';

    protected $hooks        = array(
        'sessions_start'    => 'sessions_start_hook',
        'sessions_end'      => 'sessions_end_hook',
    );

    public function __construct($settings = '')
    {
        $this->package = __CLASS__;
        parent::__construct($settings);
    }

    /**
     * Settings
     *
     * This function returns the settings for the extensions
     *
     * @return settings array
     */
    public function settings()
    {
        $settings['execution_time'] = $this->settings_default['execution_time'];
        $settings['memory_usage']   = $this->settings_default['memory_usage'];
        $settings['total_queries']  = $this->settings_default['total_queries'];

        return $settings;
    }

    /**
     * hook
     */
    public function sessions_end_hook($sess)
    {
        //$this->overloadOutput();
    }

    /**
     * hook
     */
    public function sessions_start_hook($sess)
    {

    }

    /**
     * hook
     */
    public function overloadOutput()
    {
        require_once PATH_THIRD . '/vim_custom/libraries/Vim_Output.php';
        ee()->output = new Vim_Output;
        ee()->output->enableSlowPageLogger($this->settings);
    }

}

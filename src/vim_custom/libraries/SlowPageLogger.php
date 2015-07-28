<?php namespace LMO;

class SlowPageLogger
{

    protected $EE;

    protected $settings;

    protected $data;

    protected $_available_sections = array(
        'Benchmarks',
        //'get',
        'MemoryUsage',
        //'post',
        //'uri_string',
        //'controller_info',
        'Queries',
        //'http_headers',
        //'session_data'
    );

    protected $package = 'SlowPageLogger';

    protected $profile = array();

    public function __construct($EE, $settings)
    {
        $this->EE = $EE;
        $this->settings = $settings;

        $this->settings['memory_usage'] = $this->settings['memory_usage'] * 1024 * 1024;
    }

    public function run()
    {
        foreach ($this->_available_sections as $section) {
            $func = "_compile{$section}";
            $this->{$func}();
        }


        foreach ($this->data as $key => $value) {
            if (isset($this->settings[$key]) && $value > $this->settings[$key]) {
                $this->_log($key);
                break;
            }
        }

    }

    /**
    * Log to the developer log if the setting is turned on
    *
    * @return void
    */
    protected function _log($key)
    {

        $message = "{$this->package} :: {$key} ({$this->data[$key]} > {$this->settings[$key]}) :: ";
        $message .= '<pre>' . print_r($this->profile, true) . '</pre>';

        echo $message;
        

        $this->EE->load->library('logger');
        $this->EE->load->library('user_agent');

        $this->EE->logger->developer($message);
    }

    /**
     * Compile Queries
     *
     * @return  array
     */
    protected function _compileBenchmarks()
    {
        $BM =& $this->EE->benchmark;

        $profile = array();
        foreach ($BM->marker as $key => $val) {
            // We match the "end" marker so that the list ends
            // up in the order that it was defined
            if (preg_match("/(.+?)_end/i", $key, $match)) {
                if (isset($BM->marker[$match[1].'_end']) and isset($BM->marker[$match[1].'_start'])) {
                    $profile[$match[1]] = $BM->elapsed_time($match[1].'_start', $key);
                }
            }
        }

        $this->data['total_execution_time'] = $profile['total_execution_time'];

        $this->profile['bench'] = $profile;
    }

    /**
     * Compile Queries
     *
     * @return  array
     */
    protected function _compileQueries()
    {

        $dbs = array();

        // Let's determine which databases are currently connected to
        foreach (get_object_vars($this->EE) as $CI_object) {
            if (is_object($CI_object) && is_subclass_of(get_class($CI_object), 'CI_DB')) {
                $dbs[] = $CI_object;
            }
        }

        $profile = array();

        foreach ($dbs as $db) {
            $profile[$db->database] = count($db->queries) - 1;
        }
        
        $this->data['total_queries'] = $profile[$db->database];

        $this->profile['queries'] = $profile;
    }


    /**
     * Compile memory usage
     *
     * Display total used memory
     *
     * @return  string
     */
    protected function _compileMemoryUsage()
    {


        if (function_exists('memory_get_usage') && ($usage = memory_get_usage()) != '') {
            $profile['raw']     = $usage;
            $profile['format']  = number_format($usage).' bytes';
        }

        $this->data['memory_usage'] = $profile['raw'];

        $this->profile['memory'] = $profile;
    }
}

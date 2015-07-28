<?php

namespace EE\Addons\Plugin;

$plugin_info = array(
    'pi_name'           => 'Vim Custom',
    'pi_version'        => '1.0.1',
    'pi_author'         => 'Vim Interactive',
    'pi_author_url'     => 'expressionengine.com',
    'pi_description'    => 'Custom Functions',
    'pi_usage'          => BasePlugin::usage()
);

abstract class BasePlugin
{


    /**
    * usage
    *   - description of plugin
    */
    public function usage()
    {
        return 'Instructions here or Ask steve!';
    }
}

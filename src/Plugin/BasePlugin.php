<?php

namespace EE\Addons\Plugin;

$plugin_info = [
    'pi_name'           => 'BasePlugin',
    'pi_version'        => '0.0.0',
    'pi_author'         => 'DefaultAuthor',
    'pi_author_url'     => 'expressionengine.com',
    'pi_description'    => 'Custom Functions',
    'pi_usage'          => BasePlugin::usage(),
];

abstract class BasePlugin
{
    /**
     * usage
     *   - description of plugin.
     */
    public static function usage()
    {
        return 'Please overwride';
    }
}

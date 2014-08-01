<?php
    error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
    include_once('./systems/core.php');

    import('systems.db');
    import('systems.config');

    import('systems.hscache');

    list(, $_command, $_table, $_key, $_value, $_ttl) = $argv;

    if ($_command == 'new') {
        HSCache::create_table($_table);
    } elseif ($_command == 'get') {
        echo var_export(HSCache::get($_table, $_key))."\r\n";
    } elseif ($_command == 'set') {
        HSCache::set($_table, $_key, $_value, $_ttl);
    }

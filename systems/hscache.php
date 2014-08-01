<?php

    /**
     * HSCache предназначен для хранения кеша в HandlerSocket
     * @author Oleg Shevelev
     */

    import('systems.config');

    class HSCache {
        static private $port_r_default = 9998;
        static private $port_w_default = 9999;

        static private $servers_r = array('hsdb1', 'hsdb2');
        static private $servers_w = array('hsdb1', 'hsdb2');

        static private $hs = array();
        static private $indexs = array();


        // кеш убиваем кроном, прекешем и при чтении данных
        static private $ttl_default = 2592000; //60*60*24*30 - 30 дней

        private static function openIndex($type, $table, $server, $options = array()) {
            $table = 'hs_'.mysql_escape_string($table);

            if (self::$indexs[$type][$table][$server]) return self::$indexs[$type][$table][$server];

            $config = Config::get('handler_'.$server, 'storage');
            $config['port_r'] = ($config['port_r']) ? $config['port_r'] : '9998';
            $config['port_w'] = ($config['port_w']) ? $config['port_w'] : '9999';

            // открываемые поля таблицы
            $keys = array(
                'get' => 'data,ttl',
                'set' => 'id,data,group,ttl',
                'delete' => '',
                'update' => 'id,data',
                'update_ttl' => 'id,ttl',
            );

            $host = $config['host'];
            $db   = $config['db'];

            $auth_type = (in_array($type, ['set', 'delete', 'update', 'update_ttl'])) ? 'w' :'r';
            $port = $config['port_'.$auth_type];
            $auth = $config['auth_'.$auth_type];

            $hs = (self::$hs[$host][$port]) ? self::$hs[$host][$port] : new HandlerSocketi($config['host'], $port, array('timeout' => 3));
//            $hs->auth($auth, $auth_type); # пока не работает

            try {
                self::$indexs[$type][$table][$server] = $hs->openIndex($db, $table, $keys[$type]);
            } catch (Exception $e) {
                if ($e->getCode() == 0) {
                    self::create_table($table);
                    try {
                        self::$indexs[$type][$table][$server] = $hs->openIndex($db, $table, $keys[$type]);
                    } catch (Exception $e2) {}
                }
            }
            return self::$indexs[$type][$table][$server];
        }

        public static function get($table, $key) {
            $index = self::openIndex('get', $table, self::$servers_r[0]);

            $data = $index->find($key);

            list($cache_data, $cache_ttl) = $data[0];
            $cache_data = json_decode($cache_data);

            if ($cache_ttl < time()) {
                self::delete($table, $key);
                return false;
            }
            return $cache_data;
        }

        public static function set($table, $key, $data, $group = '', $ttl = false) {
            $ttl = ($ttl) ? $ttl : self::$ttl_default;
            $data = json_encode($data);

            $index = self::openIndex('set', $table, self::$servers_w[0]);
            $index->remove($key);
            $index->insert([$key, $data, $group, time()+$ttl]);
        }

        public static function delete($table, $key) {
            $index = self::openIndex('delete', $table, self::$servers_w[0]);
            $index->remove($key);
        }

        public static function create_table($table) {
            $table = mysql_escape_string('hs_'.$table);
            $servers_r = array_combine(self::$servers_r, self::$servers_r);
            $servers_w = array_combine(self::$servers_w, self::$servers_w);

            $servers = array_merge($servers_r, $servers_w);

            if (!count($servers)) return false;

            $query = "
                CREATE TABLE IF NOT EXISTS `${table}` (
                    `id`    varchar(255) NOT NULL,
                    `data`  LONGTEXT NOT NULL,
                    `group` varchar(255) NOT NULL,
                    `ttl`   int(5) unsigned DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `key_group` (`group`),
                    KEY `key_ttl`   (`ttl`)
                ) ENGINE=innoDB DEFAULT CHARSET=utf8
            ";
            foreach ($servers as $server_name => $value) {
                $db = db($server_name, 'handler');

                if (!$db->is_table($table)) $db->q($query);
                echo $db->host." [".$db->db."] -> ${table} -> ".($db->is_table($table) ? 'OK' : 'Error')."\r\n";
            }
        }

    }
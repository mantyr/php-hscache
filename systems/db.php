<?php

    import('systems.config');


    /**
     * Универсальный класс для работы с базой данных
     * @author Oleg Shevelev
     */

    if ($_GET['view_query']) db::$debug = true;
    function db($server, $type = 'storage') {
        return db::connect($server, $type);
    }

    class db {

        static private $db_link_ids = array();
        static public $debug = false;

        private $db_name = false;
        private $db_link_id = false;
        private $is_connected = false;

        public $host = '';
        public $db = '';

        public static function connect($server, $type = 'storage') {
            if (!self::$db_link_ids[$type][$server]) {
                self::$db_link_ids[$type][$server] = new self($server, $type);
            }
            return self::$db_link_ids[$type][$server];
        }

        private function __construct($server, $type = 'storage') {
            $this->db_name = $server;
            $this->db_type = $type;
            $config = Config::get("${type}_".$server, 'storage');
            $config['type'] = 'mysql';
            $config['is_unique'] = true; // deprecated;

            $this->host = $config['host'];
            $this->db = $config['db'];

            try {
                $this->db_link_id = new PDO("${config['type']}:host=${config['host']};dbname=${config['db']}", $config['username'], $config['password']);
                $this->is_connected = true;
            } catch(PDOException $e) {
                $this->is_connected = false;
                echo 'db_error '.$server;
                return false;
            }
        }

        public function q($query, $type = PDO::FETCH_ASSOC) {
            $query = "/* ".$this->host." [".$this->db."] ".$comment." -- */ ".$query;

            $time_start = microtime(1);

                $res = $this->db_link_id->query($query);
                if (is_object($res)) $res->setFetchMode($type);

            $query_time = sprintf("%.4f", microtime(1)-$time_start);

            if (self::$debug) echo "<pre style='position:relative; background:#fff; color:#666; z-index:999999;'>".$query_time."\r\n".$query."</pre>\r\n";
            return $res;
        }

        public function lastInsertId() {
            return $this->db_link_id->lastInsertId();
        }

        public function get_tables() {
            if ($res = $this->q("SHOW TABLES", PDO::FETCH_NUM)) {
                while ($row = $res->fetch()) {
                    $result[] = $row[0];
                }
            }
            return $result;
        }

        public function is_table($table) {
            if ($res = $this->q("SHOW TABLES LIKE '".mysql_escape_string($table)."'",  PDO::FETCH_NUM)) {
                while ($row = $res->fetch()) {
                    return true;
                }
            }
            return false;
        }
    }

<?php

    /**
     * Основные функции, использующиеся во всём проекте повсеместно, не имеют зависимостей
     * @author Oleg Shevelev
     */

    define('ROOT_DIR', dirname(__FILE__).'/../');

    /**
     * Служит для импорта php файлов
     *
     * @param string в качестве адреса указывается название файла и каталога, например "systems.config" или "systems.other.config", каталоги перечисляются через запятую, вместо точки можно использовать слеш, например "systens/config"
     * @return mixed название файла или false
     */
    function import($param = false){
        if (!$param) return false;

        $class_file = ROOT_DIR.str_replace('.', '/', $param).'.php';
        if (is_file($class_file) && include_once($class_file)) return basename(str_replace('.', '/', $param));
        return false;
    }

    function import_address($param = false){
        if (!$param) return false;
        return ROOT_DIR.str_replace('.', '/', $param);
    }


    /**
     * Служит для отображения служебных сообщений в браузере в окуратной рамке
     *
     * Tекст выводится в echo, в качестве зашиты от вредоносного кода используется htmlspecialchars
     * @param string любой текст
     */
    function eEcho($param,$is_border = true){
        echo '<div style="'.(($is_border)?'border:1px solid #222222; ':'color:#CC0000;').'padding:5px; margin-bottom:10px;">'.htmlspecialchars($param).'</div>';
    }
    function cEcho($param){
        echo "$param\n";
    }

    function gGet($param){
        if (isset($_GET[$param])) return $_GET[$param];
        if (isset($_POST[$param])) return $_POST[$param];
        return null;
    }

    function globals_params($prefix = ''){
        foreach ($_GET as $var => $value) {
            if (!$GLOBALS[$prefix.'_'.$var]) $return[$prefix.'_'.$var] = $value;
        }
        foreach ($_POST as $var => $value) {
            if (!$GLOBALS[$prefix.'_'.$var]) $return[$prefix.'_'.$var] = $value;
        }
        return $return;
    }




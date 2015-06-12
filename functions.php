<?php
function vd() {
    $arguments = func_get_args();
    call_user_func_array('dump', $arguments);
    die();
    if (count($arguments)) {
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            if(!headers_sent()) {
                header('Content-Type: text/html; charset=utf-8');
            }
            echo '<pre>';
        }

        $last = array_pop($arguments);
        foreach($arguments as $item) {
            dump($item);
        }

        if ($last !== '!@#') {
            dump($last);
            die();
        }
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            echo '</pre>';
        } else echo "\n";
    }
}

//function dump($var) {
//    echo \Solve\Utils\Inflector::dumperGet($var) . "\n";
//}

function dumpAsString($var, $new_level = 0) {
    $res = '';

    if (is_bool($var)) {
        $res = $var ? "true" : "false";
    } elseif(is_null($var)) {
        $res = "null";
    } elseif(is_array($var)) {
        $res = 'array (';

        foreach($var as $key=>$item) {
            $res .= "\n". str_repeat(" ", ($new_level+1)*4);
            $res .= dumpAsString($key, $new_level+1);
            $res .= ' => ';
            $res .= dumpAsString(is_object($item) ? get_class($item) : $item, $new_level+1).',';
        }

        $res .= "\n".str_repeat(" ", ($new_level)*4).')';
    } elseif(is_string($var) && (isset($var[0]) && $var[0] != '$')) {
        $res = '"'. (strpos($var, '$__lv') === false ? str_replace('"', '\"', $var) : $var) .'"';
    } else {
        $res = $var;
    }

    return $res;
}
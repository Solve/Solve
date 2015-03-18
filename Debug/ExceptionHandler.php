<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 3/3/15 11:52 AM
 */

namespace Solve\Debug;


use Solve\Http\Request;
use Solve\Kernel\DC;
use Solve\Logger\Logger;

class ExceptionHandler {

    public static function setUp() {
        set_exception_handler((array('Solve\\Debug\\ExceptionHandler', 'exceptionHandler')));
    }

    static public function exceptionHandler(\Exception $e) {
        $fullTrace = $e->getTrace();

        if (is_callable(array($e, 'postAction'))) {
            $e->postAction($e->getMessage(), $e->getCode());
        }
        if (!DC::getProjectConfig('devMode')) {
            DC::getLogger()->add('Exception: ' . $e->getMessage(), 'exception');
            die();
        }
        $content = '<div style="font-size: 13px; font-family: Consolas, Menlo, Monaco, monospace;white-space: pre-wrap;">';

        $htmlTrace = "<b>\nLast arguments(" . count($fullTrace[0]['args']) . "):</b>\n"
            . dumpAsString($fullTrace[0]['args'])
            . "<b>\n\nCall stack:</b>\n<table style='font-size: 13px;'>";
        foreach ($fullTrace as $item) {
            $info = self::compileShortCallee($item);

            $htmlTrace .= '<tr><td style="color:#666;padding-right:10px;">' . $info['file'] . '</td><td>' . $info['call'] . '</td></tr>';
        }
        $htmlTrace .= '</table>';

        $content .= '<div style="background:#c00;color:white;font-weight:bold;padding:5px;margin-bottom: 5px; ">' . $e->getMessage() . '</div>';
        $content .= $htmlTrace;
        $content .= '</div>';

        if (DC::getRouter()->getExecutionMode() == Request::MODE_CONSOLE) {
            $content = strip_tags(str_replace('</td><td>', "\n", $content)) . "\n";
        }
        echo $content;
        die();
    }

    private static function compileShortCallee($item) {

        $className = empty($item['class']) ? '' : (strpos($item['class'], '\\') === false ? $item['class'] : substr($item['class'], strrpos($item['class'], '\\') + 1));
        if (empty($item['type'])) $item['type'] = '::';

        $res = array(
            'call' => $className . $item['type'] . $item['function'] . '()',
            'file' => ''
        );

        if (!empty($item['file'])) {
            $res['file'] = substr($item['file'], strrpos($item['file'], DIRECTORY_SEPARATOR) + 1, -(strlen($item['file']) - strrpos($item['file'], '.'))) . ':' . $item['line'];
        }
        return $res;
    }

    public function onKernelBoot() {
        self::setUp();
    }

    public function getEventListeners() {
        return array(
            'kernel.boot' => array(
                'listener' => array($this, 'onKernelBoot')
            )
        );
    }
}
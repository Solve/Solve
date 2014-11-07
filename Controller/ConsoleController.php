<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11/6/14 7:25 PM
 */

namespace Solve\Controller;


use Colors\Color;
use Solve\Utils\Inflector;

class ConsoleController extends BaseController {

    protected $_input;
    protected $_output;
    protected $_color;

    public function __construct() {
        parent::__construct();
        $this->_input  = fopen("php://stdin", "r");
        $this->_output = fopen("php://stdout", "w");
        $this->_color  = new Color();
    }

    protected function requireParametersCount($count) {
        if (count($this->route->getVars()) < $count) {
            $this->paramsError('This task require at least '.$count.' parameters!'."%n");
            die();
        }
    }

    protected function paramsError($message) {
        $method = Inflector::underscore($this->route->getActionName());
        $this->warning($message.PHP_EOL);
//        if (isset($this->help_messages[$method])) {
//            echo $this->colorize($this->help_messages[$method] . PHP_EOL);
//        }
        die();
    }
    public function message($str) {
        $this->writeln('<green>Message:</green> '.$str . "\n");
    }

    public function warning($str) {
        $this->writeln('<yellow>Warning:</yellow> '.$str . "\n");
    }

    public function error($str) {
        $this->writeln('<red>Error:</red> '.$str . "\n");
        die(1);
    }

    public function writeln($string, $newLine = true) {
        echo $this->_color->colorize($string) . ($newLine ? "\n" : "");
    }

    public function preAction() {
        if ($this->route->getVar(':options/help')) {
            $this->printHelp();
            die();
        }
    }
}
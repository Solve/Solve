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
use Solve\DocComment\DocComment;
use Solve\Utils\Inflector;

class ConsoleController extends BaseController {

    protected $_input;
    protected $_output;
    protected $_color;
    protected $_command;
    protected $_commandColor;
    protected $_className;
    protected $_reflect;

    public function __construct() {
        parent::__construct();
        $this->_input        = fopen("php://stdin", "r");
        $this->_output       = fopen("php://stdout", "w");
        $this->_color        = new Color();
        $this->_reflect      = new \ReflectionClass($this);
        $this->_command      = Inflector::underscore(substr($this->_reflect->getShortName(), 0, -10));
        $this->_commandColor = '<bold><green>' . $this->_command . '</green></bold>';
        $this->writeln("Solve console v0.1\n");
    }

    public function defaultAction() {
        $methods        = $this->_reflect->getMethods(\ReflectionMethod::IS_PUBLIC);
        $methodsList    = '';
        $skippedMethods = array('_preAction', '_postAction', 'defaultAction');
        foreach ($methods as $method) {
            if (!in_array($method->getName(), $skippedMethods) && (substr($method->getName(), -6) === 'Action')) {
                $commandMethod = str_replace('_', '-', Inflector::underscore(substr($method->getName(), 0, -6)));

                $doc = DocComment::parseFromString($method->getDocComment());
                $methodsList .= "  <bold>" . $commandMethod . "\t</bold>"
                    . $doc->getDescription() . "\n"
                    . "\t\t" . $doc->getAnnotationsAsString('optional')
                    . "\n";
            }
        }
        if (!empty($methodsList)) {
            $this->writeln('Here are methods of ' . $this->_command . ":");
            $this->writeln($methodsList);
        } else {
            $this->writeln('Command ' . $this->_commandColor . ' does not have any methods');
        }
    }

    public function _preAction() {
        if ($this->route->getVar('params/help')) {
            $this->printHelp();
            die();
        }
    }

    public function printHelp() {
        $this->message('Command help:');
    }

    protected function requireParametersCount($count) {
        if (count($this->route->getVars()) < $count) {
            $this->paramsError('This task require at least ' . $count . ' parameters!' . "%n");
            die();
        }
    }

    protected function paramsError($message) {
        $this->warning($message . PHP_EOL);
        die();
    }

    public function ask($message, $default = null) {
        $res = null;
        while (!$res) {
            $this->writeln($message . ($default ? '(<bg_dark_gray>' . $default . '</bg_dark_gray>)' : '') . ':', false);
            $res = $this->getInput();
            if ((!$res || ($res == "")) && $default) $res = $default;
        }
        return trim($res);
    }

    public function confirm($message, $default = false) {
        $this->writeln($message . '(' . ($default ? 'Y/n' : 'y/N') . ')?', false);
        $res = $this->getInput();
        if (($default && !$res) || (in_array(strtolower($res), array('y', 'yes')))) {
            return true;
        } else {
            return false;
        }
    }

    public function askArray($fields) {
        $result = array();
        foreach($fields as $name=>$info) {
            if (!is_array($info)) {
                $params[$name] = $info;
                continue;
            }
            $result[$name] = null;
            while (!($result[$name] = $this->ask($info[0], array_key_exists(1, $info) ? $info[1] : null)));
        }
        return $result;
    }

    public function information($title, $description = null) {
        $this->writeln('<green>'.$title.':</green> ' . $description . "\n");
    }

    public function message($str) {
        $this->writeln('<green>Message:</green> ' . $str . "\n");
    }

    public function warning($str) {
        $this->writeln('<yellow>Warning:</yellow> ' . $str . "\n");
    }

    public function error($str) {
        $this->writeln('<red>Error:</red> ' . $str . "\n");
        die(1);
    }

    public function getInput() {
        return trim(fgets($this->_input));
    }

    public function writeln($string, $newLine = true) {
        echo $this->_color->colorize($string) . ($newLine ? "\n" : "");
    }

}
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
                $optional = $doc->getAnnotationsAsString('optional');
                $methodsList .= "  <bold>" . $commandMethod . (strlen($commandMethod) < 16 ? str_repeat(' ', 12 - strlen($commandMethod)) : '') . "\t</bold>"
                    . $doc->getDescription()
                    . ($optional ? "\n\t\t" . $optional : "")
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
        if ($this->getRoute()->getVar('params/help')) {
            $this->printHelp();
            die();
        }
    }

    public function printHelp() {
    }

    protected function requireParametersCount($count) {
        if (count($this->getRoute()->getVars()) < $count) {
            $this->paramsError('This task require at least ' . $count . ' parameters!' . "%n");
            die();
        }
    }

    protected function paramsError($message) {
        $this->error($message);
        die();
    }

    public function ask($message, $default = null, $allowEnter = false) {
        $res = null;
        while (!$res) {
            $this->writeln($message . ($default ? '(<bg_dark_gray>' . $default . '</bg_dark_gray>)' : '') . ': ', false);
            $res = $this->getInput();
            if ($allowEnter && !$res) {
                return $default;
            }
            if ((!$res || ($res == "")) && $default) $res = $default;
        }
        return trim($res);
    }

    public function getFirstParamOrAsk($what) {
        if (!($var = $this->getRoute()->getRequestVar('params/0'))) {
            while(!($var)) {$var = $this->ask($what);}
        }
        return $var;
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

    public function notify($description = null, $title = null) {
        $this->writeln('<green>'.$title.'</green> ' . $description);
    }

    public function warning($description = null, $title = 'warning:') {
        $this->writeln('<yellow>'.$title.'</yellow> ' . $description);
    }

    public function error($description = null, $title = 'warning:') {
        $this->writeln('<red>' . $title . '</red> ' . $description);
        die(1);
    }

    public function getInput() {
        return trim(fgets($this->_input));
    }

    public function writeln($string, $newLine = true) {
        echo $this->_color->colorize($string) . ($newLine ? "\n" : "");
    }

}
<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 07.11.14 15:32
 */

namespace Solve\Application;


use Solve\Controller\ControllerService;
use Solve\Http\Request;
use Solve\Kernel\DC;

class ConsoleApplication extends Application {

    public function detectApplication() {
        DC::getEventDispatcher()->dispatchEvent('route.buildRequest', Request::getIncomeRequest());
        $this->_name = 'console';
        $this->_config = array(
            'uri'   => 'solve/',
            'path'  => 'SolveConsole/',
        );
        $this->_namespace = 'SolveConsole';
        $this->_root      = realpath(__DIR__ . '/../SolveConsole/') . '/';
        DC::getAutoloader()->registerNamespacePath($this->_namespace, realpath(__DIR__ . '/../') . '/');
        ControllerService::setActiveNamespace($this->_namespace);
        return $this->_name;
    }

}
<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11/3/14 2:04 PM
 */

namespace Solve\Router;
use Solve\Utils\Inflector;

/**
 * Class ApplicationRoute
 * @package Solve\Router
 *
 * Class ApplicationRoute is a local application route
 *
 * @method string getControllerName()
 * @method string getActionName()
 * @method string setControllerName($name)
 * @method string setActionName($name)
 *
 * @version 1.0
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 */
class ApplicationRoute extends Route {

    protected $_systemPatterns = array(
        'controller' => array('Index', '{name}Controller', 'ucfirst'),
        'action'     => array('default', '{name}Action', array('Solve\\Router\\ApplicationRoute', '_prepareActionName')),
    );

    protected $_systemVars = array();

    public function __construct(Route $route) {
        $this->setVars($route->getVars());
        $this->setRequest($route->getRequest());
        $this->setConfig($route->getConfig());
        $this->setName($route->getName());
        $this->setIsNotFound($route->isNotFound());
        $this->setUriPattern($route->getUriPattern());
        foreach ($this->_systemPatterns as $varName => $params) {
            $value = $route->getVar($varName, $params[0]);
            if (!empty($params[2])) {
                $value = call_user_func($params[2], $value);
            }
            $this->_systemVars[$varName.'name'] = str_replace('{name}', $value, $params[1]);
        }
    }


    public function __call($method, $params) {
        if (substr($method, 0, 3) == 'get') {
            $varName = strtolower(substr($method, 3));
            return !empty($this->_systemVars[$varName]) ? $this->_systemVars[$varName] : null;
        } elseif (substr($method, 0, 3) == 'set') {
            $varName = strtolower(substr($method, 3));
            if (!empty($this->_systemVars[$varName])) {
                $this->_systemVars[$varName] = $params[0];
            }
        }
        return null;
    }

    private static function _prepareActionName($string) {
        return lcfirst(Inflector::camelize($string));
    }

}
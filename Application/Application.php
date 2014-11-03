<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 02.11.14 11:14
 */

namespace Solve\Application;


use Solve\Config\Config;
use Solve\Http\Request;
use Solve\Kernel\DC;
use Solve\Kernel\Kernel;
use Solve\Router\ApplicationRoute;
use Solve\Router\Router;
use Solve\Storage\ArrayStorage;
use Solve\Storage\YamlStorage;
use Solve\Utils\Inflector;

class Application {

    private $_name;
    private $_namespace;
    private $_root;
    private $_controllersRoot;

    private static $_loadedControllers = array();
    /**
     * @var ApplicationRoute
     */
    private $_route;
    /**
     * @var YamlStorage
     */
    private $_config;

    public function run() {
        $this->detectApplication();
        $this->boot();
        $this->detectApplicationRoute();
        $this->process();
    }

    public function boot() {
        $this->_root            = DC::getEnvironment()->getApplicationRoot() . $this->_config['path'];
        $this->_controllersRoot = $this->_root . 'controllers/';
        $this->_config          = new YamlStorage($this->getRoot() . 'config.yml');
    }

    protected function detectApplicationRoute() {
        if (!$this->_config->has('routes')) {
            throw new \Exception('Routes not found for app [' . $this->_name . '], in ' . $this->_config->getPath());
        }
        DC::getRouter()->addRoutes($this->_config->get('routes'));
        $route = DC::getRouter()->processRequest(Request::getIncomeRequest())->getCurrentRoute();
        if ($route->isNotFound()) {
            DC::getEventDispatcher()->dispatchEvent('route.notFound');
            return false;
        }

        $this->_route = new ApplicationRoute($route);
    }

    public function process() {

        if (is_callable('ApplicationController', '_preAction')) {
            self::getController('ApplicationController')->_preAction();
        }
        self::getController($this->_route->getControllerName())->{$this->_route->getActionName()}();

    }

    public function detectApplication() {
        DC::getEventDispatcher()->dispatchEvent('route.buildRequest', Request::getIncomeRequest());
        /**
         * @var ArrayStorage $appList
         */
        $appList        = DC::getProjectConfig('applications');
        $defaultAppName = DC::getProjectConfig('defaultApplication', 'frontend');
        $this->_name    = $defaultAppName;
        $uriParts       = explode('/', (string)Request::getIncomeRequest()->getUri());
        if (!empty($uriParts) && ((count($uriParts) > 0) && ($uriParts[0] != '/'))) {
            foreach ($appList as $appName => $appParams) {
                if ($appName == $defaultAppName) continue;

                $appUri = !empty($appParams['uri']) ? $appParams['uri'] : $appName;
                if (strpos($uriParts[0], $appUri) === 0) {
                    return ($this->_name = $appName);
                }
            }
        }
        $this->_config = DC::getProjectConfig('applications/' . $this->_name);
        if (!is_array($this->_config)) {
            $this->_config = array(
                'uri' => $this->_name,
            );
        }
        if (empty($this->_config['path'])) {
            $this->_config['path'] = $this->_name . '/';
        }
        $this->_namespace = Inflector::camelize($this->_name);
        DC::getAutoloader()->registerNamespacePath($this->_namespace, DC::getEnvironment()->getApplicationRoot());
        return $this->_name;
    }

    public function getController($controllerName) {
        $fullControllerName = $this->_namespace . '\\Controllers\\' . ucfirst(Inflector::camelize($controllerName));
        if (empty(self::$_loadedControllers[$fullControllerName])) {
            self::$_loadedControllers[$fullControllerName] = new $fullControllerName();
        }
        return self::$_loadedControllers[$fullControllerName];
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->_name;
    }

    /**
     * @return mixed
     */
    public function getRoot() {
        return $this->_root;
    }

    public function getEventListeners() {
        return array(
            'kernel.ready' => array(
                'listener'   => array($this, 'run'),
                'parameters' => array()
            )
        );
    }

}
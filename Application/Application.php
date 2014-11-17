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
use Solve\Controller\ControllerService;
use Solve\Http\Request;
use Solve\Kernel\DC;
use Solve\Kernel\Kernel;
use Solve\Router\ApplicationRoute;
use Solve\Router\Router;
use Solve\Storage\ArrayStorage;
use Solve\Storage\YamlStorage;
use Solve\Utils\Inflector;
use Solve\View\View;

class Application {

    protected $_name;
    protected $_namespace;
    protected $_root;
    protected $_controllersRoot;

    /**
     * @var ApplicationRoute
     */
    protected $_route;
    /**
     * @var YamlStorage
     */
    protected $_config;

    public function run() {
        $this->detectApplication();
        $this->boot();
        $this->configure();
        $this->detectApplicationRoute();
        $this->process();
    }

    public function boot() {
        $this->_config          = new YamlStorage($this->getRoot() . 'config.yml');
        if (!$this->_config->has('routes')) {
            throw new \Exception('Routes not found for app [' . $this->_name . '], in ' . $this->_config->getPath());
        }
        if ($events = $this->_config->get('events')) {
            foreach ($events as $eventName => $params) {
                if (!is_array($params) || array_key_exists(0, $params)) {
                    $params = array('listener' => $params);
                }
                DC::getEventDispatcher()->addEventListener($eventName, $params['listener']);
            }
        }
        DC::getRouter()->addRoutes($this->_config->get('routes'));
    }

    public function configure() {
        DC::getView()->setTemplatesPath($this->getRoot() . 'Views/')->setRenderEngineName('Slot');
    }

    public function process() {
        if (ControllerService::isControllerExists('ApplicationController')) {
            ControllerService::getController('ApplicationController')->_preAction();
        }
        ControllerService::processControllerAction($this->_route->getControllerName(), $this->_route->getActionName());
        if (ControllerService::isControllerExists('ApplicationController')) {
            ControllerService::getController('ApplicationController')->_postAction();
        }
        DC::getView()->render();
    }

    public function detectApplicationRoute() {
        $route = DC::getRouter()->processRequest(Request::getIncomeRequest())->getCurrentRoute();
        if ($route->isNotFound()) {
            DC::getEventDispatcher()->dispatchEvent('route.notFound');
        }
        $this->_route = new ApplicationRoute($route);
        return $this;
    }


    public function detectApplication() {
        DC::getEventDispatcher()->dispatchEvent('route.buildRequest', Request::getIncomeRequest());
        /**
         * @var ArrayStorage $appList
         */
        $appList = DC::getProjectConfig('applications');
        if (empty($appList)) {
            throw new \Exception('Empty application list');
        }
        $defaultAppName = DC::getProjectConfig('defaultApplication', 'frontend');
        $this->_name    = $defaultAppName;
        $uri = (string)Request::getIncomeRequest()->getUri();
        $uriParts       = explode('/', $uri);
        if (!empty($uriParts) && ((count($uriParts) > 0) && ($uriParts[0] != '/'))) {
            foreach ($appList as $appName => $appParams) {
                if ($appName == $defaultAppName) continue;

                $appUri = !empty($appParams['uri']) ? $appParams['uri'] : $appName;
                if (strpos($uriParts[0], $appUri) === 0) {
                    array_shift($uriParts);
                    Request::getIncomeRequest()->setUri(implode('/', $uriParts));
                    $this->_name = $appName;
                    break;
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
            $this->_config['path'] = Inflector::classify($this->_name) . '/';
        }
        $this->_namespace = Inflector::camelize($this->_name);
        $this->_root      = DC::getEnvironment()->getApplicationRoot() . $this->_config['path'];
        DC::getAutoloader()->registerNamespacePath($this->_namespace, DC::getEnvironment()->getApplicationRoot());
        ControllerService::setActiveNamespace($this->_namespace);
        return $this->_name;
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

    public function getConfig() {
        return $this->_config;
    }

    /**
     * @return ApplicationRoute
     */
    public function getRoute() {
        return $this->_route;
    }

    public function setRoute(ApplicationRoute $route) {
        $this->_route = $route;
    }

    public function getEventListeners() {
        return array(
            'kernel.ready' => array(
                'listener' => array($this, 'run'),
            )
        );
    }

}
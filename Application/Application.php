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
use Solve\DocComment\DocComment;
use Solve\Exceptions\RouteNotFoundException;
use Solve\Http\Request;
use Solve\Kernel\DC;
use Solve\Kernel\Kernel;
use Solve\Router\ApplicationRoute;
use Solve\Router\Route;
use Solve\Router\Router;
use Solve\Security\SecurityService;
use Solve\Storage\ArrayStorage;
use Solve\Storage\YamlStorage;
use Solve\Utils\FSService;
use Solve\Utils\Inflector;
use Solve\View\View;

class Application {

    protected $_name;
    protected $_namespace;
    protected $_root;
    protected $_controllersRoot;
    protected $_isTerminated = false;

    /**
     * @var ApplicationRoute
     */
    protected $_route;
    /**
     * @var YamlStorage
     */
    protected $_config;

    /**
     * @var ArrayStorage
     */
    protected $_routes;

    public function run() {
        //$this->detectApplication();
        $this->boot();
        $this->detectCurrentRoute();
        $this->configure();
        $this->process();
    }

    public function boot() {
        //$this->_config = new YamlStorage($this->getRoot() . 'config.yml');
        //if (!$this->_config->has('routes')) {
        //    throw new \Exception('Routes not found for app [' . $this->_name . '], in ' . $this->_config->getPath());
        //}
        $this->_routes = new ArrayStorage();

        $appList = DC::getProjectConfig('applications');
        if (empty($appList)) {
            throw new \Exception('Empty application list');
        }

        foreach($appList as $appName => $appInfo) {
            $appName = ucfirst($appName);
            $appRootPath   = DC::getEnvironment()->getApplicationRoot() . $appName . '/';
            $routingConfig = new YamlStorage($appRootPath . 'Config/routing.yml');
            $config = $routingConfig->get('config', array());
            DC::getAutoloader()->registerNamespacePath($appName, DC::getEnvironment()->getApplicationRoot());
            if (empty($config['type']) || $config['type'] !== 'annotation') {
                foreach($routingConfig->get('routes', array()) as $routeName => $routeInfo)  {
                    $routeInfo['application'] = $appName;
                    foreach($config as $key=>$value) {
                        $routeInfo[$key] = $value;
                    }
                    $this->_routes[$routeName] = $routeInfo;
                }
            } else {
                $files = FSService::getInstance()->in($appRootPath . 'Controllers')->find('*Controller.php', FSService::TYPE_ALL, FSService::HYDRATE_NAMES);
                foreach($files as $file) {
                    $controllerName = substr($file, 0, -4);
                    $className = '\\' .$appName . '\\Controllers\\' . $controllerName;
                    $reflection = new \ReflectionClass($className);
                    foreach($reflection->getMethods() as $method) {
                        if ($comment = $method->getDocComment()) {
                            $comment = DocComment::parseConfigs($comment);
                            if ($routes = $comment->getAnnotations('Route')) {
                                if (!empty($routes['name'])) {
                                    $routes = array($routes);
                                }
                                foreach($routes as $route) {
                                    $routeInfo                     = array(
                                        'pattern'     => $route[0],
                                        'application' => $appName,
                                        'controller'  => substr($controllerName, 0, -10),
                                        'action'      => substr($method->getName(), 0, -6)
                                    );
                                    foreach($config as $key=>$value) {
                                        $routeInfo[$key] = $value;
                                    }
                                    $this->_routes[$route['name']] = $routeInfo;
                                }

                            }
                            //vd($comment, $this->_routes);
                        }
                    }
                    //vd($reflection->getMethods());
                }
            }
        }
        //vd($this->_routes->getArray());
        DC::getRouter()->addRoutes($this->_routes->getArray());
        if ($webRoot = DC::getProjectConfig('webRoot')) {
            DC::getRouter()->setWebRoot($webRoot);
        }

        SecurityService::boot();

        //if ($events = $this->_config->get('events')) {
        //    foreach ($events as $event => $listener) {
        //        DC::getEventDispatcher()->addEventListener($event, $listener);
        //    }
        //}
        //
    }

    public function configure() {
        $viewEngineName = DC::getProjectConfig('view/engine', 'Slot');
        DC::getView()
            ->setTemplatesPath($this->getRoot() . 'Views/')
            ->setRenderEngineName($viewEngineName)
            ->setLayoutTemplate(null)
        ;
    }

    public function process() {
        SecurityService::processRoute($this->_route);
        if ($this->isTerminated()) return true;

        if (ControllerService::isControllerExists('ApplicationController')) {
            ControllerService::getController('ApplicationController')->_preAction();
        }
        $vars = ControllerService::processControllerAction($this->_route->getControllerName(), $this->_route->getActionName());
        if (ControllerService::isControllerExists('ApplicationController')) {
            ControllerService::getController('ApplicationController')->_postAction();
        }
        if (is_array($vars)) {
            DC::getView()->setVars($vars);
        }
        DC::getView()->render();
    }

    public function unauthenticatedAccess($event) {
        $firewall = SecurityService::getInstance()->getActiveFirewall();

        $route = DC::getRouter()->getRoute($firewall->getDeepValue('login/login_route'));

        if (empty($route)) {
            throw new RouteNotFoundException($firewall->getDeepValue('login/login_route'));
        }
        DC::getRouter()->setCurrentRoute($route)->getCurrentRequest()->setUri($route->buildUri(array()));
        $route = new ApplicationRoute($route);
        DC::getApplication()->setRoute($route);
        ControllerService::processControllerAction($route->getControllerName(), $route->getActionName());
    }

    public function terminate() {
        $this->_isTerminated = true;
        DC::getView()->render();
    }

    public function isTerminated() {
        return $this->_isTerminated;
    }

    public function detectCurrentRoute() {
        DC::getAutoloader()->registerNamespacePath('SolveConsole', DC::getEnvironment()->getProjectRoot() . 'vendor/solve/solve/');
        DC::getEventDispatcher()->dispatchEvent('route.buildRequest', Request::getIncomeRequest());

        $route = DC::getRouter()->processRequest(Request::getIncomeRequest())->getCurrentRoute();
        if ($route->isNotFound()) {
            DC::getEventDispatcher()->dispatchEvent('route.notFound');
            if (DC::getProjectConfig('devMode')) throw new \Exception('Route not found');
        }
        $this->processApplicationRoute($route);
        return $this;
    }


    public function processApplicationRoute(Route $route) {
        $this->_route = new ApplicationRoute($route);
        $this->_name    = $this->_route->getVar('application');
        //vd($this->_route, $this->_name);
        //$uri            = (string)Request::getIncomeRequest()->getUri();

        if (empty($this->_config['path'])) {
            $this->_config['path'] = Inflector::camelize($this->_name) . '/';
        }
        $this->_namespace = Inflector::camelize($this->_name);
        $this->_root      = DC::getEnvironment()->getApplicationRoot() . $this->_config['path'];
        //DC::getAutoloader()->registerNamespacePath($this->_namespace, DC::getEnvironment()->getApplicationRoot());
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

    public function getUser() {
        return SecurityService::getInstance()->getUser();
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
            ),
            'security.unauthenticated' => array(
                'listener' => array($this, 'unauthenticatedAccess')
            ),
        );
    }

}
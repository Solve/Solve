<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 27.10.14 18:22
 */

namespace Solve\Kernel;


use Solve\Autoloader\Autoloader;
use Solve\Config\ConfigService;
use Solve\DependencyInjection\DependencyContainer;
use Solve\Environment\Environment;
use Solve\EventDispatcher\EventDispatcher;
use Solve\Http\Request;
use Solve\Logger\Logger;
use Solve\Router\Router;
use Solve\Storage\YamlStorage;
use Solve\Utils\Inflector;

class Kernel {

    /**
     * @var Kernel
     */
    private static $_mainInstance;

    /**
     * @var Environment
     */
    private $_environment;

    /**
     * @var DependencyContainer
     */
    private $_dependencyContainer;

    /**
     * @var EventDispatcher
     */
    private $_eventDispatcher;

    public function __construct(DependencyContainer $dc = null) {
        if (empty($dc)) $dc = new DependencyContainer();

        $this->_dependencyContainer = $dc;
        $this->_environment         = Environment::createFromContext();
        $this->_dependencyContainer->setDependencyObject('kernel', $this);
        $this->loadSystemDependencies();
        $this->loadUserDependencies();
        $this->processProjectConfig();
    }

    public static function getMainInstance(DependencyContainer $dc = null) {
        if (empty(self::$_mainInstance)) {
            if (empty($dc)) $dc = new DependencyContainer();

            DC::setInstance($dc);
            self::$_mainInstance = new static($dc);
        }
        return self::$_mainInstance;
    }

    protected function loadSystemDependencies() {
        $initialDependencies = new YamlStorage(__DIR__ . '/kernel.dependencies.yml');
        $this->_dependencyContainer->addDependencies($initialDependencies);

        $this->_eventDispatcher = $this->_dependencyContainer->get('eventDispatcher');
        DC::getAutoloader()->register(false);
        $this->onEnvironmentUpdate();
    }

    public function onEnvironmentUpdate() {
        ConfigService::setConfigsPath($this->_environment->getConfigRoot());
        ConfigService::loadAllConfigs();
        DC::getLogger()->setLogsPath($this->_environment->getTmpRoot() . 'log');
    }

    protected function loadUserDependencies() {
        if (is_file($this->_environment->getUserClassesRoot() . 'user.dependencies.yml')) {
            $dependencies = new YamlStorage($this->_environment->getUserClassesRoot() . 'user.dependencies.yml');
            $this->_dependencyContainer->addDependencies($dependencies);
        }
    }

    protected function processProjectConfig() {
        if ($webRoot = DC::getProjectConfig('webRoot')) {
            $this->_environment->setWebRoot($webRoot);
        }
    }

    public function boot() {
        foreach ($this->_dependencyContainer->getAllDependencies() as $name => $info) {
            if (is_callable(array($info['className'], 'onKernelBoot'))) {
                $this->_dependencyContainer->get($name)->onKernelBoot($this->_dependencyContainer);
            }
            if (is_callable(array($info['className'], 'getEventListeners'))) {
                $events = $this->_dependencyContainer->get($name)->getEventListeners();
                foreach ($events as $eventName => $params) {
                    if (!is_array($params)) {
                        $params = array('listener' => $params);
                    }
                    $this->_eventDispatcher->addEventListener($eventName, $params['listener']);
                }
            }
        }
        if (headers_sent()) {
            DC::getLogger()->add('Cannot start session, headers sent', Logger::NAMESPACE_KERNEL);
        } else {
            session_start();
        }
    }

    public function process() {
        $this->_eventDispatcher->dispatchEvent('kernel.ready');
    }

    public function run() {
        $this->boot();
        $this->process();
    }

    /**
     * @return Environment
     */
    public function getEnvironment() {
        return $this->_environment;
    }

    /**
     * @param Environment $environment
     * @return Kernel
     */
    public function setEnvironment($environment) {
        $this->_environment = $environment;
        $this->onEnvironmentUpdate();
        return $this;
    }

    public function getDependencyContainer() {
        return $this->_dependencyContainer;
    }

}
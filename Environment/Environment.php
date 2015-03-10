<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 28.10.14 18:25
 */

namespace Solve\Environment;


use Solve\Kernel\DC;
use Solve\Storage\ArrayStorage;

/**
 * Class Environment
 * @package Solve\Environment
 *
 * Class Environment is represents environment
 * @method string getProjectRoot()
 *
 * @method string getConfigRoot()
 * @method $this setConfigRoot($path)
 * @method string getTmpRoot()
 * @method $this setTmpRoot($path)
 * @method string getApplicationRoot()
 * @method $this setApplicationRoot($path)
 * @method string getWebRoot()
 * @method $this setWebRoot($path)
 * @method string getUploadRoot()
 * @method $this setUploadRoot($path)
 * @method string getUserClassesRoot()
 * @method $this setUserClassesRoot($path)
 *
 * @method string getTimezone()
 * @method $this setTimezone($path)
 *
 * @version 1.0
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 */
class Environment {

    /**
     * @var ArrayStorage
     */
    private $_vars;

    public function __construct() {
        $this->_vars = new ArrayStorage(array('roots' => array()));
    }

    public static function createFromContext() {
        $environment = new static();
        $environment->setProjectRoot(realpath(__DIR__ . '/../../../../') . '/', true);

        if (!ini_get('date.timezone') || !($timezone = date_default_timezone_get())) {
            $timezone = 'Europe/Kiev';
            date_default_timezone_set($timezone);
        }
        $environment->_vars['timezone'] = $timezone;
        return $environment;
    }

    public function setProjectRoot($path, $updateOther = false) {
        $this->_vars['roots']['project'] = $path;
        if ($updateOther) $this->updateFromProjectRoot();
        DC::getEventDispatcher()->dispatchEvent('environment.update', 'project');
    }

    protected function updateFromProjectRoot() {
        $projectRoot = $this->getProjectRoot();
        $this->_vars->setDeepValue('roots/application', $projectRoot . 'app/');
        $this->_vars->setDeepValue('roots/config', $projectRoot . 'config/');
        $this->_vars->setDeepValue('roots/tmp', $projectRoot . 'tmp/');
        $this->_vars->setDeepValue('roots/userclasses', $projectRoot . 'src/');
        $this->_vars->setDeepValue('roots/web', $projectRoot . 'web/');
        $this->_vars->setDeepValue('roots/upload', $this->getWebRoot() . 'upload/');
    }

    public function __call($method, $params) {
        $operation = substr($method, 0, 3);
        if (in_array($operation, array('get', 'set'))) {
            if (substr($method, -4) == 'Root') {
                $key = 'roots/' . strtolower(substr($method, 3, -4));
            } else {
                $key = strtolower(substr($method, 3));
            }
            if ($operation == 'get') {
                return $this->_vars->getDeepValue($key);
            } else {
                $this->_vars->setDeepValue($key, $params[0]);
                DC::getEventDispatcher()->dispatchEvent('environment.update', $key);
                return $this;
            }
        } else {
            return $this;
        }
    }

}
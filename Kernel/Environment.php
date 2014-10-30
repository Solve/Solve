<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 28.10.14 18:25
 */

namespace Solve\Kernel;


use Solve\Storage\ArrayStorage;

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
        $environment->setProjectRoot(realpath(__DIR__. '/../../../') . '/');
        $environment->setApplicationRoot($environment->getProjectRoot() . 'app/');
        $environment->setTmpRoot($environment->getProjectRoot() . 'tmp/');
        $environment->setWebRoot($environment->getProjectRoot() . 'web/');
        $environment->setUploadRoot($environment->getWebRoot() . 'upload/');

        if (!($timezone = date_default_timezone_get())) {
            $timezone = 'Europe/Kiev';
            date_default_timezone_set($timezone);
        }
        $environment->_vars['timezone'] = $timezone;
        return $environment;
    }

    public function setProjectRoot($path) {
        $this->_vars['roots']['project'] = $path;
    }

    public function getProjectRoot() {
        return $this->_vars['roots']['project'];
    }

    public function setTmpRoot($path) {
        $this->_vars['roots']['tmp'] = $path;
    }

    public function getTmpRoot() {
        return $this->_vars['roots']['tmp'];
    }

    public function setApplicationRoot($path) {
        $this->_vars['roots']['application'] = $path;
    }

    public function getApplicationRoot() {
        return $this->_vars['roots']['application'];
    }

    public function setWebRoot($path) {
        $this->_vars['roots']['web'] = $path;
    }

    public function getWebRoot() {
        return $this->_vars['roots']['web'];
    }

    public function setUploadRoot($path) {
        $this->_vars['roots']['upload'] = $path;
    }

    public function getUploadRoot() {
        return $this->_vars['roots']['upload'];
    }



    public function setTimezone($timezone) {
        $this->_vars['timezone'] = $timezone;
    }

    public function getTimezone() {
        return $this->_vars['timezone'];
    }

}
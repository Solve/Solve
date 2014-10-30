<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 27.10.14 18:22
 */

namespace Solve\Kernel;


use Solve\DependencyInjection\DependencyContainer;

class Kernel {

    /**
     * @var Kernel
     */
    private static $_projectInstance;

    /**
     * @var Environment
     */
    private $_environment;

    /**
     * @var DependencyContainer
     */
    private $_dependencyContainer;

    public function __construct(DependencyContainer $dc = null) {
        $this->_dependencyContainer = $dc;
        $this->_environment         = Environment::createFromContext();
    }

    public static function getProjectInstance(DependencyContainer $dc = null) {
        if (empty(self::$_projectInstance)) {
            self::$_projectInstance = new static($dc);
        }
        return self::$_projectInstance;
    }

    /**
     * @return Environment
     */
    public function getEnvironment() {
        return $this->_environment;
    }

    /**
     * @param Environment $environment
     */
    public function setEnvironment($environment) {
        $this->_environment = $environment;
    }

}
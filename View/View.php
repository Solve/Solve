<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11/3/14 4:39 PM
 */

namespace Solve\View;


use Solve\Http\Response;
use Solve\Router\Route;
use Solve\Storage\ArrayStorage;
use Solve\Storage\SessionStorage;
use Solve\Utils\Inflector;
use Solve\View\ViewEngine\AbstractViewEngine;

class View {

    const FORMAT_HTML    = 'html';
    const FORMAT_JSON    = 'json';
    const FORMAT_XML     = 'xml';
    const FORMAT_CONSOLE = 'console';

    /**
     * @var SessionStorage
     *
     */
    protected $_flash;

    /**
     * @var ArrayStorage
     */
    protected $_vars;
    /**
     * @var ArrayStorage
     */
    protected $_formatVars;

    /**
     * @var Response
     */
    protected $_response;

    protected $_templatesPath;
    protected $_templateName;
    protected $_layoutName;
    protected $_responseFormat = self::FORMAT_HTML;

    public function __construct() {
        $this->_vars       = new ArrayStorage();
        $this->_formatVars = new ArrayStorage();
        $this->_flash      = new SessionStorage(array(), 'view_flash');
        $this->_response   = new Response();
    }

    public function render($templateName = null) {
        if ($templateName) $this->setTemplateName($templateName);
        $viewEngineName = 'Solve\\View\\ViewEngine\\' . ucfirst($this->_responseFormat) . 'ViewEngine';
        if (!class_exists($viewEngineName)) {
            throw new \Exception('View engine '.$viewEngineName.' not found');
        }
        /**
         * @var AbstractViewEngine $viewEngine
         */
        $viewEngine = new $viewEngineName($this);
        $viewEngine->configure();
        $viewEngine->render();
    }

    /**
     * Set path to layout or null if layout does not needed
     * @param null|string $layoutName
     */
    public function setLayoutTemplate($layoutName = null) {
        $this->_layoutName = $layoutName;
    }

    public function setTemplateName($templateName) {
        $this->_templateName = $templateName;
    }

    public function getTemplateName() {
        return $this->_templateName;
    }

    public function &__get($key) {
        return $this->_vars->get($key);
    }

    public function __set($key, $value) {
        $this->_vars->set($key, $value);
    }

    /**
     * @return mixed
     */
    public function getTemplatesPath() {
        return $this->_templatesPath;
    }

    /**
     * @param mixed $templatesPath
     */
    public function setTemplatesPath($templatesPath) {
        $this->_templatesPath = $templatesPath;
    }

    /**
     * @return mixed
     */
    public function getResponseFormat() {
        return $this->_responseFormat;
    }

    /**
     * @param mixed $responseFormat
     */
    public function setResponseFormat($responseFormat) {
        $this->_responseFormat = $responseFormat;
    }

    /**
     * @return ArrayStorage
     */
    public function getVars() {
        return $this->_vars;
    }

    /**
     * @return SessionStorage
     */
    public function getFlash() {
        return $this->_flash;
    }


}
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
use Solve\Storage\ArrayStorage;
use Solve\Storage\SessionStorage;
use Solve\View\RenderEngine\BaseRenderEngine;

class View extends \stdClass {

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
    protected $_renderEngine = 'Base';

    public function __construct() {
        $this->_vars       = new ArrayStorage();
        $this->_formatVars = new ArrayStorage();
        $this->_flash      = new SessionStorage(array(), 'view_flash');
        $this->_response   = new Response();
//        $this->_responseFormat = DC::getRouter()->getCurrentRequest()->get
    }

    public function render($templateName = null) {
        if ($templateName) $this->setTemplateName($templateName);
        $viewEngineName = 'Solve\\View\\RenderEngine\\' . ucfirst($this->_renderEngine) . 'RenderEngine';
        if (!class_exists($viewEngineName)) {
            throw new \Exception('View engine '.$viewEngineName.' not found');
        }
        /**
         * @var BaseRenderEngine $viewEngine
         */
        $viewEngine = new $viewEngineName($this);
        $viewEngine->configure();
        $renderMethod = 'render' . ucfirst($this->_responseFormat);
        if (!is_callable(array($viewEngine, $renderMethod))) {
            throw new \Exception('No render method for '.$this->_responseFormat .' in engine '.$this->_renderEngine);
        }
        call_user_func(array($viewEngine, $renderMethod));
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

    public function __get($key) {
        return $this->_vars->$key;
    }

    public function __set($key, $value) {
        $this->_vars->set($key, $value);
        return $this;
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
     * @return string
     */
    public function getRenderEngine() {
        return $this->_renderEngine;
    }

    /**
     * @param string $renderEngine
     */
    public function setRenderEngine($renderEngine) {
        $this->_renderEngine = $renderEngine;
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
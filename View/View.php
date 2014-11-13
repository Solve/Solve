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
use Solve\Kernel\DC;
use Solve\Storage\ArrayStorage;
use Solve\Storage\SessionStorage;
use Solve\Utils\Inflector;
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

    protected        $_templatesPath;
    protected        $_templateName;
    protected        $_layoutName      = '_layout';
    protected        $_responseFormat  = self::FORMAT_HTML;
    protected        $_renderEngineName    = 'Base';
    protected        $_alreadyRendered = false;
    protected static $_renderEngineInstances         = array();

    public function __construct() {
        $this->_vars           = new ArrayStorage();
        $this->_formatVars     = new ArrayStorage();
        $this->_flash          = new SessionStorage(array(), 'view_flash');
        $this->_response       = DC::getResponse();
        $this->_responseFormat = $this->detectResponseFormat();
    }

    public function render($templateName = null) {
        if ($this->_alreadyRendered) {
            return true;
        }
        $vars = $this->getCombinedVars();
        if ($this->_responseFormat == View::FORMAT_HTML) {
            if (empty($templateName)) {
                if (empty($this->_templateName)) $this->detectTemplate();
                $templateName = $this->_templateName;
            }
            if (($layout = $this->getLayoutTemplate())) {
                $vars['innerTemplateContent'] = $this->fetchTemplate($templateName, $vars);
                $templateName = $layout;
            }
        }

        $this->_response->setContent($this->fetchTemplate($templateName, $vars));
        $this->_response->send();
        $this->_alreadyRendered = true;
    }

    public function fetchTemplate($templateName, $vars = array(), $format = null) {
        if (empty($format)) $format = $this->_responseFormat;

        $renderEngine = $this->getEngineInstance($this->_renderEngineName);
        $renderMethod = 'fetch' . ucfirst($format);
        if (!is_callable(array($renderEngine, $renderMethod))) {
            throw new \Exception('No render method for ' . $format . ' in engine ' . $this->_renderEngineName);
        }
        return call_user_func(array($renderEngine, $renderMethod), $vars, $templateName);
    }

    protected function detectTemplate() {
        $route = DC::getApplication()->getRoute();
        $folder = Inflector::slugify(substr($route->getControllerName(), 0, -10));
        $action = Inflector::slugify(substr($route->getActionName(), 0, -6));

        if (is_file($this->getTemplatesPath() . $folder . '/' . $action . '.slot')) {
            $this->setTemplateName($folder . '/' . $action);
        } elseif (is_file($this->getTemplatesPath() . $action . '.slot')) {
            $this->setTemplateName($action);
        } else {
            throw new \Exception('Cannot detect template:' . $folder . '/' . $action);
        }
    }

    public function getEngineInstance($engineName) {
        if (empty(self::$_renderEngineInstances[$engineName])) {
            $viewEngineName = 'Solve\\View\\RenderEngine\\' . ucfirst($engineName) . 'RenderEngine';

            if (!class_exists($viewEngineName)) {
                throw new \Exception('View engine ' . $viewEngineName . ' not found');
            }

            /**
             * @var BaseRenderEngine $renderEngine
             */
            $renderEngine = new $viewEngineName($this);
            $renderEngine->configure();
            self::$_renderEngineInstances[$engineName] = $renderEngine;
        }
        return self::$_renderEngineInstances[$engineName];
    }

    protected function detectResponseFormat() {
        $accept = DC::getRouter()->getCurrentRequest()->getAcceptType();
        if (strpos($accept, 'json') !== false) {
            return static::FORMAT_JSON;
        } elseif (strpos($accept, 'text/html') !== false) {
            return static::FORMAT_HTML;
        } elseif (strpos($accept, 'xml') !== false) {
            return static::FORMAT_XML;
        } else {
            return static::FORMAT_CONSOLE;
        }
    }

    /**
     * Set path to layout or null if layout does not needed
     * @param null|string $layoutName
     * @return $this
     */
    public function setLayoutTemplate($layoutName = null) {
        $this->_layoutName = $layoutName;
        return $this;
    }

    public function getLayoutTemplate() {
        return $this->_layoutName;
    }

    public function setNoLayout() {
        $this->_layoutName = null;
        return $this;
    }

    public function setStandaloneTemplate($templateName) {
        $this->_templateName = $templateName;
        $this->_layoutName   = null;
        return $this;
    }

    public function setTemplateName($templateName) {
        $this->_templateName = $templateName;
        return $this;
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

    public function setVar($key, $value, $format = null) {
        if ($format) {
            $this->_formatVars->setDeepValue($format . '/' . $key, $value);
        } else {
            $this->_vars->setDeepValue($key, $value);
        }
        return $this;
    }

    public function getCombinedVars($format = null) {
        if (!$format) $format = $this->_responseFormat;
        $combinedVars = new ArrayStorage($this->_vars);
        if ($this->_formatVars->has($format)) {
            $combinedVars->extendDeepValue($this->_formatVars->get($format));
        }
        $combinedVars['_baseUri'] = DC::getRouter()->getBaseUri();
        return $combinedVars;
    }

    /**
     * @return mixed
     */
    public function getTemplatesPath() {
        return $this->_templatesPath;
    }

    public function setTemplatesPath($templatesPath) {
        $this->_templatesPath = $templatesPath;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponseFormat() {
        return $this->_responseFormat;
    }

    public function setResponseFormat($responseFormat) {
        $this->_responseFormat = $responseFormat;
        return $this;
    }

    /**
     * @return string
     */
    public function getRenderEngineName() {
        return $this->_renderEngineName;
    }

    public function getResponse() {
        return $this->_response;
    }

    public function setRenderEngineName($renderEngine) {
        $this->_renderEngineName = $renderEngine;
        return $this;
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
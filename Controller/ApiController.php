<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11.11.14 15:03
 */

namespace Solve\Controller;


use Solve\Kernel\DC;
use Solve\Security\SecurityService;
use Solve\Session\RemoteSessionManager;
use Solve\Storage\ArrayStorage;
use Solve\Storage\SessionStorage;
use Solve\View\View;

class ApiController extends BaseController {


    protected $_status = 200;
    protected $_sessionToken;
    protected $_message;
    protected $_data;
    /**
     * @var ArrayStorage
     */
    protected $_params;

    /**
     * @var SessionStorage
     */
    protected $_sessionStorage;

    protected $_unprotectedMethods = array();
    protected $_securityServiceScope = 'default';

    public function __construct() {
        parent::__construct();

        $this->_sessionToken   = RemoteSessionManager::getSessionToken();
        $this->_sessionStorage = new SessionStorage(null, 'api_session');
        $this->_data           = new ArrayStorage();

        $action = DC::getApplication()->getRoute()->getActionName();
        $action = substr($action, 0, -6);
        if (!in_array($action, $this->_unprotectedMethods)) {
            $this->requireAuthorization();
        }
    }

    public function setMessage($message, $statusCode = 200) {
        $this->_message = $message;
        $this->_status  = $statusCode;
    }

    public function setError($message, $statusCode = 406) {
        $this->_status  = $statusCode;
        $this->_message = $message;
    }

    public function setData($value, $varName = null) {
        $this->_data->setDeepValue($varName, $value);
    }

    public function returnErrorStatus($message, $statusCode = 406, $required = false) {
        if ($required) {
            $this->getView()->setVar('required', $required);
        }
        $this->setError($message, $statusCode);
        $this->_postAction();
        $this->getView()->render();
        die();
    }

    public function _postAction() {
        $this->getView()->setVar('status', $this->_status);
        if ($this->_message) {
            $this->getView()->setVar('message', $this->_message);
        }
        if (!$this->_data->isEmpty()) {
            $this->getView()->setVar('data', $this->_data->getArray());
        }
    }

    protected function requireData($paramsNames, $optionalNames = null) {
        $paramsRequired = is_array($paramsNames) ? $paramsNames : ($paramsNames ? explode(',', $paramsNames) : array());
        $errors         = array();
        $params         = array();
        $data           = $this->getRequestData('data');

        if (!empty($paramsRequired)) {
            foreach ($paramsRequired as $name) {
                $name = trim($name);
                if (!empty($data) && array_key_exists($name, $data)) {
                    $params[$name] = $this->getRequestData('data/' . $name);
                } else {
                    $errors[] = $name;
                }
            }
        }

        $optionalNames = !empty($optionalNames) ?
            (is_array($optionalNames) ? $optionalNames : explode(',', $optionalNames)) : array();

        foreach ($optionalNames as $name) {
            $name = trim($name);
            if (!empty($data) && array_key_exists($name, $data)) {
                $params[$name] = $this->getRequestData('data/' . $name);
            }
        }

        if (count($errors)) {
            $this->returnErrorStatus('You have to specify fields: ' . implode(',', $errors), 406, $errors);
        } else {
            return $params;
        }
    }

    public function requireAuthorization() {
        if (SecurityService::getInstance()->isAuthorized()) {
            return true;
        }
        $this->getView()->setVar('isLoggedIn', false);
        $this->returnErrorStatus('unauthorized', 401);
    }

    public function getUser($field = null) {
        $this->requireAuthorization();
        $user = SecurityService::getInstance()->getUser();
        if (is_null($field)) {
            return $user;
        } else {
            return isset($user[$field]) ?$user[$field] : null;
        }
    }

}
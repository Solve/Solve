<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 10.11.14 19:57
 */

namespace Solve\Security;


use Solve\Database\Models\Model;
use Solve\Kernel\DC;
use Solve\Storage\SessionStorage;
use Solve\Database\QC;

class SecurityService
{

    const SCOPE_DEFAULT = 'default';

    protected $_storage;
    protected $_scope = 'default';
    protected $_userModelName = '\AclUser';
    protected static $_instances = array();

    public function __construct($scope)
    {
        if (empty($scope)) throw new \Exception('You cannot use SecurityService without scope');
        $this->_storage = new SessionStorage(array(), 'Security_Scope_' . $scope);
        $this->_scope = $scope;
    }

    /**
     * @param string $scope
     * @return SecurityService
     * @throws \Exception
     */
    public static function getInstance($scope = self::SCOPE_DEFAULT)
    {
        if (empty($scope)) throw new \Exception('You cannot use SecurityService without scope');
        if (empty(self::$_instances[$scope])) {
            self::$_instances[$scope] = new static($scope);
        }
        return self::$_instances[$scope];
    }

    public function requireAuthorization()
    {
        if (!$this->_storage['user']) {
            DC::getEventDispatcher()->dispatchEvent('security.unauthenticated');
        }
        return false;
    }

    public function checkCredentials($params, $modelName = null)
    {
        if (empty($modelName)) $modelName = $this->_userModelName;
        if (!empty($params['password'])) $params['password'] = md5($params['password']);
        /**
         * @var Model $user
         */
        $user = call_user_func(array($modelName, 'loadOne'), QC::createFromCondition($params));
        if ($user->isExists()) {
            $this->_storage['user'] = $user->getArray();
            return true;
        } else {
            return false;
        }
    }


    public function isAuthorized($right = 0)
    {
        return (!empty($this->_storage['user']));
    }

    public function unAuthorize()
    {
        if (!empty($this->_storage['user'])) {
            unset($this->_storage['user']);
        }
        return true;
    }

    public function getUser()
    {
        return $this->_storage['user'];
    }

    public function setAuthorizedUser($user)
    {
        $this->_storage['user'] = $user;
    }
}
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
use Solve\Exceptions\ConfigException;
use Solve\Exceptions\SecurityException;
use Solve\Http\Request;
use Solve\Kernel\DC;
use Solve\Router\Route;
use Solve\Storage\ArrayStorage;
use Solve\Storage\ConfigStorage;
use Solve\Storage\SessionStorage;
use Solve\Database\QC;

class SecurityService {

    const CONTEXT_DEFAULT = 'default';
    const TOKEN_ANONYMOUS = 'anonymous';

    protected        $_storage;
    protected        $_context       = 'default';
    protected        $_userModelName = '\AclUser';
    protected static $_instances     = array();
    protected static $_activeInstance;
    /**
     * @var ConfigStorage
     */
    protected static $_config;

    public function __construct($context) {
        if (empty($context)) throw new \Exception('You cannot use SecurityService without scope');
        $this->_storage = new SessionStorage(array(), '_security.context.' . $context);
        $this->_context = $context;
        if (empty(self::$_config)) {
            self::boot();
        }
    }

    /**
     * @param string $context
     * @return SecurityService
     * @throws \Exception
     */
    private static function getContextInstance($context = self::CONTEXT_DEFAULT) {
        if (empty($context)) throw new \Exception('You cannot use SecurityService without scope');
        if (empty(self::$_instances[$context])) {
            self::$_instances[$context] = new static($context);
        }
        return self::$_instances[$context];
    }

    public static function getInstance() {
        if (empty(self::$_activeInstance)) {
            self::$_activeInstance = self::getContextInstance();
        }
        return self::$_activeInstance;
    }

    public static function setActiveContext($context) {
        self::$_activeInstance = self::getContextInstance($context);
        return self::$_activeInstance;
    }

    public function setSecurityToken($token) {
        $this->_storage->set('token', $token);
        if ($token == self::TOKEN_ANONYMOUS) {
            $this->_storage->set('user', array('name' => 'Anonymous'));
        }
    }

    public function getSecurityToken() {
        return $this->_storage->get('token');
    }

    /**
     * @return ArrayStorage
     */
    public function getActiveFirewall() {
        return new ArrayStorage($this->_storage->get('firewall'));
    }

    public static function boot() {
        if (empty(self::$_config)) {
            self::$_config = new ConfigStorage(DC::getSecurityConfig()->get('security'));
        }
        foreach (self::$_config->get('firewalls', array()) as $name => $info) {
            if (!empty($info['login']['check'])) {
                DC::getRouter()->addRoute($info['login']['check'], array(
                    'application' => 'Security',
                    'action'      => 'check',
                    'pattern'     => self::getSecuredUrlForFirewall($info, $info['login']['check']),
                ));
            }
            if (!empty($info['login']['logout'])) {
                DC::getRouter()->addRoute($info['login']['logout'], array(
                    'application' => 'Security',
                    'action'      => 'logout',
                    'pattern'     => self::getSecuredUrlForFirewall($info, $info['login']['logout']),
                ));
            }

        }
    }

    public static function getSecuredUrlForFirewall($info, $url) {
        return '/_security/' . md5($info['context'] . $url);
    }

    public static function getFirewallForUrl($url, $action) {
        if ($url[0] !== '/') $url = '/' . $url;
        foreach (self::$_config->get('firewalls', array()) as $name => $info) {
            if (!empty($info['login'][$action]) && $url == self::getSecuredUrlForFirewall($info, $info['login'][$action])) {
                return new ArrayStorage($info);
            }
        }
        return null;
    }

    public static function encodePassword($password, $encoder) {
        if ($encoder == "md5") {
            return md5($password);
        } else {
            throw new SecurityException('Encoder not found: ', $encoder);
        }
    }

    public function generateSecurityToken($data) {
        return md5($data['password']);
    }

    public static function processRoute(Route $route) {
        if (empty(self::$_config)) {
            self::boot();
        }
        $uri = $route->getRequest()->getUri();

        if ($route->getRequest()->getMethod() == Request::MODE_CONSOLE) {
            self::getInstance()->setSecurityToken(self::TOKEN_ANONYMOUS);
            return true;
        }
        if ($route->getVar('application') == 'Security') {
            $firewall = self::getFirewallForUrl($uri, $route->getVar('action'));
            if (!$firewall) {
                throw new SecurityException('Can not find firewall for action: '.$route->getVar('action'));
            }
            $context  = $firewall->get('context', SecurityService::CONTEXT_DEFAULT);
            $instance = self::getContextInstance($context);
            if ($route->getVar('action') == "check") {
                $provider = new ArrayStorage(self::$_config->getDeepValue('providers/' . $firewall->get('provider')));
                $data     = $route->getRequest()->getVars();
                if (empty($data['_username']) || empty($data['_password'])) {
                    throw new SecurityException('Invalid login form data');
                }

                $user = call_user_func(
                    array($provider->get('class'), $provider->get('method', 'loadOne')),
                    array(
                        $provider->get('username', 'login')    => $data['_username'],
                        $provider->get('password', 'password') => self::encodePassword($data['_password'], $provider->get('encoder', 'md5')),
                    )
                );
                if ($user) {
                    $instance->setAuthorizedUser($user);
                    $instance->setSecurityToken($instance->generateSecurityToken($user));
                }
                DC::getRouter()->redirectToReferrer();
            } elseif ($route->getVar('action') == "logout") {
                $instance->unAuthorize();
                DC::getRouter()->redirectToReferrer();
            }
        }

        foreach (self::$_config->get('firewalls', array()) as $name => $info) {
            $pattern = '#' . $info['pattern'] . '#';
            if (preg_match($pattern, $uri)) {
                $context  = !empty($info['context']) ? $info['context'] : self::CONTEXT_DEFAULT;
                $instance = self::setActiveContext($context);

                if (array_key_exists('anonymous', $info) && ($info['anonymous'] !== false)) {
                    $instance->setSecurityToken(self::TOKEN_ANONYMOUS);
                } else {
                    $currentToken = $instance->_storage->get('token');
                    if ($currentToken && $currentToken !== self::TOKEN_ANONYMOUS) {
                        return true;
                    }

                    $provider = self::$_config->get('providers/' . $info['provider']);
                    if (empty($provider['class'])) {
                        throw new ConfigException('Provider is invalid: ' . $info['provider']);
                    }
                    $instance->_storage->set('firewall', new ArrayStorage($info));
                    DC::getEventDispatcher()->dispatchEvent('security.unauthenticated');
                }
            }
        }
    }

    public function checkCredentials($params, $modelName = null) {
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


    public function isAuthorized($right = 0) {
        return (!empty($this->_storage['user']));
    }

    public function unAuthorize() {
        if (!empty($this->_storage['user'])) {
            unset($this->_storage['user']);
            unset($this->_storage['token']);
        }
        return true;
    }

    public function getUser() {
        return $this->_storage->get('user');
    }

    public function setAuthorizedUser($user) {
        $this->_storage['user'] = $user;
    }
}
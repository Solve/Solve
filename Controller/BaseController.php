<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 11/3/14 4:05 PM
 */

namespace Solve\Controller;


use Solve\Http\HttpStatus;
use Solve\Http\Response;
use Solve\Kernel\DC;
use Solve\Router\ApplicationRoute;
use Solve\Router\Route;
use Solve\Router\Router;
use Solve\Router\UriService;
use Solve\View\View;

class BaseController {

    public $view;
    public $router;
    public $request;
    /**
     * @var ApplicationRoute
     */
    public $route;

    public function __construct() {
        $this->view    = DC::getView();
        $this->router  = DC::getRouter();
        $this->route   = DC::getApplication()->getRoute();
        $this->request = $this->router->getCurrentRequest();
    }

    public function redirectToUri($relativeUri) {
        $request = DC::getRouter()->getCurrentRequest();
        if ($relativeUri == '/') {
            $relativeUri = '';
        }
        $webRoot = DC::getRouter()->getWebRoot();
        $applicationUrlPart =  ($webRoot !== "/" ? $webRoot . '/' : '').
                               (DC::getApplication()->getName()  == 'frontend' ? '' : DC::getApplication()->getName() . '/');
        $fullUrl = $request->getHost() . '/'
                   . $applicationUrlPart
                   . $relativeUri;
        $fullUrl = str_replace('//', '/', $fullUrl);
        $response = new Response();
        $response->setStatusCode(HttpStatus::HTTP_FOUND);
        $response->setHeader('Location', $request->getProtocol() . '://' . $fullUrl);
        if (headers_sent()) {
            DC::getLogger()->add('Cannot redirect to ' . $relativeUri);
        }
        $response->send();
        die();
    }

    public function redirectSelf() {
        $this->redirectToUri(DC::getRouter()->getCurrentRequest()->getUri());
    }

    public function forwardToRoute($routeName, $vars = null) {
        if ($route = DC::getRouter()->getRoute($routeName)) {
            DC::getRouter()->setCurrentRoute($route)->getCurrentRequest()->setUri($route->buildUri($vars));
            $route = new ApplicationRoute($route);
            DC::getApplication()->setRoute($route);
            ControllerService::processControllerAction($route->getControllerName(), $route->getActionName());
        }
    }

    public function getRequestData($path = null, $default = null) {
        return $this->route->getRequestVar($path, $default);
    }

    public function _preAction() {
    }

    public function _postAction() {
    }

}
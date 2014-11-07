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
    /**
     * @var ApplicationRoute
     */
    public $route;

    public function __construct() {
        $this->view   = DC::getView();
        $this->router = DC::getRouter();
        $this->route  = DC::getApplication()->getRoute();
    }

    public function redirectToUri($relativeUri) {
        $request = DC::getRouter()->getCurrentRequest();
        if ($relativeUri == '/') {
            $relativeUri = '';
        }
        $fullUrl = $request->getProtocol() . '://' . $request->getHost() . '/'
            . DC::getApplication()->getConfig()->get('uri')
            . $relativeUri;

        $response = new Response();
        $response->setStatusCode(HttpStatus::HTTP_OK);
        $response->setHeader('Location', $fullUrl);
        if (!$response->sendHeaders()) {
            DC::getLogger()->add('Cannot redirect to ' . $relativeUri);
        }
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

    public function _preAction() {
    }

    public function _postAction() {
    }

}
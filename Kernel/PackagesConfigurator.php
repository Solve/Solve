<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 04.11.14 20:57
 */

namespace Solve\Kernel;


use Solve\Config\ConfigService;
use Solve\Database\DatabaseService;
use Solve\Database\Models\Abilities\FilesAbility;
use Solve\Database\Models\ModelOperator;
use Solve\EventDispatcher\BaseEvent;
use Solve\Http\Request;

class PackagesConfigurator {

    public function onKernelReady(BaseEvent $event) {
        $this->onEnvironmentUpdate($event);
        if ($webRoot = DC::getProjectConfig('webRoot')) {
            DC::getEnvironment()->setWebRoot($webRoot);
        }
        $databaseConfig = DC::getDatabaseConfig();
        $request        = DC::getRouter()->getCurrentRequest();
        if (($profiles = $databaseConfig->get('profiles'))) {
            foreach ($profiles as $profileName => $profileInfo) {
                DatabaseService::configProfile($profileInfo, $profileName);
            }
            if (empty($request) || ($request && !$request->isConsoleRequest())) {
                ModelOperator::getInstance(DC::getEnvironment()->getEntitiesRoot());
                if ($databaseConfig->get('autoUpdateAll')) {
                    try {
                        ModelOperator::getInstance()->generateAllModelClasses()->updateDBForAllModels();;
                    } catch (\Exception $e) {
                        echo $e->getMessage() ."\n\r";
                    }
                }
            }
        }
    }

    public function onEnvironmentUpdate(BaseEvent $event) {
        ConfigService::setConfigsPath(DC::getEnvironment()->getConfigRoot());
        ConfigService::loadAllConfigs();
        DC::getLogger()->setLogsPath(DC::getEnvironment()->getTmpRoot() . 'log');
        $entitiesRoot = DC::getEnvironment()->getEntitiesRoot();
        DC::getAutoloader()->registerSharedPath($entitiesRoot, true);
        DC::getAutoloader()->registerSharedPath($entitiesRoot . 'bases');
        DC::getAutoloader()->registerSharedPath($entitiesRoot . 'classes');
        DC::getAutoloader()->registerNamespaceSharedPaths(DC::getEnvironment()->getUserClassesRoot() . 'classes/');
        DC::getAutoloader()->registerNamespaceSharedPaths($entitiesRoot . 'bases');
        DC::getAutoloader()->registerNamespaceSharedPaths($entitiesRoot . 'classes');
        FilesAbility::setBaseStoreLocation(DC::getEnvironment()->getUploadRoot());
    }

    public function getEventListeners() {
        return array(
            'kernel.boot'       => array(
                'listener' => array($this, 'onKernelReady')
            ),
            'kernel.ready'       => array(
                'listener' => array($this, 'onKernelReady')
            ),
            'environment.update' => array(
                'listener' => array($this, 'onEnvironmentUpdate')
            )
        );
    }

    public function __destruct() {
        if (!DC::getRouter()->getCurrentRequest() || DC::getRouter()->getCurrentRequest()->getMethod() == Request::MODE_CONSOLE) return true;
        if (DC::getProjectConfig('dev/toolbar') != true) return true;
        $routeName = DC::getRouter()->getCurrentRoute()->getName();
        $css =<<<TEXT
.__solve_debug_panel {
    z-index: 1030;
    text-align: left;
    font-size: 13px;
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    height: 40px;
    padding: 10px;
    color: #666;
    background-color: #FFF;
    box-shadow: 0 0 0 1px #e0e0e0;
    -webkit-box-shadow: 0px 0px 0px 1px rgba(0, 0, 0, 0.08), 0px 0px 0px 2px rgba(0, 0, 0, 0.02);
    box-shadow: 0px 0px 0px 1px rgba(0, 0, 0, 0.08), 0px 0px 0px 2px rgba(0, 0, 0, 0.02);
}
TEXT;
        $html =<<<TEXT
<style>
$css
</style>
<div class="__solve_debug_panel">
    route: <b>$routeName</b>
</div>
TEXT;
        echo $html;

    }

}
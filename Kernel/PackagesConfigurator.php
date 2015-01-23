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
                ModelOperator::getInstance(DC::getEnvironment()->getUserClassesRoot() . 'db/');
                if ($databaseConfig->get('autoUpdateAll')) {
                    ModelOperator::getInstance()->generateAllModelClasses()->updateDBForAllModels();;
                }
            }
        }
    }

    public function onEnvironmentUpdate(BaseEvent $event) {
        ConfigService::setConfigsPath(DC::getEnvironment()->getConfigRoot());
        ConfigService::loadAllConfigs();
        DC::getLogger()->setLogsPath(DC::getEnvironment()->getTmpRoot() . 'log');
        DC::getAutoloader()->registerSharedPath(DC::getEnvironment()->getUserClassesRoot(), true);
        DC::getAutoloader()->registerSharedPath(DC::getEnvironment()->getUserClassesRoot() . 'db/bases');
        DC::getAutoloader()->registerSharedPath(DC::getEnvironment()->getUserClassesRoot() . 'db/classes');
        DC::getAutoloader()->registerNamespaceSharedPaths(DC::getEnvironment()->getUserClassesRoot() . 'classes/', true);
        FilesAbility::setBaseStoreLocation(DC::getEnvironment()->getUploadRoot());
    }

    public function getEventListeners() {
        return array(
            'kernel.ready'       => array(
                'listener' => array($this, 'onKernelReady')
            ),
            'environment.update' => array(
                'listener' => array($this, 'onEnvironmentUpdate')
            )
        );
    }

}
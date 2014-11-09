<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 07.11.14 16:37
 */

namespace SolveConsole\Controllers;

/**
 * old
 *  :update-all
:update-class
:update-db
:drop-db
:build-db
:wizard
:data-dump
:data-load
:profile-configure
:profile-activate
:gen-model
:sql
:show-settings
:show-table
:count
:update-config
:ability-add
:ability-configure
:update-relations
 */

use Solve\Config\ConfigService;
use Solve\Controller\ConsoleController;
use Solve\Kernel\DC;

/**
 * Class DbController
 * @package SolveConsole\Controllers
 *
 * Class DbController is a database console operator
 * @help operate with database & models
 *
 * @version 1.0
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 */
class DbController extends ConsoleController {

    /**
     * Using for generating profile for database
     * @help Using for generating profile for database
     * @optional [profile name] to specify profile
     */
    public function wizardAction() {
        $this->writeln('DB wizard for a profile');
        $profileName = $this->ask('Enter the <underline>profile name</underline> to edit', 'default');
        $fields = $this->askArray(array(
            'name'  => array('DB name'),
            'user'  => array('DB user', 'root'),
            'pass'  => array('DB password', 'root'),
            'host'  => array('DB host', '127.0.0.1'),
        ));
        if ($this->confirm('Write to file', true)) {
            $config = DC::getDatabaseConfig();
            foreach($fields as $field=>$value) {
                $config->set('profiles/' . $profileName . '/' . $field, $value);
            }
            $config->save();
            $this->notify(ConfigService::getConfigsPath() . 'database.yml', 'saved');
        } else {
            $this->writeln('exiting.');
        }
    }

    /**
     * Update database and models
     */
    public function updateAction() {
    }



}
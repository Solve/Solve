<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 07.11.14 16:37
 */

namespace SolveConsole\Controllers;


use Solve\Config\ConfigService;
use Solve\Controller\ConsoleController;
use Solve\Kernel\DC;

/**
 * Class DbController
 * @help operate with database & models
 * @package SolveConsole\Controllers
 *
 * Class DbController is a database console operator
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
            $this->information('saved', ConfigService::getConfigsPath() . 'database.yml');
        } else {
            $this->writeln('exiting.');
        }
    }



}
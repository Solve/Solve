<?php
/*
 * This file is a part of Solve framework.
 *
 * @author Alexandr Viniychuk <alexandr.viniychuk@icloud.com>
 * @copyright 2009-2014, Alexandr Viniychuk
 * created: 02.11.14 11:14
 */

namespace Solve\Application;


class Application {


    public function run() {
        die('run');
    }

    public function getEventListeners() {
        return array(
            'app.run'   => array(
                'listener'      => array($this, 'run'),
                'parameters'    => array()
            )
        );
    }

}
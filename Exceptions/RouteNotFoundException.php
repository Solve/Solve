<?php
/*
 * This file is a part of control project.
 *
 * @author Alexandr Viniychuk <a@viniychuk.com>
 * created: 9:01 PM 5/28/15
 */

namespace Solve\Exceptions;


class RouteNotFoundException extends \Exception {

    public function __construct($message = "", $code = 0, \Exception $previous = null) {
        $message = 'Route Not Found - '.$message;
        parent::__construct($message, $code, $previous);
    }


}
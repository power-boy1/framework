<?php
/**
 * Bluz Framework Component
 *
 * @copyright Bluz PHP Team
 * @link https://github.com/bluzphp/framework
 */

/**
 * @namespace
 */
namespace Bluz\View\Helper;

use Bluz\View\View;

return
    /**
     * Return controller name
     * or check to current controller
     *
     * @var View $this
     * @param string $controller
     * @return string|bool
     */
    function ($controller = null) {
    $request = app()->getRequest();
    if (null == $controller) {
        return $request->getController();
    } else {
        return $request->getController() == $controller;
    }
    };

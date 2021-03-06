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

use Bluz\Acl\AclException;
use Bluz\View\View;

return
    /**
     * Dispatch controller View Helper
     *
     * Example of usage:
     *     $this->dispatch($module, $controller, array $params);
     *
     * @var View $this
     * @param string $module
     * @param string $controller
     * @param array $params
     * @return View|string|null
     */
    function ($module, $controller, $params = array()) {
    try {
        $view = app()->dispatch($module, $controller, $params);
    } catch (AclException $e) {
        // nothing for Acl exception
        return null;
    } catch (\Exception $e) {
        return $this->exception($e);
    }

    // run closure
    if ($view instanceof \Closure) {
        return $view();
    }
    return $view;
    };

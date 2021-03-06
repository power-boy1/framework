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
     * Set or generate <style> code for <head>
     *
     * @var View $this
     * @param string $script
     * @return string|View
     */
    function ($style = null, $media = 'all') {
    if (app()->hasLayout()) {
        // it's stack for <head>
        $headStyle = app()->getRegistry()->__get('layout:headStyle') ? : [];

        if (null === $style) {
            // clear system vars
            app()->getRegistry()->__set('layout:headStyle', []);
            array_walk(
                $headStyle,
                function (&$item, $key) {
                    $item = $this->style($key, $item);
                }
            );
            return join("\n", $headStyle);
        } else {
            $headStyle[$style] = $media;
            app()->getRegistry()->__set('layout:headStyle', $headStyle);
            return $this;
        }
    } else {
        // it's just alias to style() call
        return $this->style($style);
    }
    };

<?php
return [
    'uri' => '/media',
    /**
     * Possible Driver values are "gd", "imagick" and "automatic".
     * If no value is given , "automatic" is used.
     * "automatic" will try to use the "imagick" driver first, if there is no PHP Imagick Extension, it will try to use
     * the "gd" driver afterwards. If there is no PHP GD Extension aswell it will throw an Exception.
     */
    'driver' => ''
];
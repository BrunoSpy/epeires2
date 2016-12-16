<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    'translator' => array(
        'locale' => 'fr_FR',
    ),
    'lang' => 'fr_FR',
    //frequency tab colors
    'frequency_tab_colors' => array(
        'fail' => 'red',
        'warning' => 'orange',
        'ok' => 'green',
        'planned' => 'blue',
    ),
);

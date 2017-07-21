<?php
/**
 * $Id: file-config.sample.php 236 2007-04-14 01:38:00Z knutolav $
 *
 * This file contains configurations that may also exist in the database,
 * but if the plugin DF_FileConf is enabled these settings will be the
 * valid ones.
 *
 * If the database contains different values those will be updated
 * with the values from this file.
 *
 * Use the map key "__unset" with an array of option names to remove the
 * options completely. The list of option names in the "__unset" array
 * will be processed before the other listings.
 *
 * @package DF_FileConf
 */


global $fileconf;
$fileconf = array(
    // Settings for this plugin
    'df_fileconf_debug'     => false,
    'df_fileconf_update'    => false,
    'df_fileconf_active'    => true,

    'siteurl'       =>  'http://localhost.localdomain',
    'home'          =>  'http://localhost.localdomain',
    'widget_STP_TC' =>  array(
                            'link_all' => '/tag',
                        ),
//    'nrkgeo_google_maps_api_key'    => 'Some key',
    '__unset'       =>  array(
                        ),
);

?>

<?php
/**
 * $Id: df_fileconf.php 248 2007-04-15 02:31:25Z knutolav $
 *
 * Plugin Name: DF File Configuration
 * Plugin URI: http://www.datafeel.no/blog/wordpress/df_fileconf/
 * Description: You can override options from the database by setting them
 * in a configuration file
 * Author: Knut-Olav Hoven <knut_dash_olav_at_hoven_dot_ws>
 * Version: 1.0
 * Author URI: http://www.hoven.ws/
 *
 * @package DF_FileConf
 */


/***************************************************************************
 *   LICENCE                                                               *
 ***************************************************************************
 *   Copyright (C) 2007 by Knut-Olav Hoven                                 *
 *   knut-olav@hoven.ws                                                    *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/



/**
 * This class controls the options for the blog.
 * It will update the options table in the database based on
 * configurations from a file-config.php file 
 *
 * @package DF_FileConf
 */
class DF_FileConf {

    /**
     * If TRUE, a message is printed with the configuration
     * values from both the database and file before the script dies.
     * Default is no debugging.
     * @var boolean
     */
    private $mode_debug     = FALSE;


    /**
     * If TRUE the configuration values in the database will be updated
     * if the values from the configuration file differs.
     * Default is not to update the database.
     * @var boolean
     */
    private $mode_update    = FALSE;


    /**
     * If TRUE the configuration values in the database will be overridden
     * by the values from the configuration file. If FALSE, the option values
     * from the configuration file are ignored.
     * Useful to override this in the configuration file to quickly turn
     * on/off the overrides.
     * Default is to override the options.
     * @var boolean
     */
    private $mode_active    = TRUE;


    /**
     * This variable holds the option name of the option that can override
     * the default value of the update mode.
     * @var string
     */
    private $option_update      = 'df_fileconf_update';


    /**
     * This variable holds the option name of the option that can override
     * the default value of the debug mode.
     * @var string
     */
    private $option_debug       = 'df_fileconf_debug';


    /**
     * This variable holds the option name of the option that can override
     * if this plugin should be active or not.
     * @var string
     */
    private $option_active      = 'df_fileconf_active';


    /**
     * If TRUE, the values will not be checked against the config file.
     * @var boolean
     */
    private $lock_check = FALSE;


    /**
     * This variable holds the name of the configuration file.
     * @var string
     */
    private $config_filename    = 'file-config.php';


    /**
     * This variable will hold the array from the configuration file.
     * @var array
     */
    private $fileconf = NULL;


    /**
     * Constructor for the class DF_FileConf. The initialization happens in
     * the method init().
     * @see init
     */
    public function DF_FileConf() {
        $this->init();
    }


    /**
     * Function that loads the configuration file when an instance
     * of the class is created.
     */
    public function init() {
        $cwd = dirname(__FILE__);
        $config_file = "{$cwd}/{$this->config_filename}";
        $fileconf = $this->read_config_file($config_file);
        $this->fileconf = $fileconf;

        $this->override_plugin_options($fileconf);
    }


    /**
     * This function overrides the plugin options which are set in private
     * instance variables and not really used in the database.
     * @param array $fileconf the configuration array
     */
    private function override_plugin_options($fileconf) {
        if( isset($fileconf{$this->option_debug }) ) {
            $this->mode_debug = $fileconf{$this->option_debug};
        }
        if( isset($fileconf{$this->option_update}) ) {
            $this->mode_update = $fileconf{$this->option_update};
        }
        if( isset($fileconf{$this->option_active}) ) {
            $this->mode_active = $fileconf{$this->option_active};
        }
    }


    /**
     * Function that reads the configuration file.
     * If the configuration file does not exist it will create an empty array
     * unless the parameter $required is TRUE.
     * @param string $config_file the path to the configuration file
     * @param boolean $required if TRUE, it dies unless config file eists
     * @return array the configuration array
     */
    public function read_config_file($config_file, $required = FALSE) {
        if( is_readable($config_file) ) {
            global $fileconf;
            include($config_file);
            if( empty($fileconf) ) $fileconf = array();
            return $fileconf;
        }
        elseif( $required ) {
            die("Configuration file does not exist or is not readable: '{$config_file}'");
        }
        else {
            return array();
        }
    }


    /**
     * Function that controls the value from the database with the value
     * stored in the configuration file.
     * This function will update the database if the two values differ.
     * Beware, this function is not thread-safe!
     * @param string $option_name the name of the value
     * @param object $option_value a string or object from the database
     * @return mixed the value from the configuration file
     */
    public function check_option($option_name, $option_value) {
        if( $this->lock_check )
            return $value;

        $fileconf = $this->fileconf;
        $conf_value = $fileconf{$option_name};

        $unset_options = $fileconf{'__unset'};
        if( ! $unset_options ) $unset_options = array();

        // store away the original value,
        // to be able to print out both when debugging
        $old_value = $option_value;
        $value_changed = FALSE;

        // If the option is to be unset...
        if( in_array("$option_name", $unset_options) ) {
            if( $option_value != NULL ) {
                $option_value = NULL;
                $value_changed = TRUE;
            }
        }

        if( isset($conf_value) ) {
            // testing arrays
            if( is_array($conf_value) ) {
                // if $option_value is NULL, this function will just return
                // $conf_value
                $option_value = $this->update_option_array($option_value, $conf_value);
                if( $old_value != $option_value ) {
                    $value_changed = TRUE;
                }
            }
            // testing objects
            elseif( is_object($conf_value) ) {
                // not supported
            }
            // testing scalars
            else {
                if( "$conf_value" != "$option_value" ) {
                    $option_value = $conf_value;
                    $value_changed = TRUE;
                }
            }
        }

        if( $value_changed ) {
            $this->update_changes($option_name, $option_value, $old_value);
        }

        return $option_value;
    }


    /**
     * This function handles changes between the configuration file
     * and the database.
     * When running in debug mode, it will print the differences and die.
     * When running in update mode, it will update the database with the new
     * value.
     * @param string $option_name the name of the option to update
     * @param mixed $option_value the value from the configuration file
     * @param mixed $old_value the old value from the database
     */
    public function update_changes($option_name, $option_value, $old_value) {
        if( $this->mode_debug ) {
            echo "Values differ!\n";
            $this->print_option_diff($old_value, $option_value);
            die();
        }
        else if( $this->mode_update ) {
            // lock the update of options, not thread safe,
            // but good enough for wordpress (single threaded)
            $this->lock_check = TRUE;
            update_option($option_name, $option_value);
            $this->lock_check = FALSE;
        }
    }


    /**
     * Function that prints two values, either as strings or arrays.
     * @param mixed $old_value the old value from the database
     * @param mixed $new_value the new value from the configuration file
     */
    public function print_option_diff($old_value, $new_value) {
        if( is_array($old_value) && is_array($new_value) ) {
            echo "db:\n";
            print_r($old_value);
            echo "\nfile:\n";
            print_r($new_value);
            echo "\n\n";
        }
        else {
            echo "db: '{$old_value}', file: '{$new_value}'\n";
        }
    }


    /**
     * This function merges the values from the database options table with
     * the values from the configuration file.
     * The values from the configuration file takes precedence over the values
     * from the database.
     * @param array $option_value the database option value
     * @param array $conf_value the configuration file value
     * @return array the combined array
     */
    public function update_option_array($option_value, $conf_value) {
        if( is_array($option_value) && is_array($conf_value) ) {
            $option_value = array_merge($option_value, $conf_value);
        }
        else {
            $option_value = $conf_value;
        }
        return $option_value;
    }


    /**
     * Function that sets up the filters and actions connecting this
     * plugin with the WordPress core system.
     * The callback functions do not exist in this class, but will be handled
     * by the __call(..) function.
     * @see __call
     */
    public function setup_hooks() {
        if( ! $this->mode_active ) return;

        $fileconf = $this->fileconf;

        if( isset($fileconf{'__unset'}) ) {
            $option_names = array_merge(array_keys($fileconf), $fileconf{'__unset'});
            $option_names = array_unique($option_names);
        }
        else {
            $option_names = array_keys($fileconf);
        }

        foreach( $option_names as $option_name ) {
            add_filter(
                "option_{$option_name}",
                array(&$this, "check_option_{$option_name}")
            );
        }
    }


    /**
     * This function is executed if the called function does not exist.
     * It will be called when filtering option values.
     * @see check_option()
     * @param string $funcname the name of the function called
     * @param array $a the parameters given to the function
     * @return mixed the output after filtering
     */
    public function __call($funcname, $a) {
        // Extract the option name from the called function
        $option_name = preg_replace('|^check_option_(.*)|', '$1', $funcname);
        //we only support one parameter on this filtering
        $option_value = $a[0];
        if( $option_name ) {
            return $this->check_option($option_name, $option_value);
        }
    }
}


$df_fileconf = new DF_FileConf();
$df_fileconf->setup_hooks();

?>

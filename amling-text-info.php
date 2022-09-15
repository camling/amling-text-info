<?php

/*
Plugin Name: Amling Text Information
Description: Add information about the text, Word Count, Character Count...
Version: 1.0
Author: Chris Amling
Author URI: https://christopheramling.com
*/

class AmlingTextInformation {


    function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']); // call before admin menu loads so we can change it. Second param is an array with $this and function name

    }

    function admin_menu(){
        add_options_page('Text Information Settings','Text Info','manage_options','text_information_settings',[$this, 'settings_page_html']); // Properties Page URL Title, Menu Title, Permissions Need to access, slug, function name
        }

    function settings_page_html(){
            echo '
            <div class="wrap">
                <h1>Text Information Settings</h1>
            </div>
            ';
        }

}

$amlingTextInformation = new AmlingTextInformation();






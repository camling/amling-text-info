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
        add_action('admin_init', [$this, 'settings_page']); // called when the admin screen is initialized 
    }

    function admin_menu(){
        add_options_page('Text Information Settings','Text Info','manage_options','text_information_settings',[$this, 'settings_page_html']); // Properties Page URL Title, Menu Title, Permissions Need to access, slug, function name
        }

    function settings_page_html(){
            echo '
            <div class="wrap">
                <h1>Text Information Settings</h1>
                <form action="options.php" method="POST">';

                settings_fields('text_information_plugin'); // The group name from register settings
                do_settings_sections('text_information_settings'); // Call in the settings from the settings page
                submit_button(); // Add form submit button
            echo '</form>
            </div>
            ';
        }

    function TI_display_location_html() // The HTML for the setting field
    { ?>
        <select name="TI_display_location">
            <option value="0" <?php selected(get_option('TI_display_location'), '0')?> > Post Start</option>  <!-- Check options to see what is selected.  Using WP selected() function and pass in name of option, then what value you're looking for -->
            <option value="1" <?php selected(get_option('TI_display_location'), '1')?> > Post End</option>
        </select>
    <?php }    

    function settings_page(){
        add_settings_section('first_section',null,null,'text_information_settings');
        add_settings_field('TI_display_location','Display Location',[$this, 'TI_display_location_html'],'text_information_settings', 'first_section'); // Adding the setting field data for the location field
        register_setting('text_information_plugin','TI_display_location',['sanitize_callback' => 'sanitize_text_field', 'default' => '0']);  // Adding settings, parameters: group name, name of setting, array of options: sanatize_callback, default. (sanitize_text_field is a wordpress function to sanitize data)
    }

}

$amlingTextInformation = new AmlingTextInformation();






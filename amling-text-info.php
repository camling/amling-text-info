<?php

/*
Plugin Name: Amling Text Information
Description: Add information about the text, Word Count, Character Count...
Version: 1.0.1
Author: Chris Amling
Author URI: https://christopheramling.com
*/

class ATISettings {

    /*
    Create WP Setting page 
    Add interactions to settings page
    - Select location
    - heading text
    - display heading text
    - display word count
    - display character count
    - display estimated reading time
    */

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
                echo '<p>Turn on and off the different displays with the checkboxes</p>';
                submit_button(); // Add form submit button
            echo '</form>
            </div>
            ';
        }

    function TI_display_location_html() // The HTML for the setting location field
    { ?>
        <select name="TI_display_location">
            <option value="0" <?php selected(get_option('TI_display_location'), '0')?> > Post Start</option>  <!-- Check options to see what is selected.  Using WP selected() function and pass in name of option, then what value you're looking for -->
            <option value="1" <?php selected(get_option('TI_display_location'), '1')?> > Post End</option>
        </select>
    <?php }    

    function TI_display_headline_html() // The HTML for the setting headline field
    { ?>
        <input type="text" value="<?php echo esc_attr(get_option('TI_display_headline')); ?>" name="TI_display_headline" >
    <?php } 
    
    function TI_display_headline_checkbox_html() // The HTML for the setting Word Count field
    { ?>
        <input type="checkbox" value="1" name="TI_display_headline_checkbox" <?php checked(get_option('TI_display_headline_checkbox'), "1");?> >
    <?php }   

    function TI_display_wordcount_html() // The HTML for the setting Word Count field
    { ?>
        <input type="checkbox" value="1" name="TI_display_wordcount" <?php checked(get_option('TI_display_wordcount'), "1");?> >
    <?php }   

    function TI_display_charactercount_html() // The HTML for the setting Character Count field
    { ?>
        <input type="checkbox" value="1" name="TI_display_charactercount" <?php checked(get_option('TI_display_charactercount'), "1");?> >
    <?php }   

    function TI_display_readtime_html() // The HTML for the setting Read Time field
    { ?>
        <input type="checkbox" value="1" name="TI_display_readtime" <?php checked(get_option('TI_display_readtime'), "1");?> >
    <?php }  
    
    function sanitizeLocation($input) // Only allow inputs of 1 or 0, otherwise return whatever value is currently in the database
    { 
        if($input == '0' || $input == '1'){
            return $input;
        }
        else
        {
            add_settings_error('TI_display_location','TI_display_location_error', 'The value of Display Location must be Post Start or Post End');
            return get_option('TI_display_location');
        }
    }

    function settings_page(){
        add_settings_section('first_section',null,null,'text_information_settings');

        add_settings_field('TI_display_location','Choose Location',[$this, 'TI_display_location_html'],'text_information_settings', 'first_section'); // Adding the setting field data for the location field
        register_setting('text_information_plugin','TI_display_location',['sanitize_callback' => [$this, 'sanitizeLocation'], 'default' => '0']);  // Adding settings, parameters: group name, name of setting, array of options: sanatize_callback, default. (sanitize_text_field is a wordpress function to sanitize data)

        add_settings_field('TI_display_headline','Headline Text',[$this, 'TI_display_headline_html'],'text_information_settings', 'first_section'); // Adding the setting field data for the heading text field
        register_setting('text_information_plugin','TI_display_headline',['sanitize_callback' => 'sanitize_text_field', 'default' => 'Page Text Information']); 

        add_settings_field('TI_display_headline_checkbox','Display Headline',[$this, 'TI_display_headline_checkbox_html'],'text_information_settings', 'first_section'); // Adding the setting field data for the Display Heading Checkbox Field
        register_setting('text_information_plugin','TI_display_headline_checkbox',['sanitize_callback' => 'sanitize_text_field', 'default' => '1']); 
        
        add_settings_field('TI_display_wordcount','Display Word Count',[$this, 'TI_display_wordcount_html'],'text_information_settings', 'first_section'); // Adding the setting field data for the Display Word Count text field
        register_setting('text_information_plugin','TI_display_wordcount',['sanitize_callback' => 'sanitize_text_field', 'default' => '1']); 

        add_settings_field('TI_display_charactercount','Display Character Count',[$this, 'TI_display_charactercount_html'],'text_information_settings', 'first_section'); // Adding the setting field data for the Display Character Count text field
        register_setting('text_information_plugin','TI_display_charactercount',['sanitize_callback' => 'sanitize_text_field', 'default' => '1']); 

        add_settings_field('TI_display_readtime','Display Estimated Read Time',[$this, 'TI_display_readtime_html'],'text_information_settings', 'first_section'); // Adding the setting field data for the Display Character Count text field
        register_setting('text_information_plugin','TI_display_readtime',['sanitize_callback' => 'sanitize_text_field', 'default' => '1']); 
    }

}

class ATIDisplay {

    /*
    Create the Word Count, Character Count and Expected Reading time based on if thoes settings are enabled.
    Return the content with the enabled data appended above or below the content. 
    */

    function __construct()
    {
        add_filter('the_content', [$this, 'filterIf']);
    }

    function filterIf($the_content)
    {
        if((is_main_query() && is_single()) && (get_option('TI_display_wordcount') || get_option('TI_display_charactercount')  || get_option('TI_display_readtime'))) // Run filter if one of filter options is checked and its not on a page
        {
            return $this->display_html($the_content);
        }
        return $the_content;
    }

    function get_word_count($the_content)
    {
        return str_word_count(strip_shortcodes($the_content));
    }

    function get_character_count($the_content)
    {
        return strlen(strip_shortcodes($the_content));
    }

    function get_estimated_reading_time( $the_content, $wpm = 250 ) {
        $clean_content = strip_tags( $the_content );
        $word_count = $this->get_word_count($clean_content);
        $time = ceil( $word_count / $wpm );
        return $time;
    }

    function display_html($the_content)
    {

       $extra_content = "<div class='ati_block'>";
      
       if(get_option('TI_display_headline_checkbox') == "1")
       {
       $extra_content .= "<h2 class='ati_heading'>".get_option('TI_display_headline', "Text Information"). "</h2>";
       }
       
       if(get_option('TI_display_wordcount') == "1")
       {
        $extra_content .= "<p class='ati_word_count'>Word Count: <span>" . $this->get_word_count($the_content) . "</span></p>";
       }

       if(get_option('TI_display_charactercount') == "1")
       {
        $extra_content .= "<p class='ati_character_count'>Character Count: <span>" . $this->get_character_count($the_content) . "</span></p>";
       }

       if(get_option('TI_display_readtime') == "1")
       {
        $extra_content .= "<p class='ati_reading_time'>Est. Reading Time: <span>" . $this->get_estimated_reading_time($the_content) . " minutes </span></p>";
       }

       $extra_content .= "</div>";


       if(get_option('TI_display_location') == "1")
       {
         return $the_content . $extra_content;
       }
       else
       {
        return  $extra_content . $the_content;
       }
       
    }

}

$ati_settings = new ATISettings();
$ati_display = new ATIDisplay();

register_activation_hook( __FILE__, 'ati_plugin_activate' );
register_deactivation_hook( __FILE__, 'ati_plugin_deactivate' );

function ati_plugin_activate() // Create the options fields in the database upon plugin activation
{
        add_option("TI_display_location", "1");
        add_option("TI_display_headline", "Post Information");
        add_option("TI_display_headline_checkbox", "1");
        add_option("TI_display_wordcount", "1");
        add_option("TI_display_charactercount", "1");
        add_option("TI_display_readtime", "1");
   
}

function ati_plugin_deactivate() // Remove the options fields in the database upon plugin deactivation
{ 
	delete_option( "TI_display_location" );
    delete_option( "TI_display_headline" );
    delete_option( "TI_display_headline_checkbox" );
    delete_option( "TI_display_wordcount" );
    delete_option( "TI_display_charactercount" );
    delete_option( "TI_display_readtime" );
}

<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      0.0.1
 *
 * @package    Examity_Client
 * @subpackage Examity_Client/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h1><?php _e('Examity Client Settings', 'examity-client'); ?></h1>
     <form action="options.php" method="post">
<?php       
settings_fields( $this->option_name );
do_settings_sections( $this->option_name.'_general-section' );
submit_button(); ?>             
    </form>
</div>

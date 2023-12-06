<?php 

?>

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <form method="post" action="options.php">
                <?php settings_fields( 'belbo-api-settings' ) ?>
                <?php do_settings_sections( 'belbo-api-settings' ); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <div class="col-md-6"></div>
    </div>
</div>
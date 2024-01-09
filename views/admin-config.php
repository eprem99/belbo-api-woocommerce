<?php 

?>

<div id="main" class="container">
    <div class="row">
        <div class="col-md-6">
            <form method="post" action="options.php">
                <?php settings_fields( 'belbo-api-settings' ) ?>
                <?php do_settings_sections( 'belbo-api-settings' ); ?>

                <?php submit_button(); ?>
            </form>
        </div>
        <div class="col-md-6">
        <?php
            $run = get_option('belbo_cron_log');
            if($run){
        ?>
            <div id="container">
                <?php
                    $file = BELBO_PLUGIN_DIR."/inc/belbo.log";
                    $myfile = fopen($file, "r");
                    if(filesize($file) > 0){
                        echo fread($myfile, filesize($file));
                    }else{
                        echo 'No log found';
                    }
                    fclose($myfile);
                ?> 
            </div>
            <?php } ?>
        </div>
    </div>
</div>
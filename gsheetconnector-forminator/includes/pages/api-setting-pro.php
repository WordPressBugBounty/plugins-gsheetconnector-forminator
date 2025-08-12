<div class="card-wp">
    <div class="gs_formntr_in_fields">
        <h2><span class="title1"><?php echo esc_html__(' Google - ', 'gsheetconnector-forminator'); ?></span><span class="title"><?php echo esc_html__('  API Settings', 'gsheetconnector-forminator'); ?></span></h2>
        <hr>
        <p class="formntr-gs-alert-kk"> <?php echo esc_html__('Create new google APIs with Client ID and Client Secret keys to get an access for the google drive and google sheets.', 'gsheetconnector-forminator'); ?></p>
        <p>
        <div class="wg_api_set">
            <div class="wg_api_option">
                <div class="wg_api_label">
                    <label><?php echo esc_html__('Client Id', 'gsheetconnector-forminator'); ?></label>
                </div>
                <div class="wg_api_input">
                    <input type="text" name="gs-formntr-client-id" id="gs-formntr-client-id" placeholder="" /><br>
                </div>
            </div>
            <div class="wg_api_option">
                <div class="wg_api_label">
                    <label><?php echo esc_html__('Client Secret', 'gsheetconnector-forminator'); ?></label>
                </div>
                <div class="wg_api_input">
                    <input type="text" name="gs-formntr-client-secret" id="gs-formntr-client-secret" placeholder="" />
                </div>
            </div>
            <div class="wg_api_option">
                <div class="wg_api_label">
                    <label><?php echo esc_html__('Client Token', 'gsheetconnector-forminator'); ?></label>
                </div>
               
                <?php
                $gsformntr_auth_url = GSC_FORMNTR_googlesheet::getClient_auth();
                ?>
                <a href="<?php echo esc_url_raw($gsformntr_auth_url); ?>" id="authlink_gsformntr" target="_blank">
                    <div class="gsformntr-button gsformntr-button-secondary"><?php echo esc_html__("Click here to generate an Authentication Token", 'gsheetconnector-forminator'); ?>
                    </div>
                </a>
            </div>
            <div class="wg_api_option">
                <input type="button" class="gs-formntr-revoke" name="revoke-formntr-gapi" id="revoke-formntr-gapi" value="Revoke Token">
            </div>
            <div class="wg_api_option">
              <input type="button" class="gs-formntr-save" name="save-formntr-gapi" id="save-formntr-gapi" value="<?php echo esc_attr__( 'Save', 'gsheetconnector-forminator' ); ?>">
             <input type="reset" class="gs-formntr-reset" name="save-formntr-reset" id="save-formntr-reset" value="<?php echo esc_attr__( 'Reset', 'gsheetconnector-forminator' ); ?>">
           </div>

            <span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
        </div>
    </div>
</div>
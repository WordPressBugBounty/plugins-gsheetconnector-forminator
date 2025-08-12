<?php
function remove_footer_admin()
{
?><p id="footer-left" class="alignleft">

    <?php echo esc_html__("Please rate", "gsheetconnector-forminator"); ?>

    <strong><?php echo esc_html__("Forminator GSheetConnector", 'gsheetconnector-forminator'); ?></strong> <a href="https://wordpress.org/support/plugin/gsheetconnector-forminator/reviews/" target="_blank" rel="noopener noreferrer">★★★★★</a> on <a href="https://wordpress.org/support/plugin/gsheetconnector-forminator/reviews/" target="_blank" rel="noopener"><?php echo esc_html__("WordPress.org", 'gsheetconnector-forminator'); ?></a> <?php echo esc_html__("to help us spread the word.", 'gsheetconnector-forminator'); ?>
  </p> <?php
      }
      add_filter('admin_footer_text', 'remove_footer_admin');

        ?>
<div class="gsheetconnect-footer-promotion">
  <p><?php echo esc_html__("Made with ♥ by the GSheetConnector Team", 'gsheetconnector-forminator'); ?></p>
  <ul class="gsheetconnect-footer-promotion-links">
    <li> <a href="https://www.gsheetconnector.com/support" target="_blank"><?php echo esc_html__("Support", 'gsheetconnector-forminator'); ?></a> </li>
    <li> <a href="https://support.gsheetconnector.com/kb-category/forminator-forms-gsheetconnector" target="_blank"><?php echo esc_html__("Docs", 'gsheetconnector-forminator'); ?></a> </li>
    <li> <a href="https://www.facebook.com/gsheetconnectorofficial" target="_blank"><?php echo esc_html__("VIP Circle", 'gsheetconnector-forminator'); ?></a> </li>
    <li> <a href="https://profiles.wordpress.org/westerndeal/#content-plugins"><?php echo esc_html__("Free Plugins", 'gsheetconnector-forminator'); ?></a> </li>
  </ul>
  <ul class="gsheetconnect-footer-promotion-social">
    <li> <a href="https://www.facebook.com/gsheetconnectorofficial" target="_blank"> <i class="fa fa-facebook-square" aria-hidden="true"></i> </a> </li>
    <li> <a href="https://www.instagram.com/gsheetconnector/" target="_blank"> <i class="fa fa-instagram" aria-hidden="true"></i> </a> </li>
    <li> <a href="https://www.linkedin.com/in/abdullah17/" target="_blank"> <i class="fa fa-linkedin-square" aria-hidden="true"></i> </a> </li>
    <li> <a href="https://twitter.com/gsheetconnector?lang=en" target="_blank"> <i class="fa fa-twitter-square" aria-hidden="true"></i> </a> </li>
    <li> <a href="https://www.youtube.com/@GSheetConnector" target="_blank"> <i class="fa fa-youtube-square" aria-hidden="true"></i> </a> </li>
  </ul>
</div>
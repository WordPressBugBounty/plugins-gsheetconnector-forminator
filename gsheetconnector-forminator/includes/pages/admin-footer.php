<?php
function remove_footer_admin()
{
?><p id="footer-left" class="alignleft">

    <?php echo esc_html__("Please rate", "gsheetconnector-forminator"); ?>

    <strong><?php echo esc_html__("GSheetConnector for Forminator Forms", 'gsheetconnector-forminator'); ?></strong> <a href="https://wordpress.org/support/plugin/gsheetconnector-forminator/reviews/" target="_blank" rel="noopener noreferrer">★★★★★</a> on <a href="https://wordpress.org/support/plugin/gsheetconnector-forminator/reviews/" target="_blank" rel="noopener"><?php echo esc_html__("WordPress.org", 'gsheetconnector-forminator'); ?></a> <?php echo esc_html__("to help us spread the word.", 'gsheetconnector-forminator'); ?>
  </p> <?php
      }
      add_filter('admin_footer_text', 'remove_footer_admin');

        ?>
<div class="gsheetconnect-footer-promotion">
  <p><?php echo esc_html__("Made with ♥ by the GSheetConnector Team", 'gsheetconnector-forminator'); ?></p>
  <ul class="gsheetconnect-footer-promotion-links">
    <li> <a href="https://www.gsheetconnector.com/support" target="_blank"><?php echo esc_html__("Support", 'gsheetconnector-forminator'); ?></a> </li>
    <li> <a href="https://www.gsheetconnector.com/docs/forminator-forms-gsheetconnector/introduction" target="_blank"><?php echo esc_html__("Docs", 'gsheetconnector-forminator'); ?></a> </li>
    <li> <a href="https://profiles.wordpress.org/westerndeal/#content-plugins"><?php echo esc_html__("Free Plugins", 'gsheetconnector-forminator'); ?></a> </li>
  </ul>
  <ul class="gsheetconnect-footer-promotion-social">
    <li> <a href="https://www.facebook.com/gsheetconnectorofficial" target="_blank"> <i class="fa-brands fa-facebook"></i></a> </li>
    <li> <a href="https://www.instagram.com/gsheetconnector/" target="_blank"> <i class="fa-brands fa-square-instagram"></i> </a> </li>
    <li> <a href="https://www.linkedin.com/company/gsheetconnector/" target="_blank"> <i class="fa-brands fa-square-linkedin"></i> </a> </li>
    <li> <a href="https://twitter.com/gsheetconnector?lang=en" target="_blank"> <i class="fa-brands fa-square-twitter"></i> </a> </li>
    <li> <a href="https://www.youtube.com/@GSheetConnector" target="_blank"> <i class="fa-brands fa-square-youtube"></i> </a> </li>
  </ul>
</div>
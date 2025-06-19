<!-- plugin promotion footer-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<?php
function remove_footer_admin () 
{
     ?><p id="footer-left" class="alignleft"> 

<?php echo __("Please rate", "gsheetconnector-forminator"); ?>

		 <strong><?php echo __("Forminator GSheetConnector", 'gsheetconnector-forminator'); ?></strong> <a href="https://wordpress.org/support/plugin/gsheetconnector-forminator/reviews/?filter=5" target="_blank" rel="noopener noreferrer">★★★★★</a> on <a href="https://wordpress.org/support/plugin/gsheetconnector-forminator/reviews/?filter=5" target="_blank" rel="noopener"><?php echo __("WordPress.org", 'gsheetconnector-forminator'); ?></a> <?php echo __("to help us spread the word.", 'gsheetconnector-forminator'); ?> 	</p> <?php 
}
add_filter('admin_footer_text', 'remove_footer_admin');

 ?>
<div class="gsheetconnect-footer-promotion">
  <p><?php echo __("Made with ♥ by the GSheetConnector Team", 'gsheetconnector-forminator'); ?></p>
  <ul class="gsheetconnect-footer-promotion-links">
    <li> <a href="https://www.gsheetconnector.com/support" target="_blank"><?php echo __("Support", 'gsheetconnector-forminator'); ?></a> </li>
    <li> <a href="https://support.gsheetconnector.com/kb-category/forminator-forms-gsheetconnector" target="_blank"><?php echo __("Docs", 'gsheetconnector-forminator'); ?></a> </li>
    <li> <a href="https://www.facebook.com/gsheetconnectorofficial" target="_blank"><?php echo __("VIP Circle", 'gsheetconnector-forminator'); ?></a> </li>
    <li> <a href="https://profiles.wordpress.org/westerndeal/#content-plugins"><?php echo __("Free Plugins", 'gsheetconnector-forminator'); ?></a> </li>
  </ul>
  <ul class="gsheetconnect-footer-promotion-social">
    <li> <a href="https://www.facebook.com/gsheetconnectorofficial" target="_blank"> <i class="fa fa-facebook-square" aria-hidden="true"></i> </a> </li>
    <li> <a href="https://www.instagram.com/gsheetconnector/" target="_blank"> <i class="fa fa-instagram" aria-hidden="true"></i> </a> </li>
    <li> <a href="https://www.linkedin.com/in/abdullah17/" target="_blank"> <i class="fa fa-linkedin-square" aria-hidden="true"></i> </a> </li>
    <li> <a href="https://twitter.com/gsheetconnector?lang=en" target="_blank"> <i class="fa fa-twitter-square" aria-hidden="true"></i> </a> </li>
    <li> <a href="https://www.youtube.com/@GSheetConnector" target="_blank"> <i class="fa fa-youtube-square" aria-hidden="true"></i> </a> </li>
  </ul>
</div>

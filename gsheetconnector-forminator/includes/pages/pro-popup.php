<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<div id="popup" class="popup">
	<div class="popup-outer" id="popup-outer"></div>
    
    <div class="popup-container">
     <button id="closeButton" class="popup-close"><i class="fa fa-times" aria-hidden="true"></i></button>
	<div class="popup-content align-center">
    <i class="fa fa-lock popup-icon" aria-hidden="true"></i>
	<h2><?php echo esc_html( __( 'GSheetConnector for Forminator PRO Features', 'gsheetconnector-forminator' ) ); ?></h2>
	<p><?php echo __('This features is available in the PRO version of the plugin. To <strong>Enable the options Upgrade to the PRO</strong> version to unlock all these awesome features.', 'gsheetconnector-forminator'); ?></p>
    <a href="https://www.gsheetconnector.com/forminator-forms-google-sheet-connector-pro" target="_blank" class="popup-btn-normal">
	<?php echo esc_html( __( 'Upgrade To PRO', 'gsheetconnector-forminator' ) ); ?></a>
    </div>
    
    
    <p class="note"><?php echo __('Bonus: GSheetConnector for Forminator Lite users will get <strong>Special Discounts</strong> for unlimited site licence, automatically applied at checkout.', 'gsheetconnector-forminator'); ?></p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var opener = document.getElementById('opener');
    var popup = document.getElementById('popup');
    var closeButton = document.getElementById('closeButton');
    var popupOuter = document.getElementById('popup-outer');

    opener.addEventListener('click', function() {
        fadeIn(popup);
    });

    closeButton.addEventListener('click', function() {
        fadeOut(popup);
    });

    popupOuter.addEventListener('click', function(event) {
        if (event.target === popupOuter) {
            fadeOut(popup);
        }
    });

    function fadeIn(element) {
        var opacity = 0;
        element.style.opacity = opacity;
        element.style.display = 'block';
        var fadeInInterval = setInterval(function() {
            if (opacity < 1) {
                opacity += 0.1;
                element.style.opacity = opacity;
            } else {
                clearInterval(fadeInInterval);
            }
        }, 50);
    }

    function fadeOut(element) {
        var opacity = 1;
        var fadeOutInterval = setInterval(function() {
            if (opacity > 0) {
                opacity -= 0.1;
                element.style.opacity = opacity;
            } else {
                clearInterval(fadeOutInterval);
                element.style.display = 'none';
            }
        }, 50);
    }
});

</script>

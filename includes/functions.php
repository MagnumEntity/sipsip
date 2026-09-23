<?php
// includes/functions.php

/**
 * Basic helper functions for SipSip.
 * Further functions will be implemented in subsequent phases.
 */

/**
 * Sanitize output for HTML display to prevent XSS.
 */
function escape_html($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>

<?php
/**
 * Root Index - Redirect to public directory
 * This file ensures proper routing when server is run from root
 */

// Redirect to public directory
header('Location: /public/login.php');
exit();


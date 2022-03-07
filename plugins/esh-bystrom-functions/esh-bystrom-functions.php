<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/** 
*  Plugin Name: Bystrom functions
*  Description: Plugin for extra options specifically for the Bystrom webshop
*  Version: 1.0
*  Text Domain: bystrom-functions
*  Author: Elias Hakuni
*  Author URI: hakuni.se
*/

require_once 'esh-backend.php';
require_once 'esh-frontend.php';
require_once 'esh-price-filter-sql.php';
require_once 'price-format.php';
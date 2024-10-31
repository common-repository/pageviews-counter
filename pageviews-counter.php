<?php
/*
Plugin Name: PageViews Counter
Description: Lightweight pageviews counter.
Version: 3.0.1
Author: Petr Paboucek -aka- BoUk
Author URI: https://wpadvisor.co.uk
Text Domain: pageviews-counter
*/

if ( ! defined( 'ABSPATH' ) ) 
    exit;

define( 'BPVC_PLUGIN_PATH', 	plugin_dir_path( __FILE__ ) );
define( 'BPVC_PLUGIN_URL', 		plugin_dir_url( __FILE__ ) );
define( 'BPVC_PLUGIN_VERSION', 	'3.0.1' );

/**
 * Load required files
 */
require BPVC_PLUGIN_PATH . "vendor/autoload.php";

$pvcView 		= new \Latte\Engine();
$pvcView->setTempDirectory( BPVC_PLUGIN_PATH . 'latte/' );

$pvcModel 		= new \BoUk\PageViewsCounter\Models\pvcModel();
$pvcController 	= new \BoUk\PageViewsCounter\Controllers\pvcController( $pvcModel, $pvcView );

/**
 * Adding custom cron schedule required for regular fetch of counters 
 * from storage
 */
add_filter( 'cron_schedules', [ 
				$pvcModel, 			
				'addCronSchedules' 
			]);

/**
 * Making sure re-occuring cron event is set
 */
add_action( 'init',	[ 
				$pvcController, 
				'scheduleCronEvent' 
			]);	

/**
 * Logic for inlining counter JS
 */
add_action( 'wp_footer', [
    			$pvcController,
    			'maybeInjectCounter'
			], 
			999 );

/**
 * Allow for injection of script via Timber/Twig context
 */
add_filter( 'timber/context', [ 
    			$pvcController,
    			'addToContext' 
			]);

/**
 * Custom cron action. Move counters from temporary storage into DB
 */
add_action( $pvcModel->cronEvent, [ 
				$pvcController, 
				'storeViews' 
			]);

/**
 * Clean up after deactivation
 */
register_deactivation_hook( __FILE__, [ 
				$pvcController, 
				'deactivate' 
			]);

/**
 * Remove re-occuring cron event
 */
add_action( 'pc_plugin_deactivate', [
				$pvcController, 
				'removeCronEvents' 
			]);

/**
 * Add extra column with pageviews into posts admin screen
 */
add_filter( 'manage_posts_columns', [ 
				$pvcModel, 
				'addPvColumn' 
			]);

/**
 * Add extra column with pageviews into pages admin screen
 */
add_filter( 'manage_pages_columns', [ 
				$pvcModel, 
				'addPvColumn' 
			]);

/**
 * Fetches pageviews from custom post meta
 */
add_action( 'manage_posts_custom_column', [ 
				$pvcController, 
				'pvColumnContent' 
			], 
			10, 2 );

add_action( 'manage_pages_custom_column', [ 
				$pvcController, 
				'pvColumnContent' 
			], 
			10, 2 );    
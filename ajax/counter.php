<?php

/**
 * Load required files
 */
require __DIR__ . "/../vendor/autoload.php";

use Phpfastcache\CacheManager;
use Phpfastcache\Core\phpFastCache;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

$settings = require __DIR__ . "/../app/settings.php";

if ( ! $settings || ! isset( $settings['cache']['driver'] ) || ! isset( $settings['cache']['options'] ) || ! isset ( $settings['cache']['configClass'] ) )
	die();

if ( ! isset( $_POST['postId'] ) || intval( $_POST['postId'] ) <= 0 || ! isset( $_POST['siteId'] ) || intval( $_POST['siteId'] ) < 0 )
	die();

if ( (new CrawlerDetect)->isCrawler() ) 
    die();

$cacheInstance = CacheManager::getInstance( 
					$settings['cache']['driver'], 
					new $settings['cache']['configClass']( $settings['cache']['options'] )
				);

/*
	Make the key very specific - allows for running this plugin even in multisite
	or more sites on one server with single memcached instance
 */
$key = $settings['cache']['prefix'] . intval( $_POST['siteId'] ) . "-post-" . intval( $_POST['postId'] );

$cache = $cacheInstance->getItem( $key );

if ( is_null( $cache->get() ) ) 
    $cache->set(1)->expiresAfter(3600)->addTag( $settings['cache']['prefix'] . intval( $_POST['siteId'] ) . '_pageviews_counter' );       
 
else 
    $cache->increment();

$cacheInstance->save( $cache );
echo $key . ':' . intval( $cache->get() );

die();
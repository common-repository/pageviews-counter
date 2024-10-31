<?php

namespace BoUk\PageViewsCounter\Models;

use Phpfastcache\CacheManager;
use Phpfastcache\Core\phpFastCache;

/**
 * 
 */
class pvcModel
{		
	/**
	 * Name of custom cron schedule
	 */
	public $cronSchedule = 'five_minute';

	/**
	 * Name of custom cron event
	 */
	public $cronEvent 	= 'pc_five_minute';	

	/**
	 * [$wpdb description]
	 * @var [type]
	 */
	protected $wpdb;

	/**
	 * [$settings description]
	 * @var [type]
	 */
	protected $settings;

	/**
	 * [__construct description]
	 */
	public function __construct()
	{
		global $wpdb;

		$this->wpdb 	= $wpdb;
		$this->settings = require __DIR__ . "/../settings.php";
	}

	/**
	 * [addCronSchedules description]
	 * @param [type] $schedules [description]
	 */
	public function addCronSchedules( $schedules ) 
	{
		$schedules[$this->cronSchedule] = array (
            	'interval'  => 300,
            	'display'   => 'Once in 5 minutes'
    	);    	

    	return $schedules;
	}

	/**
	 * [setCacheManager description]
	 */
	public function setCacheManager()
	{		
		return CacheManager::getInstance( 
					$this->settings['cache']['driver'], 
					new $this->settings['cache']['configClass']( $this->settings['cache']['options'] )
		 		);		
	}

	/**
	 * [getAllCounters description]
	 * @param  [type] $cacheInstance [description]
	 * @return [type]                [description]
	 */
	public function getAllCounters( $cacheInstance  )
	{
		return $cacheInstance->getItemsByTag( $this->settings['cache']['prefix'] . $this->getSiteId() . '_pageviews_counter' );
	}

	/**
	 * [getViews description]
	 * @param  [type] $cacheInstance [description]
	 * @param  [type] $key           [description]
	 * @return [type]                [description]
	 */
	public function getViews( $cacheInstance, $key )
	{
		if ( ! $key )
			return;
		
		$cached = $cacheInstance->getItem( $key );			
		$views = $cached->get();

		if ( intval( $views ) <= 0 )
			return false;

		return $views;					
	}

	/**
	 * [updateViews description]
	 * @param  [type] $key   [description]
	 * @param  [type] $views [description]
	 * @return [type]        [description]
	 */
	public function updateViews( $key, $views )
	{
		$postId = str_replace( $this->settings['cache']['prefix'] . $this->getSiteId() . '-post-', '', $key );

		if ( intval( $postId ) <= 0 || intval( $views ) <= 0 )
			return false;		

		$metaId = $this->counterExists( $postId );

		/*
			WP action allowing to store pageviews externally for potential more
			in-depth analysis
		 */		 
		do_action( 'pvc_update_views', $postId, $views );			

		/**
		 * We are updating existing counter
		 */
		if ( $metaId )
		{			
			$result = $this->wpdb->query(
					$this->wpdb->prepare(
						"UPDATE " . $this->wpdb->prefix . "postmeta
			        	SET meta_value = meta_value+%d
			        	WHERE meta_id = %d",
			           	$views,
			           	$metaId
					)
				);

			wp_cache_delete( $postId, 'post_meta' );

			return $result;
		}

		/**
		 * Counter doesn't exist, we need to create new one
		 */
		else 
		{
			return add_post_meta( $postId, '_pageviews', intval( $views ), true );
		}
		
	}

	/**
	 * Add extra column into WP Admin standard screen
	 * @param [type] $columns [description]
	 */
	public function addPvColumn( $columns )
	{
		$columns['pageviews'] = 'Pageviews';
      	return $columns;
	}

	/**
	 * [getSiteId description]
	 * @return [type] [description]
	 */
	public function getSiteId()
	{
		return ( is_multisite() ) ? get_current_blog_id() : 0;
	}

	/**
	 * [validateInjection description]
	 * @param  [type] $post [description]
	 * @return [type]       [description]
	 */
	public function validateInjection( $post )
	{
		if ( ! is_object( $post ) )
			return false;

		if ( $post->post_status != 'publish' )
			return false;

		if ( ! is_singular() )
			return false;

		return true;
	}

	/**
	 * [counterExists description]
	 * @param  [type] $postId [description]
	 * @return [type]         [description]
	 */
	private function counterExists( $postId )
	{
		return $this->wpdb->get_var(
				$this->wpdb->prepare(
					"SELECT meta_id FROM " . $this->wpdb->prefix . "postmeta
					WHERE post_id = %d AND meta_key = '_pageviews'",
					$postId
			));
	}

}
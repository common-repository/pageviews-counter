<?php

namespace BoUk\PageViewsCounter\Controllers;

/**
 * 
 */
class pvcController
{

	/**
	 * [$model description]
	 * @var [type]
	 */
	protected $model;

	/**
	 * [$view description]
	 * @var [type]
	 */
	protected $view;

	/**
	 * [__construct description]
	 * @param \BoUk\PageViewsCounter\Models\pvcModel $model [description]
	 * @param \Latte\Engine                          $view  [description]
	 */
	public function __construct( \BoUk\PageViewsCounter\Models\pvcModel $model, \Latte\Engine $view )
	{
		$this->model 	= $model;
		$this->view 	= $view;
	}

	/**
	 * [scheduleCronEvent description]
	 * @return [type] [description]
	 */
	public function scheduleCronEvent()
	{
		if ( ! wp_next_scheduled( $this->model->cronEvent ) ) 
    		wp_schedule_event( time(),  $this->model->cronSchedule,  $this->model->cronEvent );
	}

	/**
    * [maybeInjectCounter description]
    * @return [type] [description]
    */
   	public function maybeInjectCounter()
	{
		global $wp_query;

		if ( ! $this->model->validateInjection( $wp_query->post ) )
			return false;

		$args = [
			'post_id' 	=> $wp_query->post->ID,
			'site_id' 	=> $this->model->getSiteId(),
			'ajax_url'	=> BPVC_PLUGIN_URL . 'ajax/counter.php'
		];

		$this->view->render( 
			BPVC_PLUGIN_PATH . 'app/Views/counter.js.latte',
			$args
		);
	}

	/**
	 * [addToContext description]
	 * @param [type] $context [description]
	 */
	public function addToContext( $context )
	{
		global $wp_query;

		$context['pvc_counter_script'] = false;
		
		if ( $this->model->validateInjection( $wp_query->post ) )
		{
			$args = [
				'post_id' 	=> $wp_query->post->ID,
				'site_id' 	=> $this->model->getSiteId(),
				'ajax_url'	=> BPVC_PLUGIN_URL . 'ajax/counter.php'
			];

			$counterJs = $this->view->renderToString( 
				BPVC_PLUGIN_PATH . 'app/Views/counter.js.latte',
				$args
			);
			
			$context['pvc_counter_script'] = $counterJs;
		}

		return $context;
	}

	/**
	 * [storeViews description]
	 * @return [type] [description]
	 */
	public function storeViews()
	{
		$cacheInstance = $this->model->setCacheManager();

		$counters = $this->model->getAllCounters( $cacheInstance );

		if ( ! $counters )
			return;		

		foreach( $counters as $counter )
		{						
			$key = $counter->getKey();						
			$views = $this->model->getViews( $cacheInstance, $key );			

			if ( ! $views )
				return;

			$result = $this->model->updateViews( $key, $views );

			if ( $result )
				$cacheInstance->deleteItem( $key );
		}
	}

	/**
	 * [pvColumnContent description]
	 * @param  [type] $name   [description]
	 * @param  [type] $postId [description]
	 * @return [type]         [description]
	 */
	public function pvColumnContent( $name, $postId )
	{
		if ( $name == 'pageviews' ) 
		{
          $pageviews = get_post_meta( $postId, '_pageviews', true );
          
          if ( $pageviews )           
            echo intval( $pageviews );          
          
          else 
            echo '-';          
      	}
	}

	/**
	 * Action executed on plugin deactivation
	 * @return [type] [description]
	 */
	public function deactivate()
	{
		do_action( 'pc_plugin_deactivate' );
	}

	/**
	 * [removeCronEvents description]
	 * @return [type] [description]
	 */
	public function removeCronEvents()
	{
		$timestamp = wp_next_scheduled( $this->model->cronEvent );
		wp_unschedule_event( $timestamp, $this->model->cronEvent );
	}

}
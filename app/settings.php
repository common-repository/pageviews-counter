<?php 

$pvcConfig 		= false;
$pvcWpConfig 	= dirname( dirname( dirname( dirname(__DIR__) ) ) ) . '/wp-config.php';

/*
	Let's try to load settings from wp-config.php
	In case it's found, it allows to keep custom config even when plugin is updated.

	In case file is not found, fallback to default file-based cache
 */
if ( file_exists($pvcWpConfig) )
{
	$wpConfigContent = file_get_contents( $pvcWpConfig );	

	$pattern = '/define\s*\(\s*[\'"]WP_PVC_CONFIG[\'"]\s*,\s*(\[.*?\])\s*\)\s*;/s';

	if ( preg_match($pattern, $wpConfigContent, $matches) )
	    $pvcConfig = eval("return {$matches[1]};");
}

if ( $pvcConfig )
{
	return [ 'cache'=> $pvcConfig ];
}
else
{
	return [
		'cache'	=> [
			'driver'		=> 'files',
			'configClass'	=> '\Phpfastcache\Config\Config',
			'prefix'        => 'pvc_',
			'options'		=> [
				"path"              => __DIR__ . "/../cache",
	        	"itemDetailedDate"  => false
			]	
		]
	];
}

/*

See examples for other storages here https://github.com/PHPSocialNetwork/phpfastcache/tree/master/docs/examples

// Configuration example for file based storage
return [
	'cache'	=> [
		'driver'		=> 'files',
		'configClass'	=> '\Phpfastcache\Config\Config',
		'prefix'        => 'pvc_',
		'options'		=> [
			"path"              => __DIR__ . "/../cache",
        	"itemDetailedDate"  => false
		]	
	]
];


// Configuration example for MemcacheD storage
return [
	'cache'	=> [
		'driver'		=> 'memcached',
		'configClass'	=> '\Phpfastcache\Drivers\Memcached\Config',
		'prefix'        => 'pvc_',
		'options'		=> [
			'host' =>'127.0.0.1',
    		'port' => 11211,
		]	
	]
];

// Configuration example for Redis storage
return [
    'cache' => [
        'driver'        => 'redis',
        'configClass'   => '\Phpfastcache\Drivers\Redis\Config',
        'prefix'        => 'pvc_',
        'options'       => [
            'host'      =>'127.0.0.1',
            'port'      => 6379,
            'database'  => 1
        ]   
    ]
];

*/
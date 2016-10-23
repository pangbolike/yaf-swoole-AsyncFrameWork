<?php
	define("WORKER_UID",1001);
	define("WORKER_GID",1001);


class HttpServer
{
	public static $instance;

	public static $get;
	public static $rawConent;
	public static $header;
	public static $server;
	public static $task;
	public static $taskData;
	public static $cookie;
	public static $req;
	public static $rsp;
	public static $rspData;

	public $http;
	private $application;

	public function __construct() {
		$http = new swoole_http_server("0.0.0.0", 9501);

		$this->http = $http;

		$http->set(
			array(
                'worker_num' => 256,
                'task_worker_num' => 1,
                'task_ipc_mode' => 1,
                'max_conn' => 65535,
                'backlog' => 2048,
                'log_file' => '/data/log/swoole/swoole_tarot.log',
                //'buffer_output_size' => 1024 * 1024 * 50,
                'daemonize' => 1
			)
		);

		$http->on('WorkerStart' , array( $this , 'onWorkerStart'));

		$http->on('request', function ($request, $response) {
			if( isset($request->server) ) {
				HttpServer::$server = $request->server;
			}else{
				HttpServer::$server = [];
			}
			if( isset($request->header) ) {
				HttpServer::$header = $request->header;
			}else{
				HttpServer::$header = [];
			}
			if( isset($request->get) ) {
				HttpServer::$get = $request->get;
			}else{
				HttpServer::$get = [];
			}
			if (isset($request->cookie)){
				HttpServer::$cookie = $request->cookie;
			}else{
				HttpServer::$cookie = [];
			}
			HttpServer::$req = $request;
			HttpServer::$rsp = $response;
			HttpServer::$task = false;
			HttpServer::$taskData = [];
			HttpServer::$rawConent = $request->rawContent();
			HttpServer::$rspData = '';

			$response->header("Content-Type", "application/json; charset=utf-8");

			try {
				$yaf_request = new Yaf_Request_Http(HttpServer::$server['request_uri']
					, Yaf_Registry::get('config')['application']['baseUri']);
				$this->application->getDispatcher()->dispatch($yaf_request);
			} catch ( Exception $e ) {
				var_dump( $e );
			}

		  	$response->end(HttpServer::$rspData);

			if (HttpServer::$task) {
				$this->http->task(self::$taskData);
			}

		});

		$http->on('task',function(swoole_http_server $http, $task_id, $from_id, $data){
			HttpServer::$taskData = $data;
			$yaf_request = new Yaf_Request_Http(HttpServer::$server['request_uri'] . 'Task'
				, Yaf_Registry::get('config')['application']['baseUri']);
			$this->application->getDispatcher()->dispatch($yaf_request);
		});

		$http->on('finish',function(){

		});

		$http->start();
	}

	public function onWorkerStart() {
		posix_setuid(WORKER_UID);
		posix_setgid(WORKER_GID);
		define('APPLICATION_PATH', dirname(__DIR__));
		$this->application = new Yaf_Application( APPLICATION_PATH . 
					"/conf/application.ini");
		$this->application->bootstrap()->run();
	}

	public static function getInstance() {
		if (!self::$instance) {
            self::$instance = new HttpServer;
        }
        return self::$instance;
	}
}

HttpServer::getInstance();

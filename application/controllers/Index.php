<?php
/**
 * @name IndexController
 * @author pangbolike
 * @desc 默认控制器
 */
require_once(dirname(__FILE__).'/../library/config.php');

class IndexController extends Yaf_Controller_Abstract {

	public function init(){
        Yaf_Dispatcher::getInstance()->disableView();
    }

	//同步流程
	public function indexAction() {
		ServerModel::setRsp(array('ret' => 0));
		ServerModel::setTaskData(array('money' => 100));
	}

	//异步任务流程
	public function indexTaskAction() {
		PayModel::pay(ServerModel::getTaskData());
	}

}

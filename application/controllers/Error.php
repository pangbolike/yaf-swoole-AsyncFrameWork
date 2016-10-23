<?php
/**
 * @name ErrorController
 * @desc 错误控制器, 在发生未捕获的异常时刻被调用
 * @author pangbolike
 */
class ErrorController extends Yaf_Controller_Abstract {

	public function init(){
        Yaf_Dispatcher::getInstance()->disableView();
    }
	
	public function errorAction($exception) {
		UtilsModel::log_debug($exception->getMessage());
	}
}

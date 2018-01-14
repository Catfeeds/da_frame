<?php
/**
* 队列
* 任务计划执行，执行频率5分钟
*
 * @大商城 (c) 2014-2018 SHOPDA Inc. http://www.shopda.cn
 * @license    http://www.shopda.cn
 * @link       交流群号：387110194
 * @since      大商城荣誉出品
 */
namespace Crontab\Controller;
use Crontab\Controller\BaseCronController;
use Common\Lib\Language;
use Common\Lib\Log;
use Common\Lib\Model;


class QueueController extends BaseCronController {

    public function __construct() {
		parent::init_view();
		ini_set('default_socket_timeout', -1);
	}

    public function index() {
        if (!C('queue.open')) return;
        $timer = microtime(TRUE);
        $logic_queue = Logic('queue');
        $model = Model();
        $worker = new QueueServer();
        $queues = $worker->scan();
        while (true) {
            $content = $worker->pop($queues,$keeptimer ? $keeptimer : 290);
  //          echo ceil(microtime(TRUE)-$timer),PHP_EOL;ob_flush();
            if (is_array($content)) {
                $method = key($content);
                $arg = current($content);
                $result = $logic_queue->$method($arg);
                if (!$result['state']) {
                    $this->log($result['msg'],false);
                }
//				echo $method,PHP_EOL;ob_flush();
            }
			$keeptimer = 300 - intval(ceil(microtime(TRUE) - $timer)); 
//			echo var_dump($keeptimer),PHP_EOL;ob_flush();
//			echo 'real timer: '.ceil(microtime(TRUE) - $timer),PHP_EOL;ob_flush();
            if ($keeptimer <= 10) {
                break;
            }
        }
    }
}

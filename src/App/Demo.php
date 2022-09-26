<?php
namespace App\App;

use App\Util\HttpRequest;
use App\Service\ProductHandler;
use PHPUnit\Framework\TestCase;

class Demo extends TestCase{
    const URL = "http://some-api.com/user_info";
    private $_logger;
    private $_req;
    public $productObj;
    function __construct($logger, HttpRequest $req) {
        $this->_logger = $logger;
        $this->_req = $req;
        $this->productObj = new ProductHandler();
    }
    function set_req(HttpRequest $req) {
        $this->_req = $req;
    }
    function foo() {
        return "bar";
    }
    function get_user_info() {
        $result = $this->_req->get(self::URL);
        $result_arr = json_decode($result, true);
        if (in_array('error', $result_arr) && $result_arr['error'] == 0) {
            if (in_array('data', $result_arr)) {
                return $result_arr['data'];
            }
        } else {
            $this->_logger->error("fetch data error.");
        }
        return null;
    }
    //2.1 返回的json数据格式
    function get_user_info_result()
    {
        $result = ['error'=>0,'data'=>$this->productObj->getUserInfo()];
        print_r( json_encode($result));
    }
}

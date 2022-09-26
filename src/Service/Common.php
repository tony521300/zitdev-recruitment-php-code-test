<?php
namespace App\Service;

/**
 * 公用方法
 *
 *
 *
 */
class Common
{ 
    protected static $debug;
    protected static $open_status_arr = ['901' => 1, '902' => 2, '903' => 3];
    // backend状态为 909 915 916 时 解锁工作单 但不回调
    protected static $code_arr = ['909', '915', '916'];
    protected static $code_success= 900;
    /**
     * geo helper 地址转换为坐标
     * @param $address
     * @return bool|string
     */
    public function geoHelperAddress($address, $merchant_id = '')
    {

        try {
            $cackeKey = 'cache-address-'.$address;

            // 從獲取座標
            $userLocation = redisx()->get($cackeKey);
            if ($userLocation) {
                return $userLocation;
            }

            $key = 'time=' . time();

            // requestLog：寫日志
            requestLog('Backend', 'Thrift', 'Http', 'phpgeohelper\\Geocoding->convert_addresses', 'https://geo-helper-hostr.ks-it.co',  [[$address, $key]]);

            // getThriftService： 獲取 Thrift 服務
            $geoHelper = ServiceContainer::getThriftService('phpgeohelper\\Geocoding');
            $param = json_encode([[$address, $key]]);

            // 調用接口，以地址獲取座標
            $response = $geoHelper->convert_addresses($param);
            $response = json_decode($response, true);

            if ($response['error'] == 0) {
                responseLog('Backend', 'phpgeohelper\\Geocoding->hksf_addresses', 'https://geo-helper-hostr.ks-it.co', '200', '0',  $response);
                $data = $response['data'][0];
                $coordinate = $data['coordinate'];

                // 如果返回 '-999,-999'，表示調用接口失敗，那麼直接使用商家位置的座標
                if ($coordinate == '-999,-999') {
                    infoLog('geoHelper->hksf_addresses change failed === ' . $address);
                    if ($merchant_id) {
                        $sMerchant = new Merchant();
                        $res = $sMerchant->get_merchant_address($merchant_id);
                        $user_location = $res['latitude'] . ',' . $res['longitude'];
                        return $user_location;
                    }
                    infoLog('geoHelper->hksf_addresses change failed === merchant_id is null' . $merchant_id);
                    return false;
                }
                if (!isset($data['error']) && (strpos($coordinate,',') !== false)) {
                    $arr = explode(',', $coordinate);
                    $user_location = $arr[1] . ',' . $arr[0];

                    // set cache
                    redisx()->set($cackeKey, $user_location);
                    return $user_location;
                }
            }
            responseLog('Backend', 'phpgeohelper\\Geocoding->hksf_addresses', 'https://geo-helper-hostr.ks-it.co', '401', '401',  $response);
            return false;
        } catch (\Throwable $t) {
            criticalLog('geoHelperAddress critical ==' . $t->getMessage());
            return 0;
        }
    }
    //建议
    // 回调状态过滤
    public static function checkStatusCallback($order_id, $status)
    {
        // 是900 可以回调
        if ($status == 900) {
            return 1;
        }
        // backend状态为 909 915 916 时 解锁工作单 但不回调
        $code_arr = ['909', '915', '916'];
        if (in_array($status, $code_arr)) {
            infoLog('checkStatusCallback backend code is 909 915 916');
            return 0;
        }

        $open_status_arr = ['901' => 1, '902' => 2, '903' => 3];
        return $order_id.'-'.$open_status_arr[$status];
        //1返回数据可能遇到数组内不存在的代码 如801;
        //2 把正常返回的值放在最上面
        //3不存在的代码返回false

    }
    // 回调状态过滤
    public static function myCheckStatusCallback($order_id, $status)
    {
        if(!empty(self::$open_status_arr[$status])){
            return $order_id.'-'.self::$open_status_arr[$status];
        }
        // 是900 可以回调
        if ($status == self::$code_success) {
            return 1;
        }
        // backend状态为 909 915 916 时 解锁工作单 但不回调
        if (in_array($status, self::$code_arr)) {
            infoLog('checkStatusCallback backend code is 909 915 916');
            return 0;
        }
        return false;
    }
}

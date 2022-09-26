<?php

namespace App\Service;
use PHPUnit\Framework\TestCase;
class ProductHandler extends TestCase
{
    private $products = [
        [
            'id' => 1,
            'name' => 'Coca-cola',
            'type' => 'Drinks',
            'price' => 10,
            'create_at' => '2021-04-20 10:00:00',
        ],
        [
            'id' => 2,
            'name' => 'Persi',
            'type' => 'Drinks',
            'price' => 5,
            'create_at' => '2021-04-21 09:00:00',
        ],
        [
            'id' => 3,
            'name' => 'Ham Sandwich',
            'type' => 'Sandwich',
            'price' => 45,
            'create_at' => '2021-04-20 19:00:00',
        ],
        [
            'id' => 4,
            'name' => 'Cup cake',
            'type' => 'Dessert',
            'price' => 35,
            'create_at' => '2021-04-18 08:45:00',
        ],
        [
            'id' => 5,
            'name' => 'New York Cheese Cake',
            'type' => 'Dessert',
            'price' => 40,
            'create_at' => '2021-04-19 14:38:00',
        ],
        [
            'id' => 6,
            'name' => 'Lemon Tea',
            'type' => 'Drinks',
            'price' => 8,
            'create_at' => '2021-04-04 19:23:00',
        ],
    ];

    //題目 1.1計算商品總 金額。
    public function getTotalPrice()
    {
        $this->assertEquals(143, array_sum(array_column($this->products, 'price','id')));
    }
    //題目 1.2編寫一個函數，把商品以金額排序（由大至小），並 篩選商品類種是 “dessert” 的商品
    public function arrange()
    {
        // 先取出要排序的字段的值
        $priceSort = array_column($this->products, 'price');
        // 按照sort字段 SORT_DESC降序
        array_multisort($dessertProducts, SORT_DESC, $priceSort);
        // 读取出 Dessert的商品
        foreach ($dessertProducts as $pKey => &$product) {
            if($product['type'] != 'Dessert'){
                unset($dessertProducts[$pKey]);
            }
        }
        return $dessertProducts;
    }

    //題目 1.3編寫一個函數，把創建日期轉換為 unix timestamp。
    public function dateToTimestamp()
    {
        foreach ($this->products as &$product) {
            $product['create_at'] = strtotime($product['create_at']);
        }
    }

    public function getUserInfo()
    {
        return $this->products;
    }
}

<?php
// 应用公共文件
use think\Config;
/**
 * @param $route
 * @param array $params
 * @param array $order
 * @param $field
 * @return $url
 */
function urlOrder($route,$params=[],$order=[],$field)
{
    //设置
    Config::set('url_common_param',true);
    //接收排序参数
    $params['order[field]'] = $field;
    if(empty($order))
    {
        $order['field'] = '';
        $order['type'] = '';
    }
    $params['order[type]'] = ($order['field']==$field&&$order['type']=='asc')?'desc':'asc';
    return url($route,$params);
}

function classOrder($order=[],$field = null)
{
    if(!isset($order['field'])||!isset($order['type'])) return '';
    if($field == $order['field'])
    {
        $type = ($order['type']=='asc')?'desc':'asc';
        return $type;
    }else{
        return '';
    }
}

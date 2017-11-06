<?php

namespace app\back\validate;
use think\Validate;

class BrandValidate extends Validate
{
    protected $rule = [
        'id'        =>  'token|integer',
        'title'     =>  'token|require|max:32|unique:brand,title',
        'site'      =>  'url|max:255',
        'sort'      =>  'integer'
    ];

    protected $field = [
        'title'     =>  '品牌',
        'site'      =>  '网站',
        'sort'      =>  '排序'
    ];
}
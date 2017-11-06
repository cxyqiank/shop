<?php

namespace app\back\validate;
use think\Validate;

class RoleValidate extends Validate
{
    protected $rule = [ "id" => "token|integer",'title'     =>  'token|max:32','description'     =>  'token|max:255',];

    protected $field = ['id'     =>  'ID','title'     =>  '角色','description'     =>  '描述','is_super'     =>  '是否为超管','sort'     =>  '排序','create_time'     =>  '创建时间','update_time'     =>  '修改时间',];
}
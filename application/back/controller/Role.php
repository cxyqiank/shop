<?php
namespace app\back\controller;

use \app\back\model\Role as RoleModel;
use app\back\validate\RoleValidate;
use think\Request;
use think\Session;

class Role extends Base
{
    //展示页面
    public function index()
    {
        //空查询构造器
        $search = RoleModel::where('');
        $filter = [];
        $filter_title = input('filter_title','');
if(""!== $filter_title)
{
$search->where('title','like','%'.$filter_title.'%');
$filter['filter_title'] = $filter_title;
}
$filter_description = input('filter_description','');
if(""!== $filter_description)
{
$search->where('description','like','%'.$filter_description.'%');
$filter['filter_description'] = $filter_description;
}
$filter_is_super = input('filter_is_super','');
if(""!== $filter_is_super)
{
$search->where('is_super','like','%'.$filter_is_super.'%');
$filter['filter_is_super'] = $filter_is_super;
}

        //排序
        $order = input('order/a',[]);
        if(!empty($order)) $search->order($order['field'],$order['type']);
        //分页
        $data = $search->paginate(3);
        $start = ($data->currentPage()-1)*($data->listRows())+1;
        $end = min(($data->currentPage())*($data->listRows()),$data->total());
        //分配数据
        $this->assign(compact('data','start','end','filter','order'));
        return $this->fetch();
    }
    //添加更新页面
    public function set(Request $request)
    {
        if($request->isGet()){
            $message = Session::get('message')?Session::get('message'):[];
            //判断是否为修改
            $id = $request->get('id')?:null;
            $data = (!is_null($id))?RoleModel::find($request->get('id')):(Session::get('data')?Session::get('data'):[]);
            $this->assign('id',$id);
            (!is_null($id))?$this->assign('addOrEdit','编辑'):$this->assign('addOrEdit','创建');
            $this->assign(['message'=>$message,'data'=>$data]);
            return $this->fetch();
        }
        elseif($request->isPost())
        {
            //验证数据
            $validate = new RoleValidate();
            $data = $request->post();
            if(!$validate->batch()->check($data))
            {
                $this->redirect('set',[],302,[
                    //返回错误信息
                    'message'   =>  $validate->getError(),
                    //返回数据
                    'data'      =>  $data
                ]);
            }
            //接收数据
            $model = new RoleModel();
            if(isset($data['id'])&&!is_null($data['id']))
            {
                $res = $model->allowField(true)->save($data,['id'=>$data['id']]);
            }
            else
            {
                $res = $model->allowField(true)->save($data);
            }
            if($res)
            {
                $this->redirect('index');
            }
            else
            {
                $this->redirect('set');
            }
        }
    }
    //删除页面
    public function del(Request $request)
    {
        $id = $request->post();
        RoleModel::destroy($id['selected']);
        $this->redirect('index');
    }
}
<?php
namespace app\back\controller;

use \app\back\model\Brand as BrandModel;
use app\back\validate\BrandValidate;
use think\Config;
use think\Request;
use think\Session;
use think\Validate;

class Brand extends Base
{
    //展示页面
    public function index()
    {
        //空查询构造器
        $brand = BrandModel::where('');
        $filter = [];
        //搜索条件
        $filter_title = input('filter_title','');
        if(""!== $filter_title)
        {
            $brand->where('title','like','%'.$filter_title.'%');
            $filter['filter_title'] = $filter_title;
        }
        $filter_site = input('filter_site','');
        if(""!== $filter_site)
        {
            $brand->where('site','like','%'.$filter_site.'%');
            $filter['filter_site'] = $filter_site;
        }
        //排序
        $order = input('order/a',[]);
        if(!empty($order)) $brand->order($order['field'],$order['type']);
        //分页
        $data = $brand->paginate(3);
        $start = ($data->currentPage()-1)*($data->listRows())+1;
        $end = min(($data->currentPage())*($data->listRows()),$data->total());
        //分配数据
        $this->assign(compact('data','start','end','filter','order'));
        return $this->fetch();
    }
    //添加页面
    public function set(Request $request)
    {
        if($request->isGet()){
            $message = Session::get('message')?Session::get('message'):[];
            //判断是否为修改
            $id = $request->get('id')?:null;
            $data = (!is_null($id))?BrandModel::find($request->get('id')):(Session::get('data')?Session::get('data'):[]);
            $this->assign('id',$id);
            (!is_null($id))?$this->assign('addOrEdit','编辑'):$this->assign('addOrEdit','创建');
            $this->assign(['message'=>$message,'data'=>$data]);
            return $this->fetch();
        }
        elseif($request->isPost())
        {
            //验证数据
            $brand_validate = new BrandValidate();
            $data = $request->post();
            if(!$brand_validate->batch()->check($data)){
                $this->redirect('set',[],302,[
                    'message'   =>  $brand_validate->getError(),
                    'data'      =>  $data
                ]);
            }
            //接收数据
            $brand = new BrandModel();
            if(isset($data['id'])&&!is_null($data['id']))
                $res = $brand->allowField(true)->save($data,['id'=>$data['id']]);
            else
                $res = $brand->allowField(true)->save($data);
            if($res)
                $this->redirect('/back/brand');
            else
                $this->redirect('set');
        }
    }
    //删除页面
    public function del(Request $request)
    {
        $id = $request->post();
        $validate = new BrandValidate();
        $res = $validate->check($id);
        if(!$res) {echo '你不能随便删的';die;}
        BrandModel::destroy($id['selected']);
        $this->redirect('/back/brand');
    }
    public function titleUniqueCheck()
    {
        // ajax请求默认会转为json，但是我们需要的是字符串，将默认响应类型改为html
        Config::set('default_ajax_return', 'html');

        # 校验是否唯一，利用验证器校验
        return Validate::unique(null,'brand,title',input(),'title')?'true':'false';
    }
}
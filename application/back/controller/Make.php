<?php
namespace app\back\controller;

use think\Config;
use think\Db;

class Make extends Base
{
    protected  $input = [];
    protected $searchNum = 0;
    public function table()
    {
        return $this->fetch();
    }
    //获取表信息
    public function info()
    {
        $table = input('table');
        #获取表信息
        $sql = "select table_comment from information_schema.tables where table_schema=? and table_name=?";
        $table_schema = Config::get('database.database');
        $table_name = Config::get('database.prefix'). $table;
        $rows_table = Db::query($sql, [$table_schema, $table_name]);

        # 获取表的字段
        $sql = 'select CHARACTER_MAXIMUM_LENGTH,column_name,column_type, column_comment from information_schema.columns where table_schema=? and table_name=?';
        $rows_field = Db::query($sql, [$table_schema, $table_name]);

        // 响应数据
        return [
            'table_schema' => $table_schema,
            'table' => $table,
            'table_name' => $table_name,
            'comment' => $rows_table[0]['table_comment'],
            'fields' => $rows_field,
        ];
    }

    public function generate()
    {
        if (! request()->isPost() ) {
            $this->redirect('table');
        }

        # 获取请求数据
        $this->input['table'] = input('table');
        $this->input['comment'] = input('comment');
        $this->input['fields'] = input('fields/a');

        # 生成控制器
        $this->controller();
        # 生成列表视图
        $this->indexView();
        # 生成设置视图
        $this->setView();
        # 生成模型
        $this->model();
        # 生成验证器
        $this->mkValidate();
        # 生成路由
        $this->route();

    }
    public function tableUp()
    {
        $table = explode('_',$this->input['table']);
        $arr = array_map('ucfirst',$table);
        return $table = implode('',$arr);
    }
    public function date()
    {
        $date['date'] = date('Y/m/d');
        $date['time'] = date('H:i');
        return $date;
    }
    //控制器生成
    public function controller()
    {
        $where_list = '';
        $tpl = file_get_contents(APP_PATH.'back/code/where_list.php');

        foreach($this->input['fields'] as $field)
        {   //判断是否设置搜索
            if(!isset($field['search'])) continue;
            $where_list.=str_replace('%field%',$field['name'],$tpl);
            $this->searchNum++;
        }
        $tpl = file_get_contents(APP_PATH.'back/code/controller.php');
        $search = ['%table%','%where_list%'];
        $table = $this->tableUp();
        $replace = [$table,$where_list];
        $content = str_replace($search,$replace,$tpl);
        $res = file_put_contents(APP_PATH.'back/controller/'.$table.'.php',$content);
        echo $this->input['comment'].'控制器生成'.(($res)?'成功':'失败').'<br>';
    }
    //列表页面生成
    public function indexView()
    {
        //替换搜索条件
        $indexSearch = '';
        $tpl = file_get_contents(APP_PATH.'back/code/indexSearch.html');
        foreach($this->input['fields'] as $field)
        {   //判断是否设置搜索
            if(!isset($field['search'])) continue;
            $search = ['%num%','%field%','%field_name%'];
            $replace = [$this->searchNum,$field['name'],$field['comment']];
            $indexSearch .= str_replace($search,$replace,$tpl);
        }

        //替换展示字段
        $indexField = '';
        //替换字段值
        $fieldVal = '';
        $tpl = file_get_contents(APP_PATH.'back/code/indexField.html');
        foreach($this->input['fields'] as $field)
        {   //判断是否设置排序
            if(isset($field['index']))
            {
                if(isset($field['order']))
                {
                    $search = ['%table%','%field%','%field_name%'];
                    $replace = [$this->input['table'],$field['name'],$field['comment']];
                    $indexField .= str_replace($search,$replace,$tpl);
                    $fieldVal .= '<td class="text-left">{$v["'.$field['name'].'"]}</td>';
                }else{
                    $indexField .= '<td class="text-left">'.$field['name'].'</td>';
                    $fieldVal .= '<td class="text-left">{$v["'.$field['name'].'"]}</td>';
                }
            }
        }
        //主模板替换
        $tpl = file_get_contents(APP_PATH.'back/code/index.html');
        $search = ['%name%','%table%','%indexSearch%','%indexField%','%fieldVal%'];
        $replace = [$this->input['comment'],$this->input['table'],$indexSearch,$indexField,$fieldVal];
        $content = str_replace($search,$replace,$tpl);
        $dir = APP_PATH.'back/view/'.$this->input['table'].'/';
        if(!is_dir($dir)){mkdir($dir,0777,true);}
        $res = file_put_contents($dir.'index.html',$content);
        echo $this->input['comment'].'列表页生成'.(($res)?'成功':'失败').'<br>';
    }
    //设置页面生成/必须/设置/长度
    public function setView()
    {
        //显示可设置字段
        $setField = '';
        $rules = '';
        $messages = '';
        $tpl = file_get_contents(APP_PATH.'back/code/setField.html');
        //循环增加设置字段
        foreach($this->input['fields'] as $field) {
            if (isset($field['set'])) {
                $search = ['%field%', '%comment%'];
                $replace = [$field['name'], $field['comment']];
                $setField .= str_replace($search, $replace, $tpl);
            }
            if (isset($field['require'])) {
                $rules = $messages = $field['name'] . ':{';
                if (isset($field['maxLength']) && $field['maxLength'] != '') {
                    $rules .= "required:true,maxlength:" . $field['maxLength'] . "'";
                    $messages .= "required:'$field[comment]不能为空',maxlength:'$field[comment]不能超过$field[maxLength]'";
                } else {
                    $rules .= "required:true,";
                    $messages .= "required:$field[comment]不能为空,";
                }
                $rules .= '},';
                $messages .= '},';
            }
            elseif(isset($field['maxLength']) && $field['maxLength'] != '')
            {
                $rules = $messages = $field['name'] . ':{';
                $rules .= "required:true,maxlength:" . $field['maxLength'] . "',";
                $messages .= "required:'$field[comment]不能为空',maxlength:'$field[comment]不能超过$field[maxLength]'";
                $rules .= '},';
                $messages .= '},';
            }
        }
        //替换主模板
        $content = '';
        $tpl = file_get_contents(APP_PATH.'back/code/set.html');
        $search = ['%comment%','%setField%','%rules%','%messages%'];
        $replace = [$this->input['comment'],$setField,$rules,$messages];
        $content = str_replace($search,$replace,$tpl);
        $dir = APP_PATH.'back/view/'.$this->input['table'].'/';
        if(!is_dir($dir)){mkdir($dir,0777,true);}
        $res = file_put_contents($dir.'set.html',$content);
        echo $this->input['comment'].'设置页生成'.(($res)?'成功':'失败').'<br>';

    }
    //路由生成
    public function route()
    {
        $tpl =file_get_contents(APP_PATH.'back/code/route.php');
        $search = ['%name%','%table%'];
        $replace = [$this->input['comment'],$this->input['table']];
        $content = str_replace($search,$replace,$tpl);
        $fileOrigin = file_get_contents(APP_PATH.'route.php');

        $res = file_put_contents(APP_PATH.'route.php',$fileOrigin.'
        '.$content);
        echo $this->input['comment'].'路由生成'.(($res)?'成功':'失败').'<br>';
    }
    //模型生成
    public function model()
    {
        $date = $this->date();
        $tpl = file_get_contents(APP_PATH.'back/code/model.php');
        $search = ['%name%','%date%','%time%'];
        $replace = [$this->tableUp(),$date['date'],$date['time']];
        $content = str_replace($search,$replace,$tpl);
        $dir = APP_PATH.'back/model/';
        if(!is_dir($dir)){mkdir($dir,0777,true);}
        $res = file_put_contents($dir.$this->tableUp().'.php',$content);
        echo $this->input['comment'].'ORM模型生成'.(($res)?'成功':'失败').'<br>';
    }
    //验证器生成
    public function mkValidate()
    {
        $rule = 'protected $rule = [ "id" => "token|integer",';
        $fields = 'protected $field = [';
        foreach($this->input['fields'] as $field)
        {
            if(isset($field['require']))
            {
                $rule .= "'".$field['name']."'     =>  'token|require";
                if(isset($field['maxLength'])&&$field['maxLength']!='')
                {
                    $rule .= "|max:".$field['maxLength']."',";
                }
                else
                {
                    $rule .="',";
                }
            }
            else if(isset($field['maxLength'])&&$field['maxLength']!=''){
                $rule .="'".$field['name']."'     =>  'token|max:".$field['maxLength']."',";
            }
            $fields .="'".$field['name']."'     =>  '".$field['comment']."',";
        }
        $rule .= '];';
        $fields .= '];';

        $tpl = file_get_contents(APP_PATH.'back/code/validate.php');
        $search = ['%table%','%rule%','%field%'];
        $replace = [$this->tableUp(),$rule,$fields];
        $content = str_replace($search,$replace,$tpl);
        $res = file_put_contents(APP_PATH.'back/validate/'.$this->tableUp().'Validate.php',$content);
        echo $this->input['comment'].'验证器生成'.(($res)?'成功':'失败').'<br>';
    }

}
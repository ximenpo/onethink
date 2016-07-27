<?php
namespace Admin\Controller;
use Think\Page;

/**
* 后台文档列表控制器基类(根据分类进行处理)
* @author ximenpo <ximenpo@jiandan.ren>
*/
class DocumentListController extends AdminController {

    //  某分类下的指定模型数据列表
    public  function    index($cate_id, $model_id, $position = null, $group_id=null){
        $errmsg = $this->loadDocumentList($cate_id, $model_id, $position, $group_id);
        if(!empty($errmsg)){
            $this->error($errmsg);
        };

        $this->meta_title   = get_category_title($cate_id);
        $this->assign('file_tool_buttons', 'inc_index_tool_buttons');
        $this->display();
    }

    //  查看详细信息
    public  function    detail(){
        $id     =   I('get.id','');
        $errmsg = $this->loadDocumentDetail($id);

        if(!empty($errmsg)){
            $this->error($errmsg);
        };

        $this->meta_title   = '查看'.($this->get('model.title'));
        $this->display();
    }

    //  编辑
    public  function    edit(){
        $id     =   I('get.id','');
        $errmsg = $this->loadDocumentDetail($id);

        if(!empty($errmsg)){
            $this->error($errmsg);
        };

        $this->meta_title   = '编辑'.($this->get('model.title'));
        $this->display();
    }

    //  更新
    public  function    update(){
        $document   =   D('Document');
        $res = $document->update();
        if(!$res){
            $this->error($document->getError());
        }else{
            $this->success($res['id']?'更新成功':'新增成功', Cookie('__forward__'));
        }
    }

    //  设置状态
    public function setStatus($model='Document'){
        return parent::setStatus('Document');
    }

    /**
    * 加载分类文档数据（仅支持一个类别下的一个模型）
    * @param integer $cate_id 分类id
    * @param integer $model_id 模型id
    * @param integer $position 推荐标志
    * @param integer $group_id 分组id
    */
    protected function loadDocumentList($cate_id, $model_id, $position = null, $group_id=null){

        //  Exported data:
        //      category    类别信息
        //      model       模型信息
        //      groups      分组信息
        //      category_id 类别ID
        //      model_id    模型ID
        //      group_id    分组ID
        //      position    推荐位ID
        //      pid         当前为子文档列表时的父类别
        //      list        列表数据
        //      list_grids  列表规则
        //      article     为子文档列表时的上层文档数据（id,title,type）

        if(!is_int($cate_id) || !is_int($model_id)){
            return  '参数错误';
        }

        $cate   = get_category($cate_id);
        $model  = get_document_model($model_id);
        if(empty($cate) || empty($model)){
            return  '分类或模型信息不存在';
        }

        // 获取分组定义
        $groups		=	$cate['groups'];
        if($groups){
            $groups	=	parse_field_attr($groups);
        }

        //解析列表规则
        $fields =	array();
        $grids  =	preg_split('/[;\r\n]+/s', trim($model['list_grid']));
        foreach ($grids as &$value) {
            // 字段:标题:链接
            $val      = explode(':', $value);
            // 支持多个字段显示
            $field   = explode(',', $val[0]);
            $value    = array('field' => $field, 'title' => $val[1]);
            if(isset($val[2])){
                // 链接信息
                $value['href']  =   $val[2];
                // 搜索链接信息中的字段信息
                preg_replace_callback('/\[([a-z_]+)\]/', function($match) use(&$fields){$fields[]=$match[1];}, $value['href']);
            }
            if(strpos($val[1],'|')){
                // 显示格式定义
                list($value['title'],$value['format'])    =   explode('|',$val[1]);
            }
            foreach($field as $val){
                $array  =   explode('|',$val);
                $fields[] = $array[0];
            }
        }

        // 文档模型列表始终要获取的数据字段 用于其他用途
        $fields[] = 'category_id';
        $fields[] = 'model_id';
        $fields[] = 'pid';
        // 过滤重复字段信息
        $fields =   array_unique($fields);
        // 列表查询
        $list   =   $this->getDocumentList($cate_id,$model_id,$position,$fields,$group_id);
        // 列表显示处理
        $list   =   $this->parseDocumentList($list,$model_id);

        $this->assign('category',       $cate);
        $this->assign('model',          $model);
        $this->assign('groups',         $groups);
        $this->assign('category_id',    $cate_id);
        $this->assign('model_id',       $model_id);
        $this->assign('group_id',       $group_id);
        $this->assign('position',       $position);
        $this->assign('pid',            $pid);
        $this->assign('list',           $list);
        $this->assign('list_grids',     $grids);

        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
    }

    protected function loadDocumentDetail($id){

        //  Exported data:
        //      article     为子文档时的上层文档数据（id,title,type）
        //      data        文档数据
        //      model_id    模型ID
        //      model       模型数据
        //      fields      字段
        //      groups      分组信息
        //      type_list   类型列表

        if(empty($id)){
            return  '参数不能为空！';
        }

        // 获取详细数据
        $Document = D('Document');
        $data = $Document->detail($id);
        if(!$data){
            return  $Document->getError();
        }

        if($data['pid']){
            // 获取上级文档
            $article        =   $Document->field('id,title,type')->find($data['pid']);
            $this->assign('article',$article);
        }
        // 获取当前的模型信息
        $model    =   get_document_model($data['model_id']);

        $this->assign('data', $data);
        $this->assign('model_id', $data['model_id']);
        $this->assign('model',      $model);

        //获取表单字段排序
        $fields = get_model_attribute($model['id']);
        $this->assign('fields',     $fields);

        $cate_id    = intval($data['category_id']);
        if($cate_id != 0){
            $groups		=	get_category($cate_id, 'groups');
            if($groups){
                $groups	=	parse_field_attr($groups);
                $this->assign('groups', $groups);
            }
        }

        //获取当前分类的文档类型
        $this->assign('type_list', get_type_bycate($data['category_id']));

        //$this->meta_title   =   '编辑文档';
        //$this->display();
    }

    /**
    * 默认文档列表方法
    * @param integer $cate_id 分类id
    * @param integer $model_id 模型id
    * @param integer $position 推荐标志
    * @param mixed $field 字段列表
    * @param integer $group_id 分组id
    */
    private function getDocumentList($cate_id=0,$model_id=null,$position=null,$field=true,$group_id=null){
        /* 查询条件初始化 */
        $map = array();
        if(isset($_GET['title'])){
            $map['title']  = array('like', '%'.(string)I('title').'%');
        }
        if(isset($_GET['status'])){
            $map['status'] = I('status');
            $status = $map['status'];
        }else{
            $status = null;
            $map['status'] = array('in', '0,1,2');
        }
        if ( isset($_GET['time-start']) ) {
            $map['update_time'][] = array('egt',strtotime(I('time-start')));
        }
        if ( isset($_GET['time-end']) ) {
            $map['update_time'][] = array('elt',24*60*60 + strtotime(I('time-end')));
        }
        if ( isset($_GET['nickname']) ) {
            $map['uid'] = M('Member')->where(array('nickname'=>I('nickname')))->getField('uid');
        }

        // 构建列表数据
        $Document = M('Document');

        if($cate_id){
            $map['category_id'] =   $cate_id;
        }
        $map['pid']         =   I('pid',0);
        if($map['pid']){ // 子文档列表忽略分类
            unset($map['category_id']);
        }
        $Document->alias('DOCUMENT');
        if(!is_null($model_id)){
            $map['model_id']    =   $model_id;
            if(is_array($field) && array_diff($Document->getDbFields(),$field)){
                $modelName  =   M('Model')->getFieldById($model_id,'name');
                $Document->join('__DOCUMENT_'.strtoupper($modelName).'__ '.$modelName.' ON DOCUMENT.id='.$modelName.'.id');
                $key = array_search('id',$field);
                if(false  !== $key){
                    unset($field[$key]);
                    $field[] = 'DOCUMENT.id';
                }
            }
        }
        if(!is_null($position)){
            $map[] = "position & {$position} = {$position}";
        }
        if(!is_null($group_id)){
            $map['group_id']	=	$group_id;
        }
        //指定分页每页记录数
        if(!isset($_REQUEST['r'])){
            $_REQUEST['r']  = get_category($cate_id, 'list_row');
        }
        $list = $this->lists($Document,$map,'level DESC,DOCUMENT.id DESC',$field);

        if($map['pid']){
            // 获取上级文档
            $article    =   $Document->field('id,title,type')->find($map['pid']);
            $this->assign('article',$article);
        }
        //检查该分类是否允许发布内容
        $allow_publish  =   get_category($cate_id, 'allow_publish');

        //$this->assign('status', $status);
        //$this->assign('allow',  $allow_publish);
        //$this->assign('pid',    $map['pid']);

        //$this->meta_title = '文档列表';
        return $list;
    }
}

<?php

namespace backend\components;

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\widgets\LinkPager;

use yii\helpers\ArrayHelper;

use Yii;
use yii\helpers\Url;
use yii\widgets\BaseListView;

class ResourceList extends BaseListView
{
    private $sort;
    public $search = 'ResourceSearch';
    public function init()
    {
        parent::init();
        $view = $this->getView();

        // GridViewAsset::register($view);
        // $id = $this->options['id'];
        // $options = Json::htmlEncode(array_merge($this->getClientOptions(), ['filterOnFocusOut' => $this->filterOnFocusOut]));


        $url = Url::current(['t' => '0', $this->search => null]);
        $view->registerJs(
            "
            function Search(self){
                window.location.href='$url&'+encodeURI('$this->search[name]')+'='+$('#search').val();
            }
            ",
            \yii\web\View::POS_BEGIN
        );

        $view->registerJs('
            function OpenModal(id, self){
                $("#model_id").val(id);
                $("#model_name").val(self.value);
                $("#modal-default").modal();
            }
            ',  \yii\web\View::POS_BEGIN);
    }
    private function renderModal()
    {
        ob_start();
?>
        <div class="modal fade" id="modal-default">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">修改信息</h4>
                    </div>
                    <div class="modal-body">



                        <?php ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>
                        <div class="box-body">
                            <div class="form-group">

                                <label class="col-sm-2 control-label">资源名称</label>

                                <div class="col-sm-10">
                                    <input type="hidden" id="model_id" name="id" value="1">
                                    <input name="name" class="form-control" placeholder="名称" id="model_name" value="">
                                </div>
                            </div>


                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" class="btn btn-success pull-right">提交修改</button>
                        </div>
                        <!-- /.box-footer -->

                        <?php ActiveForm::end() ?>


                    </div>

                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
<?php

        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
    private function renderLeger()
    {
        return '<div class="box box-info"><div class="box-body">';
    }
    private function renderFoolter()
    {


        return "</div> <!-- /.box-body --></div>";
    }
    public function renderSection($name)
    {

        switch ($name) {
            case '{header}':
                return $this->renderHeader();
            case '{modal}':
                return $this->renderModal();
            case '{leger}':
                return $this->renderLeger();
            case '{footer}':
                return $this->renderFoolter();
            default:
                return parent::renderSection($name);
        }
    }
    private function header()
    {
        return
            <<<EOF
        <div class="resource-index">
            <hr>
            <div class="box box-info">
                <div class="box-body">
                    <!-- Date and time range -->
                    <div class="btn-group">
                        <a type="button" class="btn {time-class} btn-sm" href="{time-href}">
                            <span>
                                <i class="fa fa-clock-o"></i> 时间排序
                            </span>
                            <i class="fa {time-caret}"></i>
                        </a>
                
                        <a type="button" class="btn {name-class} btn-sm" href="{name-href}">
                            <span>
                                <i class="fa fa-terminal"></i> 名称排序
                            </span>
                            <i class="fa {name-caret}"></i>
                        </a>
                    </div>
                    <!-- /.form group -->


                    <div class="input-group input-group-sm  col-lg-2  col-sm-4  col-xs-6 pull-right">
                        
                        <!-- /btn-group -->
                        <input type="text" id="search" class="form-control"  value="{search-value}">  <div class="input-group-btn ">
                            <a type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" onclick="Search(this)">搜索
                                <span class="fa fa-search"></span>
                            </a>
                        </div>
                    </div>


                
                </div>
                <!-- /.box-body -->
            </div>
EOF;
    }
    public function renderSomething($name)
    {

        switch ($name) {
            case '{time-class}':
                return strpos($this->sort, 'created_at') !== false ? 'btn-info' : 'btn-default';
            case '{time-href}':
                return Url::current(['sort' => $this->sort == 'created_at' ? '-created_at' : 'created_at']);
            case '{time-caret}':
                return $this->sort == '-created_at' ? 'fa-caret-down' : 'fa-caret-up';
            case '{name-class}':
                return strpos($this->sort, 'name') !== false ? 'btn-info' : 'btn-default';
            case '{name-href}':
                return Url::current(['sort' => $this->sort == 'name' ? '-name' : 'name']);
            case '{name-caret}':
                return $this->sort == '-name' ? 'fa-caret-down' : 'fa-caret-up';
            case '{search-value}':
                return isset(Yii::$app->request->queryParams[$this->search]['name']) ? Html::encode(Yii::$app->request->queryParams[$this->search]['name']) : '';
            default:
                return false;
        }
    }
    public function renderHeader()
    {

        $header = $this->header();
        $content = preg_replace_callback('/{[\\w-]+}/', function ($matches) {
            $content = $this->renderSomething($matches[0]);
            return $content === false ? $matches[0] : $content;
        }, $header);

        return $content;
    }
    public function run()
    {
        $this->sort = isset(Yii::$app->request->queryParams['sort']) ? Yii::$app->request->queryParams['sort'] : '-created_at';
        $this->layout = "{header}\n{items}\n{leger}\n{summary}\n{pager}\n{footer}\n{modal}";
        $this->summaryOptions = ['class' => 'pull-left'];
        parent::run();
    }

    /**
     * Renders the pager.
     * @return string the rendering result
     */
    public function renderPager()
    {
        $pagination = $this->dataProvider->getPagination();
        if ($pagination === false || $this->dataProvider->getCount() <= 0) {
            return '';
        }
        /* @var $class LinkPager */
        $pager = $this->pager;
        $class = ArrayHelper::remove($pager, 'class', LinkPager::className());
        $pager['pagination'] = $pagination;
        $pager['view'] = $this->getView();
        $pager['options'] = ['class' => 'pagination pagination-sm pull-right'];
        $result = $class::widget($pager);
        if ($result) {
            return $result;
        }
        return '';
        //return $class::widget($pager);
    }
    private function renderItem($item)
    {
        $ready = null != $item->image;
        $cogs = Url::toRoute(['@web/public/image/cogs.jpg']);
        $html = $ready ?
            <<<IF_READY
        <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
            <div class="box box-info box-solid">
                <div class="box-header with-border">

                    <div class="box-tools pull-right">
                        <button value = "{item-name}" onclick='OpenModal({item-id},  this)' type="button" class="btn  btn-box-tool">
                            <i class="fa fa-pen"></i>
                        </button>
                        {item-delete}
                    </div>
                    <span class="info-box-text"> {item-name}</span>
                    <!-- /.box-tools -->
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <img src=" {item-image}" alt="..." class="img-thumbnail center-block">
                    <b class="pull-left info-box-text">
                    {item-created_at}
                    </b>
                    <hr>
                    <a type="button" href="{item-url}" class="btn btn-block btn-info  btn-flat">进入查看</a>

                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->

IF_READY :
            <<<ELSE_READY
            <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
                <div class="box box-warning box-solid">
                    <div class="box-header with-border">
                    
                    <div class="box-tools pull-right">
                        {item-delete}
                    </div>
                    <span class="info-box-text"> {item-name}</span>
                        <!-- /.box-tools -->
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">

                        <div class="box box-danger">
                            <div class="box-header">
                                <h3 class="box-title">等待处理</h3>
                            </div>
                            <div class="box-body">
                                处理后开始使用
                            </div>
                            <!-- /.box-body -->
                            <!-- Loading (remove the following to stop the loading)-->
                            <div class="overlay">
                                <i class="fa fa-refresh fa-spin"></i>
                            </div>
                            <!-- end loading -->
                        </div>
                        <!-- /.box -->
                
                   
                        <a type="button" href="{item-url}" class="btn btn-block btn-warning  btn-flat"> 开始预处理</a>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
ELSE_READY;

        $content = preg_replace_callback('/{[\\w-]+}/', function ($matches) use ($item) {
            $content = $this->replateItem($item, $matches[0]);
            return $content === false ? $matches[0] : $content;
        }, $html);
        return $content;
    }
    private function replateItem($item, $name)
    {
        switch ($name) {
            case '{item-url}':
                return Url::toRoute(['view', 'id' => $item->id]);
            case '{item-image}':
                return $item->image->url;
            case '{item-id}':
                return $item->id;
            case '{item-name}':
                return $item->name;
            case '{item-created_at}':
                return $item->created_at;
            case '{item-delete}':
                return Html::a('<i class="fa fa-times"></i>', ['delete', 'id' => $item->id, 'back'=>true], [
                    'class' => 'btn btn-box-tool',
                    'data' => [
                        'confirm' => '确认删除此资源?',
                        'method' => 'post',
                    ],
                ]);
            default:
                return false;
        }
    }
    public function renderItems()
    {
        $content = '<div class="row">';
        $models = $this->dataProvider->getModels();
        foreach ($models as $item) {
            $content .= $this->renderItem($item);
        }
        $content .= '</div>';
        return $content;
    }
}

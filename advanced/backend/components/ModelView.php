<?php
namespace backend\components;

use yii\base\Widget;
use yii\helpers\Html;
use yii\widgets\LinkPager;

use yii\helpers\ArrayHelper;

use Closure;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\i18n\Formatter;
use yii\widgets\BaseListView;
use yii\grid\GridViewAsset;

class ModelView extends BaseListView
{

    public function init()
    {
        parent::init();
     
    }
 
    public function run()
    {

    //public $layout = "{summary}\n{items}\n{pager}";
        $this->layout = "{summary}{pager}";
        $this->summaryOptions =['class' => 'pull-left'];
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


        return $class::widget($pager);
    }
    public function renderItems()
    {
        return "";
    }
}
<?php
/**
 * Author: Imam Omar Mochtar <iomarmochtar@gmail.com>
 * Date: 08/04/17
 * Reuse ajax-curd untuk menambahkan data secara async pada (sementara ini) komponen select. reload via pjax
 */

namespace common\ext\omarov;

use yii\web\AssetBundle;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use kartik\icons\Icon;
use yii\helpers\Html;

/**
 * HOW TO Use
 * ----------
 * (Pada contoh ini menggunakan select2-nya kartik)
 * - Include library Pjax dan helper URL pada view yang akan menggunakan ini
 * use yii\helpers\Url;
 * use yii\widgets\Pjax;
 * use common\utils\AjxReloader;
 *
 * - Register asset2 yang diperlukan
 *
 * AjxReloader::register($this);
 *
 * - Pada bagian addon untuk argument form set seperti berikut ini beserta dengan url dari create action dari controller yang pakai ajax-crud.
 *   Url yang dibuat menyertaka dengan target pjax ID yang akan direload
 *     $addon = [
        'append' => [
            'content' => Html::button(Icon::show('plus'), [
            'class' => 'btn btn-primary',
            'title' => 'Add to this list',
            'data-toggle' => 'tooltip',
            'href'=>Url::to(['/product/product-type/create', 'tgtjax'=>'pjax-product-type']),
            'role'=>'modal-remote'
        ]),
        'asButton' => true
        ]
];
 *
 *  note: atau juga bisa menggunakan helper yang sudah dibuat jika menggunakan widget select2
 *  'addon'=>AjxReloader::genSelect2Args('/product/product-type/create', 'pjax-product-type'),

- Kelilingi form field dengan Pjax dan set unique ID untuknya
<?php  Pjax::begin(['id'=>'pjax-product-type']) ?>

<?= $form->field($model,'product_type_id')->widget(Select2::classname(),[
'data'=>ProductType::getCDataMap(),
'options'=>['placeholder'=>'Pilih ...'],
'pluginOptions' => ['allowClear' => true],
'addon'=>$addon,
])->label('Product Category'); ?>

<?php Pjax::end() ?>

- Pada bagian action pada controller ajax-crud tambahan argument $tgtjax dan pada bagian forceReload juga
public function actionCreate($tgtjax=null)
'forceReload'=> $tgtjax ? '#'.$tgtjax : '#crud-datatable-pjax',
 *
 * - Untuk mengintegrasikan dengan gridview maka pakai gridview punya kartik kartik\grid\GridView dan agar pada update,view,delete colum action di set menjadi seperti ini
 *   sesuaikan urlCreator dengan url pada controller dan method ajax provider-nya
[
'class' => 'kartik\grid\ActionColumn',
'dropdown' => false,
'vAlign'=>'middle',
'urlCreator' => function($action, $model, $key, $index) {
$action = '/bom/material/'.$action;
return Url::to([$action,'id'=>$key]);
},
'viewOptions'=>['role'=>'modal-remote','title'=>'View','data-toggle'=>'tooltip'],
'updateOptions'=>['role'=>'modal-remote','title'=>'Update', 'data-toggle'=>'tooltip'],
'deleteOptions'=>[
'role'=>'modal-remote','title'=>'Delete',
'data-toggle'=>'tooltip',
'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
'data-request-method'=>'post',
'data-confirm-title'=>'Are you sure?',
'data-confirm-message'=>'Kacangitem: Are you sure want to delete this item'
]
]
 *
 *
 *
 * Class AjxReloader
 * @package common\utils
 */
class AjxReloader extends  AssetBundle
{
    public $depends = [
        'johnitvn\ajaxcrud\CrudAsset'
    ];

    public static function register($view, $modal_attrs=[])
    {
        $modal_attrs = array_merge($modal_attrs, [
            "id"=>"ajaxCrudModal",
            "footer"=>"",
        ]);
        Modal::begin($modal_attrs);
        Modal::end();
        return parent::register($view);
    }


    public static function genSelect2Args($url, $tgtjax, $label='', $icon='plus', $btn_cls='btn btn-primary', $btn_title='Add to this list'){
        return [
            'append' => [
                'content' => Html::button(Icon::show($icon).' '.$label, [
                    'class' => $btn_cls,
                    'title' =>  $btn_title,
                    'data-toggle' => 'tooltip',
                    'href'=>Url::to([$url, 'tgtjax'=>$tgtjax]),
                    'role'=>'modal-remote'
                ]),
                'asButton' => true
            ]
        ];
    }

    /**
     * Generate kartik action column untuk gridview yg menggunakan pjax, argument urlCreator untuk generate url secara dinamis. contoh urlCreator
     *    'urlCreator' => function($action, $model, $key, $index) {
    $action = '/bom/material/'.$action;
    return Url::to([$action,'id'=>$key]);
    },
     * @param array $urlCreator
     * @return array
     */
    public static function genkartikActionColumn($urlCreator=[], $template=null){
        $args  = [
            'class' => 'kartik\grid\ActionColumn',
            'dropdown' => false,
            'vAlign'=>'middle',
            'viewOptions'=>['role'=>'modal-remote','title'=>'View','data-toggle'=>'tooltip'],
            'updateOptions'=>['role'=>'modal-remote','title'=>'Update', 'data-toggle'=>'tooltip'],
            'deleteOptions'=>['role'=>'modal-remote','title'=>'Delete',
                'data-toggle'=>'tooltip',
                'data-confirm'=>false, 'data-method'=>false,// for overide yii data api
                'data-request-method'=>'post',
                'data-confirm-title'=>'Are you sure?',
                'data-confirm-message'=>'Are you sure want to delete this item'
            ],
        ];

        if ($template)
            $args['template'] = $template;

        if (!empty($urlCreator))
            $args['urlCreator'] = $urlCreator;

        return $args;
    }
}

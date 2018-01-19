<?php

namespace common\ext\omarov;

use Yii;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;


/**
 * wrapper controller untuk mempermudah editor dialog via ajax terutama untuk master data
**/
class AjxController extends Controller
{
    public $name = 'Default';

    protected function getModel(){
        return null;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'bulk-delete' => ['post'],
                ],
            ],
        ];
    }


    /**
     * Lists all models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = $this->getModel();
        $dataProvider = new ActiveDataProvider([
            'query' => $model::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $cls = $this->getModel();
        if (($model = $cls::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Displays a single  model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        return [
            'title'=> $this->name.": ".$model->name,
            'content'=>$this->renderAjax('view', [
                'model' => $model,
            ]),
            'footer'=> Html::button('Tutup',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                Html::a('Edit',['update','id'=>$id],['class'=>'btn btn-primary','role'=>'modal-remote'])
        ];
    }

    /**
     * Creates a new model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($tgtjax=null)
    {
        $request = Yii::$app->request;
        $cls = $this->getModel();
        $model = new $cls();

        $title = 'Menambahkan '.$this->name.' baru';
        Yii::$app->response->format = Response::FORMAT_JSON;

        if($request->isGet){
            return [
                'title'=> $title,
                'content'=>$this->renderAjax('create', [
                    'model' => $model,
                ]),
                'footer'=> Html::button('Tutup',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                    Html::button('Simpan',['class'=>'btn btn-primary','type'=>"submit"])

            ];
        }else if($model->load($request->post()) && $model->save()){
            return [
                'forceReload'=>$tgtjax ? '#'.$tgtjax : '#crud-datatable-pjax',
                'title'=> $title,
                'content'=>'<span class="text-success">Berhasil menambahkan master data '.$this->name.'</span>',
                'footer'=> Html::button('Tutup',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                    Html::a('Tambah Lagi',['create'],['class'=>'btn btn-primary','role'=>'modal-remote'])

            ];
        }

        return [
            'title'=> $title,
            'content'=>$this->renderAjax('create', [
                'model' => $model,
            ]),
            'footer'=> Html::button('Tutup',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                Html::button('Simpan',['class'=>'btn btn-primary','type'=>"submit"])

        ];
    }

    /**
     * Updates an existing  model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);

        $title = 'Update '.$this->name.': '.$model->name;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if($request->isGet){
            return [
                'title'=> $title,
                'content'=>$this->renderAjax('update', [
                    'model' => $model,
                ]),
                'footer'=> Html::button('Tutup',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                    Html::button('Simpan',['class'=>'btn btn-primary','type'=>"submit"])
            ];
        }else if($model->load($request->post()) && $model->save()){
            return [
                'forceReload'=>'#crud-datatable-pjax',
                'title'=> $this->name." : ".$model->name,
                'content'=>$this->renderAjax('view', [
                    'model' => $model,
                ]),
                'footer'=> Html::button('Tutup',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                    Html::a('Edit',['update','id'=>$id],['class'=>'btn btn-primary','role'=>'modal-remote'])
            ];
        }

        return [
            'title'=> "Update  #".$id,
            'content'=>$this->renderAjax('update', [
                'model' => $model,
            ]),
            'footer'=> Html::button('Tutup',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                Html::button('Simpan',['class'=>'btn btn-primary','type'=>"submit"])
        ];
    }

    /**
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        if (!$model->delete()){
            foreach ($model->getErrors('name') as $msg){
                throw new HttpException(406, $msg);
            }
        }

        if($request->isAjax){
            /*
            *   Process for ajax request
            */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];
        }else{
            /*
            *   Process for non-ajax request
            */
            return $this->redirect(['index']);
        }

    }
}

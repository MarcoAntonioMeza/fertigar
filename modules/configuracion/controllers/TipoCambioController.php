<?php
namespace app\modules\configuracion\controllers;

use Yii;
use yii\db\Query;
use yii\helpers\Url;
use app\models\catalogo\TipoCambio;
use app\models\catalogo\ViewTipoCambios;
use yii\web\BadRequestHttpException;
use yii\web\Response;



class TipoCambioController extends \app\controllers\AppController
{

    private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('admin'),
            'update' => Yii::$app->user->can('admin'),
            'cancel' => Yii::$app->user->can('admin'),
        ];
    }
    public function actionIndex()
    {
        return $this->render('index',[
            'can'           => $this->can
        ]);
    }
   
  
    public function actionPostTipoCambio()
    {

        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            Yii::$app->response->format = Response::FORMAT_JSON;
            $post = $request->post();
            $fecha      = isset($post["fecha"]) && $post["fecha"]  ? $post["fecha"] : null;
            $tipoCambio     = isset($post["tipo_cambio"]) && $post["tipo_cambio"]  ? $post["tipo_cambio"] : null;
            
            if ($fecha ) {
                $response = TipoCambio::saveTasa($fecha, $tipoCambio);
                return $response;
            }

            return [
                "code"      => 10,
                "message"   => "Ocurrio un error, intenta nuevamente",
            ];
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');

       
    }

 
 


//------------------------------------------------------------------------------------------------//
// BootstrapTable list
//------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */

  
    public function actionTipoCambioJsonBtt(){
        return ViewTipoCambios::getJsonBtt(Yii::$app->request->get());
    }

 
}

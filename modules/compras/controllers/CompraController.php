<?php

namespace app\modules\compras\controllers;

use Yii;
use kartik\mpdf\Pdf;
use yii\web\Controller;
use app\models\compra\Compra;
use app\models\compra\ViewCompra;
use app\models\cobro\CobroVenta;
use app\models\credito\Credito;
use app\models\producto\Producto;
use yii\web\Response;
use app\models\cliente\ViewCliente;

/**
 * Default controller for the `clientes` module
 */
class CompraController extends \app\controllers\AppController
{

    private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => 1,#Yii::$app->user->can('compraCreate'),
            'cancel' => Yii::$app->user->can('compraCancel'),
            'hideMonto' =>  Yii::$app->user->can('ENCARGADO CEDIS SIN MONTOS'),
        ];
    }

    public function actionViewModal($id)
    {
        $model = Compra::findOne($id);
        if ($model->entrada) {
            $entrada = $model->entrada->operacionDetalles;
            $entradaarray = array();
            foreach ($entrada as $entradas) {
                $nuevoRegistro = [
                    'id' => $entradas->id,
                    'producto_id' => $entradas->producto->nombre,
                    'cantidad' => $entradas->cantidad,
                    'costo' => $entradas->costo,
                    'total' => number_format($entradas->cantidad * $entradas->costo, 2),
                ];
                $entradaarray[] = $nuevoRegistro;
            }
        } else {
            $entrada = "";
        }

        if ($model->compraDetalles) {
            $compradetalles = $model->compraDetalles;
            $comprasdetallesarray = array();
            foreach ($compradetalles as $compradetalle) {
                $nuevoRegistro = [
                    'id' => $compradetalle->id,
                    'producto_id' => $compradetalle->producto->nombre,
                    'cantidad' => $compradetalle->cantidad,
                    'costo' => $compradetalle->costo,
                    'total' => number_format($compradetalle->cantidad * $compradetalle->costo, 2),
                ];
                $comprasdetallesarray[] = $nuevoRegistro;
            }
        } else {
            $comprasdetallesarray[] = "";
        }

        if ($model->updatedBy)
            $nombre = $model->updatedBy->nombre;
        else
            $nombre = "";
        $data = [
            'code' => 202,
            'compras' => $model,
            'entrada' => $entradaarray,
            'compradetalles' => $comprasdetallesarray,
            'nombre' => $nombre,
        ];
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $data;
    }
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            "can" => $this->can
        ]);
    }

    /**
     * Displays a single EsysDivisa model.
     * @param integer $name
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
            'can'   => $this->can,
        ]);
    }
    public function actionSaveConfirmacion()
    {
        $request    = Yii::$app->request->post();
        $total      = 0;
        if ($request) {
            if ($request["input_cobro_efectivo"]) {

                $CobroVenta = CobroVenta::find()->andWhere(["and", ["=", "compra_id", $request["compra_id"]], ["=", "metodo_pago", CobroVenta::COBRO_EFECTIVO]])->one();
                if (!isset($CobroVenta->id)) {
                    $CobroVenta  =  new CobroVenta();
                }

                $CobroVenta->compra_id      = $request["compra_id"];
                $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
                $CobroVenta->tipo_cobro_pago = CobroVenta::PERTENECE_PAGO;
                $CobroVenta->metodo_pago    = CobroVenta::COBRO_EFECTIVO;
                $CobroVenta->cantidad       = $request["input_cobro_efectivo"];
                $CobroVenta->save();


                $total = $total + $request["input_cobro_efectivo"];
            }

            if ($request["input_cobro_cheque"]) {

                $CobroVenta = CobroVenta::find()->andWhere(["and", ["=", "compra_id", $request["compra_id"]], ["=", "metodo_pago", CobroVenta::COBRO_CHEQUE]])->one();
                if (!isset($CobroVenta->id)) {
                    $CobroVenta  =  new CobroVenta();
                }
                $CobroVenta->compra_id      = $request["compra_id"];
                $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
                $CobroVenta->tipo_cobro_pago = CobroVenta::PERTENECE_PAGO;
                $CobroVenta->metodo_pago    = CobroVenta::COBRO_CHEQUE;
                $CobroVenta->cantidad       = $request["input_cobro_cheque"];
                $CobroVenta->save();
                $total = $total + $request["input_cobro_cheque"];
            }

            if ($request["input_cobro_tranferencia"]) {

                $CobroVenta = CobroVenta::find()->andWhere(["and", ["=", "compra_id", $request["compra_id"]], ["=", "metodo_pago", CobroVenta::COBRO_TRANFERENCIA]])->one();
                if (!isset($CobroVenta->id)) {
                    $CobroVenta  =  new CobroVenta();
                }
                $CobroVenta->compra_id      = $request["compra_id"];
                $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
                $CobroVenta->tipo_cobro_pago = CobroVenta::PERTENECE_PAGO;
                $CobroVenta->metodo_pago    = CobroVenta::COBRO_TRANFERENCIA;
                $CobroVenta->cantidad       = $request["input_cobro_tranferencia"];
                $CobroVenta->save();

                $total = $total + $request["input_cobro_tranferencia"];
            }

            if ($request["input_cobro_tarjeta_credito"]) {

                $CobroVenta = CobroVenta::find()->andWhere(["and", ["=", "compra_id", $request["compra_id"]], ["=", "metodo_pago", CobroVenta::COBRO_TARJETA_CREDITO]])->one();
                if (!isset($CobroVenta->id)) {
                    $CobroVenta  =  new CobroVenta();
                }
                $CobroVenta->compra_id      = $request["compra_id"];
                $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
                $CobroVenta->tipo_cobro_pago = CobroVenta::PERTENECE_PAGO;
                $CobroVenta->metodo_pago    = CobroVenta::COBRO_TARJETA_CREDITO;
                $CobroVenta->cantidad       = $request["input_cobro_tarjeta_credito"];
                $CobroVenta->save();

                $total = $total + $request["input_cobro_tarjeta_credito"];
            }

            if ($request["input_cobro_tarjeta_debito"]) {

                $CobroVenta = CobroVenta::find()->andWhere(["and", ["=", "compra_id", $request["compra_id"]], ["=", "metodo_pago", CobroVenta::COBRO_TARJETA_DEBITO]])->one();
                if (!isset($CobroVenta->id)) {
                    $CobroVenta  =  new CobroVenta();
                }
                $CobroVenta->compra_id      = $request["compra_id"];
                $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
                $CobroVenta->tipo_cobro_pago = CobroVenta::PERTENECE_PAGO;
                $CobroVenta->metodo_pago    = CobroVenta::COBRO_TARJETA_DEBITO;
                $CobroVenta->cantidad       = $request["input_cobro_tarjeta_debito"];
                $CobroVenta->save();

                $total = $total + $request["input_cobro_tarjeta_debito"];
            }

            if ($request["input_cobro_deposito"]) {

                $CobroVenta = CobroVenta::find()->andWhere(["and", ["=", "compra_id", $request["compra_id"]], ["=", "metodo_pago", CobroVenta::COBRO_DEPOSITO]])->one();
                if (!isset($CobroVenta->id)) {
                    $CobroVenta  =  new CobroVenta();
                }
                $CobroVenta->compra_id      = $request["compra_id"];
                $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
                $CobroVenta->tipo_cobro_pago = CobroVenta::PERTENECE_PAGO;
                $CobroVenta->metodo_pago    = CobroVenta::COBRO_DEPOSITO;
                $CobroVenta->cantidad       = $request["input_cobro_deposito"];
                $CobroVenta->save();
                $total = $total + $request["input_cobro_deposito"];
            }

            if ($request["input_cobro_credito"]) {

                $CobroVenta = CobroVenta::find()->andWhere(["and", ["=", "compra_id", $request["compra_id"]], ["=", "metodo_pago", CobroVenta::COBRO_CREDITO]])->one();
                if (!isset($CobroVenta->id)) {
                    $CobroVenta  =  new CobroVenta();
                }
                $CobroVenta->compra_id      = $request["compra_id"];
                $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
                $CobroVenta->tipo_cobro_pago = CobroVenta::PERTENECE_PAGO;
                $CobroVenta->metodo_pago    = CobroVenta::COBRO_CREDITO;
                $CobroVenta->cantidad       = $request["input_cobro_credito"];
                $CobroVenta->save();

                $Credito = Credito::findOne(["compra_id" => $request["compra_id"]]);
                if (!isset($Credito->id)) {
                    $Credito = new  Credito();
                }

                $Credito->compra_id  = $request["compra_id"];
                $Credito->monto      = $request["input_cobro_credito"];
                $Credito->tipo       = Credito::TIPO_PROVEEDOR;
                //$Credito->created_by = $user->id;
                $Credito->save();


                $total = $total + $request["input_cobro_credito"];
            }

            $compra = Compra::findOne($request["compra_id"]);
            /*if ( $total >= $compra->total   )
                $compra->status = Compra::STATUS_PAGADA;
            else
                $compra->status = Compra::STATUS_PORPAGAR;
            */
            $compra->is_confirmacion = Compra::IS_CONFIRMACION_ON;

            $compra->save();
        }


        return $this->redirect([
            'view',
            'id' => $request["compra_id"]
        ]);
    }

    public function actionCancel($id)
    {
        $model = $this->findModel($id);
        if ($model) {
            $model->status = Compra::STATUS_CANCEL;
            foreach ($model->pagoCompra as $key => $item_pago) {

                if ($item_pago->metodo_pago == CobroVenta::COBRO_CREDITO) {
                    $credito = Credito::find()->andWhere(["compra_id" => $item_pago->compra_id])->one();
                    if ($credito) {
                        $credito->status = Credito::STATUS_CANCEL;
                        $credito->update();
                    }
                }

                $item_pago->is_cancel = CobroVenta::IS_CANCEL_ON;
                $item_pago->update();
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', "SE GENERO LA CANCELACION CON EXITO");
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        Yii::$app->session->setFlash('danger', "OCURRIO UN ERROR, VERIFICA TU INFORMACIÓN");

        return $this->redirect(['index']);
    }


    //------------------------------------------------------------------------------------------------//
    // BootstrapTable list
    //------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionComprasJsonBtt()
    {
        return ViewCompra::getJsonBtt(Yii::$app->request->get());
    }

    //------------------------------------------------------------------------------------------------//
    // HELPERS
    //------------------------------------------------------------------------------------------------//
    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @return Model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($name, $_model = 'model')
    {
        switch ($_model) {
            case 'model':
                $model = Compra::findOne($name);
                break;

            case 'view':
                $model = ViewCompra::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new \yii\web\NotFoundHttpException('La página solicitada no existe.');
    }




    /**
     * Creates a new Sucursal model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Compra();

        $post = Yii::$app->request->post();
        if ($post) {
            $sucursal_id = $post['Compra']['sucursal_id'] ?? Yii::$app->user->identity->sucursal_id;
            $proveedor_id = $post['Compra']['proveedor_id'] ?? null;
            $compraDetalles = $post['carrito_json'] ?? [];
            $destino_tipo = $post['Compra']['destino_tipo'] ?? null;
            $fecha_entrega = $post['Compra']['fecha_entrega'] ?? null;
            $cliente_id = $post['Compra']['cliente_id'] ?? null;
            $tipo_pago = $post['Compra']['tipo_pago'] ?? null;
            //$metodo_pago = $post['Compra']['metodo_pago'] ?? null;
            $nota = $post['Compra']['nota'] ?? '';
            $total = 0;
            $cliente_id = $post['Compra']['cliente_id'] ?? null;

            $compraDetalles = json_decode($compraDetalles, true);
            foreach ($compraDetalles as $detalle) {
                $sucursal_id = $detalle['sucursal_id'] ?? $sucursal_id;
                $cliente_id = $detalle['cliente_id'] ?? $cliente_id;
                if (isset($detalle['producto_id']) && isset($detalle['cantidad']) && isset($detalle['costo'])) {
                    $total += $detalle['cantidad'] * $detalle['costo'];
                }
            }
            echo "<pre>";
            print_r($post);
            echo "</pre>";
            #die;
            $model->load($post);
            $model->tiempo_recorrido = 0; // Asignar un valor por defecto
            $model->fecha_salida = time(); // Asignar la fecha actual
            $model->sucursal_id = $sucursal_id;
            $model->proveedor_id = $proveedor_id;
            $model->destino_tipo = $destino_tipo;
            $model->fecha_entrega = $fecha_entrega;
            $model->cliente_id = $cliente_id;
            $model->tipo_pago = $tipo_pago;
            $model->is_confirmacion = Compra::IS_CONFIRMACION_ON;
            #$model->metodo_pago = $metodo_pago;
            $model->status = Compra::STATUS_PROCESO;
            $model->is_especial = Compra::COMPRA_GENERAL;
            $model->venta_id = null;
            $model->total = $total;
            $model->nota = $nota;

            if (!$model->save()) {
                Yii::$app->session->setFlash('danger', "OCURRIO UN ERROR, VERIFICA TU INFORMACIÓN" . "<br>" . print_r($model->getErrors(), true));
                return $this->render('create', [
                    'model' => $model,
                    'can'   => $this->can,
                ]);
            }

            foreach ($compraDetalles as $detalle) {
                $compraDetalle = new \app\models\compra\CompraDetalle();
                $compraDetalle->compra_id = $model->id;
                $compraDetalle->producto_id = $detalle['producto_id'] ?? null;
                $compraDetalle->cantidad = $detalle['cantidad'] ?? 0;
                $compraDetalle->costo = $detalle['costo'] ?? 0;
                $compraDetalle->save();
            }

            $CobroVenta  =  new CobroVenta();
            $CobroVenta->compra_id      = $model->id;
            $CobroVenta->tipo           = CobroVenta::TIPO_COMPRA;
            $CobroVenta->tipo_cobro_pago = CobroVenta::PERTENECE_PAGO;
            $CobroVenta->metodo_pago    = $model->tipo_pago ;
            $CobroVenta->cantidad       = round($total, 2);
            $CobroVenta->created_by   = Yii::$app->user->identity->id;

            if ($model->tipo_pago == CobroVenta::COBRO_CREDITO) {
                #$model->fecha_credito = isset($model["fecha_liquidacion"]) ? strtotime($model["fecha_liquidacion"]) : time();
                $Credito = new  Credito();
                $Credito->compra_id  = $model->id;
                $Credito->monto      = round($detalle["cantidad"], 2);
                $Credito->fecha_credito = isset($model["fecha_credito"]) ? strtotime($model["fecha_credito"]) : time();
                $Credito->tipo       = CobroVenta::PERTENECE_PAGO;
                $Credito->created_by = Yii::$app->user->identity->id;
                $Credito->save();
            }
            $CobroVenta->save();
            Yii::$app->session->setFlash('success', "COMPRA REGISTRADA CORRECTAMENTE");
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'can'   => $this->can,
        ]);
    }

    public function actionGetProducto()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $proveedor_id = Yii::$app->request->get('proveedor_id');
        $productos = Producto::find()
            ->select(['id', 'nombre'])
            ->where(['proveedor_id' => $proveedor_id, 'status' => Producto::STATUS_ACTIVE])
            ->asArray()->all();
        $result = [];
        foreach ($productos as $producto) {
            $result[$producto['id']] = $producto['nombre'];
        }
        return $result;
    }


    public function actionClienteAjax($q = false, $cliente_id = false)
    {
        $request = Yii::$app->request;

        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {


            if ($q) {
                $text = $q;
            } else {
                $text = Yii::$app->request->get('data');
                $text = $text['q'];
            }

            if (is_null($text) && $cliente_id)
                $user = ViewCliente::getClienteAjax($cliente_id, true);
            else
                $user = ViewCliente::getClienteAjax($text, false);
            // Obtb venemos user


            // Devolvemos datos YII2 SELECT2
            if ($q) {
                return $user;
            }

            // Devolvemos datos CHOSEN.JS
            $response = ['q' => $text, 'results' => $user];

            return $response;
        }
        throw new \yii\web\BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->status = Compra::STATUS_PAGADA;
    }
}

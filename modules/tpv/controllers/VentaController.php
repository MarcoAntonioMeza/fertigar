<?php

namespace app\modules\tpv\controllers;

use app\models\sucursal\Sucursal;
use Yii;
use kartik\mpdf\Pdf;
use yii\db\Query;
use yii\web\Response;
use app\models\venta\Venta;
use app\models\venta\ViewVenta;
use app\models\producto\Producto;
use app\models\venta\VentaDetalle;
use app\models\producto\ViewProducto;
use app\models\cobro\CobroVenta;
use app\models\inv\InvProductoSucursal;
use app\models\apertura\AperturaCaja;
use app\models\apertura\AperturaCajaDetalle;
use app\models\catalogo\TipoCambio;
use app\models\credito\Credito;
use app\models\user\User;
use app\models\esys\EsysSetting;
use app\models\credito\CreditoTokenPay;
use app\models\credito\CreditoAbono;
use app\models\trans\TransProductoInventario;
use app\models\venta\VentaTokenPay;
use app\models\cliente\ViewCliente;
use app\models\inv\Operacion;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use app\models\cliente\Cliente;
use app\models\MathUtils;

/**
 * Default controller for the `clientes` module
 */
class VentaController extends \app\controllers\AppController
{

    private $can;

    public function init()
    {
        parent::init();

        $this->can = [
            'create' => Yii::$app->user->can('ventaCreate'),
            'update' => Yii::$app->user->can('ventaUpdate'),
            'cancel' => Yii::$app->user->can('ventaCancel'),
            'caja'   => Yii::$app->user->can('openCloseCaja') || Yii::$app->user->identity->id == 5 ? true : false,
        ];
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


    public function actionImprimirTicket($id)
    {
        $ids = explode(",", $id);

        $model = Venta::find()->with('ventaDetalle')->where(['id' => $ids])->all();
        $count_ventas = VentaDetalle::find()->where(['venta_id' => $ids])->count();
        $model_detalle_venta = VentaDetalle::find()->where(['venta_id' => $ids])->all();

        $lengh = 270;
        $width = 80;
        $lengh = $lengh + ($count_ventas * 40);

        $content = $this->renderPartial('ticket', ["model" => $model, "model_detalle_venta" => $model_detalle_venta, 'id' => $id]);

        ini_set('memory_limit', '-1');

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // Custom ticket size
            'format' => array($width, $lengh),
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            // your html content input
            'content' => $content,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            // any css to be embedded if required
            'cssInline' => '.kv-heading-1{font-size:18px}',
            // set mPDF properties on the fly
            'options' => ['title' => 'Ticket'],
            // call mPDF methods on the fly
            'methods' => [
                //'SetHeader'=>[ 'TICKET #' . $model->id],
                //'SetFooter'=>['{PAGENO}'],
            ]
        ]);

        $pdf->marginLeft = 0.5;
        $pdf->marginRight = 0.5;

        // return the pdf output as per the destination setting
        return $pdf->render();
    }



     public function actionImprimirPagareTicket($id)
    {
        $venta_model = $this->findModel($id);
        $ids = explode(",", $id);

        $model = Venta::find()->with('ventaDetalle')->where(['id' => $ids])->all();
    
        $count_ventas = VentaDetalle::find()->where(['venta_id' => $ids])->count();
        $model_detalle_venta = VentaDetalle::find()->where(['venta_id' => $ids])->all();

        $content = $this->renderPartial('ticket_pagare', ["model" => $model, "model_detalle_venta" => $model_detalle_venta, 'id' => $id, 'venta_model' => $venta_model]);

        ini_set('memory_limit', '-1');

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // Letter paper format
            'format' => Pdf::FORMAT_LETTER,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            // your html content input
            'content' => $content,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            // any css to be embedded if required
            'cssInline' => '.kv-heading-1{font-size:18px}',
            // set mPDF properties on the fly
            'options' => ['title' => 'Ticket de envio'],
            // call mPDF methods on the fly
            'methods' => [
                //'SetHeader'=>[ 'TICKET #' . $model->id],
                //'SetFooter'=>['{PAGENO}'],
            ]
        ]);

        $pdf->marginLeft = 10;
        $pdf->marginRight = 10;

        // return the pdf output as per the destination setting
        return $pdf->render();
    }


 

    public function actionImprimirCredito($pay_items)
    {
        //$pay_id = explode(',', $pay_items);


        $lengh = 270;
        $width = 72;
        $count = 0;
        //$total_piezas = 0;
        //$total_piezas = $total_piezas + count($pay_id);

        //$lengh = $lengh + ($count  * 75 );
        //$lengh = $lengh + ( $total_piezas * 7);

        $width = $width + ($count  * 2);


        $model = [];

        ///foreach ($pay_id as $key => $payment) {
        $getCreditos = CreditoTokenPay::find()->andWhere(["token_pay" => $pay_items])->all();
        foreach ($getCreditos as $key => $item_credito) {
            //$CobroVenta = CobroVenta::findOne($payment);
            $credito = Credito::find()->where(['id' => $item_credito->credito_id])->one();
            array_push($model, [
                "credito_id"   => $item_credito->credito_id,
                //"cantidad"  => $CobroVenta->cantidad,
                "credito"           => $credito,
                "venta"             => isset($item_credito->credito->venta->id) ? $item_credito->credito->venta->id : '00',
                "cantidad_credito"  => $item_credito->credito->monto,
                "total_abonado"     => $item_credito->credito->monto_pagado,
            ]);
        }
        //}


        $content = $this->renderPartial('ticket-credito', ["model" => $model, 'token' => $pay_items]);

        ini_set('memory_limit', '-1');

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => array($width, $lengh), //Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            // your html content input
            'content' => $content,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            // any css to be embedded if required
            'cssInline' => '.kv-heading-1{font-size:18px}',
            // set mPDF properties on the fly
            'options' => ['title' => 'Ticket de envio'],
            // call mPDF methods on the fly
            'methods' => [
                //'SetHeader'=>[ 'TICKET'],
                //'SetFooter'=>['{PAGENO}'],
            ]
        ]);

        $pdf->marginLeft = 1;
        $pdf->marginRight = 1;

        $pdf->setApi();

        /*$pdf_api = $pdf->getApi();
        $pdf_api->SetWatermarkImage(Yii::getAlias('@web').'/img/marca_agua_cora.png');
        $pdf_api->showWatermarkImage = true;*/


        // return the pdf output as per the destination setting
        return $pdf->render();
    }

    public function actionImprimirAcusePdf($venta_id)
    {

        $Reparto = $this->findModel($venta_id);

        ini_set('memory_limit', '-1');

        $content = "";

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => Pdf::FORMAT_LETTER,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            // your html content input
            'content' => $content,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            // any css to be embedded if required
            'cssInline' => '.kv-heading-1{font-size:18px}',
            // set mPDF properties on the fly
            'options' => ['title' => 'Acuse'],
            // call mPDF methods on the fly
        ]);

        $pdf->setApi();
        $pdf_api = $pdf->getApi();

        $content = $this->renderPartial('pagare', ["model" => $Reparto, "copy" => false]);
        $pdf_api->WriteHTML($content);
        $pdf_api->setFooter('<table width="100%" style="padding-top: 5px;margin-top: 15px">
            
        </table>
        <table width="100%" style="margin-top: 15px;">
            <tr>
                <td width="70%" >
                    <strong style="font-size:12px">CLIENTE:  <small style="font-size:16px; font-weight: 100;">' . $Reparto->cliente->nombreCompleto . '</small></strong>
                </td>
                <td align="center" width="30%">
                    <table width="100%">
                        <tr>
                            <td align="center" style="border-bottom-style:solid; border-width: 2px; "></td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size: 14px">ACEPTA(MOS)</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <p style="text-align:right; border-width: 1px; border-bottom-style: solid;">ORIGINAL</p>');

        $pdf_api->AddPage();

        $content = $this->renderPartial('pagare', ["model" => $Reparto, "copy" => true]);
        $pdf_api->WriteHTML($content);
        $pdf_api->setFooter('<table width="100%" style="padding-top: 5px;margin-top: 15px">
            
        </table>
        <table width="100%" style="margin-top: 15px;">
            <tr>
                <td width="70%" >
                    <strong style="font-size:12px">CLIENTE:  <small style="font-size:16px; font-weight: 100;">' . $Reparto->cliente->nombreCompleto . '</small></strong>
                </td>
                <td align="center" width="30%">
                    <table width="100%">
                        <tr>
                            <td align="center" style="border-bottom-style:solid; border-width: 2px; "></td>
                        </tr>
                        <tr>
                            <td align="center" style="font-size: 14px">ACEPTA(MOS)</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <p style="text-align:right; border-width: 1px; border-bottom-style: solid;">COPIA</p>');


        return $pdf->render();
    }

    public function actionUpdate($id)
    {

        $model = $this->findModel($id);

        // Cargamos datos de dirección
        $model->dir_obj   = $model->direccion;

        $model->dir_obj->codigo_search   = isset($model->direccion->esysDireccionCodigoPostal->codigo_postal)  ? $model->direccion->esysDireccionCodigoPostal->codigo_postal : null;

        $model->tipo = Sucursal::TIPO_SUCURSAL;

        // Si no se enviaron datos POST o no pasa la validación, cargamos formulario
        if ($model->load(Yii::$app->request->post()) && $model->dir_obj->load(Yii::$app->request->post())) {

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
            return $this->render('update', [
                'model' => $model,
            ]);
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }
    
    public function sonTodosIguales($array)
    {
        if (count($array) <= 1) {
            return true;
        }
        $primerClienteId = $array[0]['cliente_id'];

        foreach ($array as $elemento) {
            if ($elemento['cliente_id'] !== $primerClienteId) {
                return false;
            }
        }

        return true;
    }
    public function actionGetPreVenta()
    {

        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('id')) {
                $datosArray = explode(",", $request->get('id'));
                $arrayDatosTrimmed = array_map(function ($dato) {
                    return trim($dato);
                }, $datosArray);
                //                $arrayDatosTrimmed = array_map(fn($dato) => trim($dato), $datosArray);
                $cadenaUnida = implode(', ', $arrayDatosTrimmed);
                $array = explode(',', $cadenaUnida);
                $query = new \yii\db\Query;
                $resultados = $query
                    ->select('cliente_id')
                    ->from('venta')
                    ->where(['IN', 'id', $array])
                    ->all();
                $verificados = self::sonTodosIguales($resultados);
                if ($verificados == false) {
                    return [
                        "code" => 202,
                        "errorcode" => 50,
                        "message" => "Error en los datos ingresados, no todos le pertenecen al mismo cliente o al público en general",
                    ];
                }
                $venta = Venta::find()
                    ->where(['IN', 'id', $array])
                    ->all();
                //                $venta  = Venta::findOne(trim($request->get('id')));

                if (count($venta) > 0) {
                    foreach ($venta as $ventas) {
                        $responseArray = [
                            "id"                => $ventas->id,
                            "cliente_id"        => $ventas->cliente_id,
                            "cliente"           => isset($ventas->cliente->id) ? $ventas->cliente->nombreCompleto : null,
                            "ruta_sucursal_id"  => $ventas->ruta_sucursal_id,
                            "sucursal"          => isset($ventas->sucursal->id) ? $ventas->sucursal->nombre : null,
                            "tipo"              => $ventas->tipo,
                            "status"            => $ventas->status,
                            "total"             => $ventas->total,
                            "venta_detalle"     => [],

                        ];
                    }
                    foreach ($venta as $ventas) {
                        $detalles = $ventas->ventaDetalle;
                        foreach ($ventas->ventaDetalle as $key => $v_detalle) {

                            array_push($responseArray["venta_detalle"], [
                                "producto_id"       => $v_detalle->producto_id,
                                "sucursal_abastece" => $v_detalle->apply_bodega == VentaDetalle::APPLY_BODEGA_ON ? 'CEDIS' : 'TIENDA',
                                "producto"      => isset($v_detalle->producto->id) ? $v_detalle->producto->nombre : null,
                                "clave"         => $v_detalle->producto->clave,
                                "cantidad"      => $v_detalle->cantidad,
                                "precio_venta"  => $v_detalle->precio_venta,
                                "producto_unidad"  => $v_detalle->producto->unidadMedida ? $v_detalle->producto->unidadMedida->nombre : '--',
                            ]);
                        }
                    }

                    return [
                        "code" => 202,
                        "venta" => $responseArray,
                    ];
                }
            }
            return [
                "code" => 10,
                "message" => "Error al buscar la venta, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }


    public function actionSearchProductoNombre()
    {

        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('nombre')) {

                $productos  = Producto::find()->andFilterWhere([
                    'or',
                    ['like', 'nombre', trim($request->get('nombre'))],
                    ['like', 'clave', trim($request->get('nombre'))],
                ])->all();

                $responseArray = [];

                #return [
                #    "code" => 202,
                #    "productos" => $productos,
                #];

                foreach ($productos as $key => $producto) {

                    $existencia = 0;
                    if (Yii::$app->user->identity->sucursal_id) {

                        $InvProductoSucursal = InvProductoSucursal::find()->andWhere(["and"/*,["=", "sucursal_id" , Yii::$app->user->identity->sucursal_id]*/, ["=", "producto_id", $producto->id]])->one();

                        if (isset($InvProductoSucursal->id))
                            $existencia = $InvProductoSucursal->cantidad;
                    }

                    array_push($responseArray, [
                        "id"        => $producto->id,
                        "clave"    => $producto->clave,
                        "nombre"    => $producto->nombre,
                        "publico"     => $producto->precio_publico,
                        "mayoreo"     => $producto->precio_mayoreo,
                        "existencia"     => $existencia,
                        "menudeo"    => $producto->precio_menudeo,
                        "proveedor"         => isset($producto->proveedor->nombre) ? $producto->proveedor->nombre : 'N/A',
                        "tipo_medida"       => $producto->unidadMedida ? $producto->unidadMedida->nombre : '--',
                        //"tipo_medida_text"  =>Producto::$medidaList[$producto->tipo_medida],
                    ]);
                }

                if (isset($producto->id)) {
                    return [
                        "code" => 202,
                        "productos" => $responseArray,
                    ];
                }
            }
            return [
                "code" => 10,
                "message" => "Error al buscar el producto, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionRetiroEfectivoCaja()
    {
        $request = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if (Yii::$app->request->isAjax) {
            /*echo "<pre>";
            print_r($request['efectivo']);
            die();*/
            $AperturaCaja = AperturaCaja::find()->andWhere([
                "and",
                ["=", "status", AperturaCaja::STATUS_PROCESO],
                ["=", "user_id", Yii::$app->user->identity->id],
            ])->one();
            $usuarios = User::find()
                ->where(['id' => Yii::$app->user->identity->id])
                ->all();



            if ($AperturaCaja &&  isset($request["efectivo"]) && floatval($request["efectivo"]) > 0) {

                //$isAproved = AperturaCaja::getTotalCaja() > str_replace(",","",$request["efectivo"])  ? true : false;
                $isAproved = true;

                if ($isAproved) {

                    $AperturaCajaDetalle = new AperturaCajaDetalle();
                    $AperturaCajaDetalle->apertura_caja_id  = $AperturaCaja->id;
                    $AperturaCajaDetalle->tipo              = AperturaCajaDetalle::TIPO_RETIRO;
                    $AperturaCajaDetalle->pertenece         = AperturaCajaDetalle::PERTENECE_RETIRO;
                    $AperturaCajaDetalle->cantidad          = isset($request["efectivo"]) ? str_replace(",", "", $request["efectivo"]) : 0;
                    $AperturaCajaDetalle->status            = AperturaCajaDetalle::STATUS_SUCCESS;

                    if ($AperturaCajaDetalle->save()) {
                        $notificacionEmails = EsysSetting::getCorreoNotificacionRetiro();
                        $emailArray = explode(",", $notificacionEmails);
                        if (count($emailArray) > 0) {
                            $lengh = 180;
                            $width = 80;
                            $content = '';

                            ini_set('memory_limit', '-1');

                            $pdf = new Pdf([
                                // set to use core fonts only
                                'mode' => Pdf::MODE_CORE,
                                // A4 paper format
                                'format' => array($width, $lengh), //Pdf::FORMAT_A4,
                                // portrait orientation
                                'orientation' => Pdf::ORIENT_PORTRAIT,
                                // stream to browser inline
                                'destination' => Pdf::DEST_DOWNLOAD,
                                // your html content input
                                'content' => $content,
                                // format content from your own css file if needed or use the
                                // enhanced bootstrap css built by Krajee for mPDF formatting
                                'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
                                // any css to be embedded if required
                                'cssInline' => '.kv-heading-1{font-size:18px}',
                                // set mPDF properties on the fly
                                'options' => ['title' => 'Ticket de recibo'],
                                // call mPDF methods on the fly
                                'methods' => [
                                    'SetHeader' => [AperturaCajaDetalle::$tipoList[$AperturaCajaDetalle->tipo] . ' #' . $AperturaCajaDetalle->id],
                                    'SetFooter' => [''],
                                ]
                            ]);


                            $pdf->marginLeft = 3;
                            $pdf->marginRight = 3;

                            $pdf->setApi();
                            $pdf_api = $pdf->getApi();

                            $content =  $this->renderFile(Yii::getAlias('@app') . '/modules/tpv/views/venta/ticket-retiro.php', ["model" => $AperturaCajaDetalle]);

                            $pdf_api->WriteHTML($content);

                            $filename = Yii::getAlias('@webroot') . '/temp/ticket_retiro_' . Yii::$app->security->generateRandomString() . "-" . time() . ".pdf";

                            $pdf_api->Output($filename, \Mpdf\Output\Destination::FILE);

                            try {
                                Yii::$app->mailer->compose()
                                    ->setFrom(Yii::$app->params['supportEmail'])
                                    ->setTo($emailArray)
                                    ->attach($filename)
                                    ->setTextBody('SE GENERÓ UN RETIRO POR UN MONTO DE $' . number_format($AperturaCajaDetalle->cantidad, 2))
                                    ->setSubject('RETIRO GENERADO -  ' . date("Y-m-d", time()) . ' RETIRO DE [$ ' . number_format($AperturaCajaDetalle->cantidad, 2) . ']- GENERADO POR ' . $usuarios[0]['username'])
                                    ->send();

                                return [
                                    "code"      => 202,
                                    "data"      => $AperturaCajaDetalle->id,
                                    "message"   => 'SE REALIZO CORRECTAMENTE EL RETIRO',
                                    "type"      => "Success",
                                ];
                            } catch (\Exception $e) {

                                return [
                                    "code"      => 202,
                                    "data"      => $AperturaCajaDetalle->id,
                                    "message"   => 'SE REALIZO CORRECTAMENTE EL RETIRO',
                                    "type"      => "Success",
                                ];
                            }
                        } else {
                            return [
                                "code"      => 202,
                                "data"      => $AperturaCajaDetalle->id,
                                "message"   => 'SE REALIZO CORRECTAMENTE EL RETIRO',
                                "type"      => "Success",
                            ];
                        }
                    }
                } else {
                    return [
                        "code"      => 10,
                        "message"   => 'Ocurrio un error, intenta nuevamente',
                        "type"      => "Error",
                    ];
                }
            }

            return [
                "code"      => 10,
                "message"   => 'Ocurrio un error, intenta nuevamente',
                "type"      => "Error",
            ];
        }
    }

    public function actionRegistroGastoCaja()
    {
        $request = Yii::$app->request->post();

        $AperturaCaja = AperturaCaja::find()->andWhere([
            "and",
            ["=", "status", AperturaCaja::STATUS_PROCESO],
            ["=", "user_id", Yii::$app->user->identity->id],
        ])->one();


        if ($AperturaCaja &&  isset($request["efectivo_gasto"]) && $request["efectivo_gasto"] && isset($request["observacion"]) && $request["observacion"] && isset($request["tipo_gasto_id"]) && $request["tipo_gasto_id"]) {

            $isAproved = AperturaCaja::getTotalCaja() > $request["efectivo_gasto"]  ? true : false;


            if ($isAproved) {

                $AperturaCajaDetalle = new AperturaCajaDetalle();
                $AperturaCajaDetalle->apertura_caja_id  = $AperturaCaja->id;
                $AperturaCajaDetalle->tipo              = AperturaCajaDetalle::TIPO_GASTO;
                $AperturaCajaDetalle->pertenece         = AperturaCajaDetalle::PERTENECE_RETIRO;
                $AperturaCajaDetalle->observacion         = $request["observacion"];
                $AperturaCajaDetalle->tipo_gasto_id         = $request["tipo_gasto_id"];
                $AperturaCajaDetalle->concepto          = isset($request["concepto"]) ? $request["concepto"] : null;
                $AperturaCajaDetalle->cantidad          = isset($request["efectivo_gasto"]) ? $request["efectivo_gasto"] : 0;
                $AperturaCajaDetalle->status            = AperturaCajaDetalle::STATUS_SUCCESS;
                $AperturaCajaDetalle->updated_at            = time();

                if ($AperturaCajaDetalle->save()) {
                    Yii::$app->session->setFlash('success', 'SE REALIZO CORRECTAMENTE EL REGISTRO DE GASTO');
                    return $this->redirect(['pre-venta']);
                    //return $this->actionImprimirTicketRetiro($AperturaCajaDetalle);
                }
            } else {

                Yii::$app->session->setFlash('danger', 'EL MONTO A GASTAR NO DEBE SER MAYOR A LA CANTIDAD EN CAJA');
                return $this->redirect(['pre-venta']);
            }
        }
        Yii::$app->session->setFlash('danger', 'Ocurrio un error al REALIZAR EL REGISTRO DE GASTO DE LA CAJA, intenta nuevamente');
        return $this->redirect(['pre-venta']);
    }

    public function actionImprimirTicketRetiro($id)
    {
        $model = AperturaCajaDetalle::find()->where(['id' => $id])->one();

        $lengh = 180;
        $width = 80;
        $content = $this->renderPartial('ticket-retiro', ["model" => $model]);

        ini_set('memory_limit', '-1');

        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_CORE,
            // A4 paper format
            'format' => array($width, $lengh), //Pdf::FORMAT_A4,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_BROWSER,
            // your html content input
            'content' => $content,
            // format content from your own css file if needed or use the
            // enhanced bootstrap css built by Krajee for mPDF formatting
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/src/assets/kv-mpdf-bootstrap.min.css',
            // any css to be embedded if required
            'cssInline' => '.kv-heading-1{font-size:18px}',
            // set mPDF properties on the fly
            'options' => ['title' => 'Ticket de recibo'],
            // call mPDF methods on the fly
            'methods' => [
                'SetHeader' => [AperturaCajaDetalle::$tipoList[$model->tipo] . ' #' . $model->id],
                'SetFooter' => [''],
            ]
        ]);

        $pdf->marginLeft = 3;
        $pdf->marginRight = 3;

        $pdf->setApi();

        /*$pdf_api = $pdf->getApi();
        $pdf_api->SetWatermarkImage(Yii::getAlias('@web').'/img/marca_agua_cora.png');
        $pdf_api->showWatermarkImage = true;*/


        // return the pdf output as per the destination setting
        return $pdf->render();
    }

    public function actionGetCierreCajaMonto()
    {

        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {


            $AperturaCaja = AperturaCaja::find()->andWhere([
                "and",
                ["=", "status", AperturaCaja::STATUS_PROCESO],
                ["=", "user_id", Yii::$app->user->identity->id],
            ])->one();

            $cierre_venta_efectivo   = 0;
            $cierre_venta_credito   = 0;
            $totalVenta     = 0;
            $totalRetiro    = 0;
            $totalGasto    = 0;

            /*echo "<pre>";
            print_r($AperturaCaja->aperturaCajaDetalles);
            die();*/

            foreach ($AperturaCaja->aperturaCajaDetalles as $key => $apertura) {
                if ($apertura->tipo == AperturaCajaDetalle::TIPO_VENTA) {
                    $cierre_venta_efectivo = $cierre_venta_efectivo +  $apertura->cantidad;
                    //$totalVenta   = $totalVenta + $apertura->cantidad;
                }
                if ($apertura->tipo == AperturaCajaDetalle::TIPO_CREDITO) {
                    $cierre_venta_credito = $cierre_venta_credito +  $apertura->cantidad;
                    //$totalVenta   = $totalVenta + $apertura->cantidad;
                }

                if ($apertura->tipo == AperturaCajaDetalle::TIPO_RETIRO) {
                    //$cierre_venta   = $cierre_venta - $apertura->cantidad;
                    $totalRetiro    = $totalRetiro + $apertura->cantidad;
                }
                if ($apertura->tipo == AperturaCajaDetalle::TIPO_GASTO) {
                    //$cierre_venta   = $cierre_venta - $apertura->cantidad;
                    $totalGasto    = $totalGasto + $apertura->cantidad;
                }
            }

            return [
                "code" => 202,
                "cierre_venta_efectivo"      => $cierre_venta_efectivo,
                "cierre_venta_credito"      => $cierre_venta_credito,
                "totalVenta"        => $cierre_venta_efectivo + $cierre_venta_credito,
                "totalRetiro"       => $totalRetiro,
                "totalGasto"       => $totalGasto,
                "monto_apertura"    => $AperturaCaja->cantidad_caja,
                "total_caja"        => $AperturaCaja->cantidad_caja + $cierre_venta_efectivo - $totalRetiro - $totalGasto,
                "totalTransferencia" => number_format(AperturaCaja::getTotalTranferenciaTpv($AperturaCaja->id), 2),
                "totalCheque"       => number_format(AperturaCaja::getTotalChequeTpv($AperturaCaja->id), 2),
                "totalTarjCredito"  => number_format(AperturaCaja::getTotalTarjetaCreditoTpv($AperturaCaja->id), 2),
                "totalTarjDebito"   => number_format(AperturaCaja::getTotalTarjetaDebitoTpv($AperturaCaja->id), 2),
                "totalDeposito"     => number_format(AperturaCaja::getTotalDepositoTpv($AperturaCaja->id), 2),
                "totalCredito"      => number_format(AperturaCaja::getTotalCreditoPayTpv($AperturaCaja->id), 2)
            ];
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

 
    public function actionAperturaCajaCreate()
    {
        $request = Yii::$app->request->post();

        $AperturaCaja = new AperturaCaja();
        $AperturaCaja->user_id        = Yii::$app->user->identity->id;
        $AperturaCaja->cantidad_caja  = isset($request["cantidad_apertura"]) && $request["cantidad_apertura"] ?  $request["cantidad_apertura"] : 0;
        $AperturaCaja->status         = AperturaCaja::STATUS_PROCESO;
        $AperturaCaja->fecha_apertura = time();

        if ($AperturaCaja->save()) {
            Yii::$app->session->setFlash('success', 'SE REALIZO CORRECTAMENTE LA APERTURA DE CAJA');
            return $this->redirect(['menu-venta']);
        }
        Yii::$app->session->setFlash('danger', 'Ocurrio un error al APERTURAR LA CAJA, intenta nuevamente');
        return $this->redirect(['menu-venta']);
    }


    public function actionAperturaCajaUpdate()
    {
        $request = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;

        $AperturaCaja           = AperturaCaja::getInfoAperturaActual();

        $AperturaCaja->total    = isset($request["cantidad_cierre"]) && $request["cantidad_cierre"] ?  $request["cantidad_cierre"] : 0;
        $cuentaAbiertasArray    = isset($request["inputCuentaAbiertasArray"]) && $request["inputCuentaAbiertasArray"] ?  $request["inputCuentaAbiertasArray"] : 0;
        $AperturaCaja->status   = AperturaCaja::STATUS_CERRADA;
        $AperturaCaja->fecha_cierre = time();

        $cuentaAbiertasArray = json_decode($cuentaAbiertasArray);

        if ($cuentaAbiertasArray) {
            foreach ($cuentaAbiertasArray as $key => $cuenta) {
                if ($cuenta->accion == 10) {
                    $preventa = $this->findModel($cuenta->id);
                    $preventa->status = Venta::STATUS_CANCEL;
                    $preventa->save();
                }
            }
        }

        if ($AperturaCaja->update()) {
            //return $this->redirect(['pre-venta']);
            Yii::$app->session->setFlash('success', 'SE REALIZO CORRECTAMENTE EL CIERRE');
            return [
                "code" => 201,
                "data" => $AperturaCaja->id,
                "message" => 'SE REALIZO CORRECTAMENTE EL CIERRE DE CAJA',
                "type" => "Success",
            ];
        } else {
            Yii::$app->session->setFlash('danger', 'Ocurrio un error al CERRAR LA CAJA, intenta nuevamente.');
            //return $this->redirect(['pre-venta']);
            return [
                "code" => 404,
                "message" => 'Ocurrio un error al CERRAR LA CAJA',
                "type" => "Error",
            ];
        }
    }


    public function actionGetArqueoCaja()
    {
        $request = Yii::$app->request->post();
        Yii::$app->response->format = Response::FORMAT_JSON;

        $AperturaCaja   = AperturaCaja::getInfoAperturaActual();
        $response       = [];
        foreach ($AperturaCaja->aperturaCajaDetalles as $key => $apertura_venta) {

            $clienteText = "";

            if ($apertura_venta->tipo == AperturaCajaDetalle::TIPO_VENTA) {
                $clienteText = isset($apertura_venta->venta->cliente_id) ? $apertura_venta->venta->cliente->nombreCompleto : '';
            }

            if ($apertura_venta->tipo == AperturaCajaDetalle::TIPO_CREDITO) {
                $getCreditos = CreditoTokenPay::find()->andWhere(["token_pay" => $apertura_venta->token_pay])->one();
                $credito = Credito::find()->where(['id' => $getCreditos->credito_id])->one();

                $clienteText = $credito->venta_id ? $credito->venta->cliente->nombreCompleto : $credito->cliente->nombreCompleto;
            }

            array_push($response, [
                "tipo"      => $apertura_venta->tipo,
                "tipo_text" => AperturaCajaDetalle::$tipoList[$apertura_venta->tipo],
                "venta_id"  => $apertura_venta->venta_id,
                "cliente"   => $clienteText,
                "credito_id" => $apertura_venta->token_pay,
                "cantidad"  => $apertura_venta->cantidad,
                "status"    => $apertura_venta->status,
                "created_at"    => $apertura_venta->created_at,
            ]);
        }

        return [
            "code"     => 202,
            "apertura" => $response,
        ];
    }

    public function actionGetCreditoCliente()
    {
        $request = Yii::$app->request->get();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (isset($request["cliente_id"]) && $request["cliente_id"]) {
            $credito = Credito::find()->leftJoin('venta', 'credito.venta_id = venta.id')
                ->andWhere([
                    "and",
                    ["=", "credito.tipo", Credito::TIPO_CLIENTE],
                ])
                ->andWhere([
                    "or",
                    ["=", "venta.cliente_id", $request["cliente_id"]],
                    ["=", "credito.cliente_id", $request["cliente_id"]]
                ])
                ->andWhere([
                    "or",
                    ["=", "credito.status", Credito::STATUS_ACTIVE],
                    ["=", "credito.status", Credito::STATUS_POR_PAGADA],
                ])
                ->all();
            $response = [];
            foreach ($credito as $key => $item_credito) {
                //$total_deuda     = floatval($item_credito->monto) - floatval(CobroVenta::getPagoCredito($item_credito->id));
                $ventaIDs = $item_credito->venta_id;
                if (empty($item_credito->venta_id)) {
                    $query = VentaTokenPay::find()->andWhere(["token_pay" => $item_credito->trans_token_venta])->all();
                    foreach ($query as $key => $itemOperacion) {
                        $ventaIDs = $ventaIDs . (empty($ventaIDs) ? '' : ',') . $itemOperacion->venta_id;
                    }
                }
                array_push($response, [
                    "id"         => $item_credito->id,
                    "cliente_id" => $item_credito->cliente_id,
                    "venta_id"   => $ventaIDs,
                    "compra_id"  => $item_credito->compra_id,
                    "descripcion" => $item_credito->descripcion,
                    "fecha_credito" => $item_credito->fecha_credito,
                    "monto"      => floatval($item_credito->monto) - floatval($item_credito->monto_pagado),
                    "nota"       => $item_credito->nota,
                    "status"     => $item_credito->status,
                    "tipo"       => $item_credito->tipo,
                    "created_at" => $item_credito->created_at,
                    "created_by" => $item_credito->created_by,
                    "created_by_user" => $item_credito->createdBy->nombreCompleto,
                    "updated_at" => $item_credito->updated_at,
                    "updated_by" => $item_credito->updated_by,
                ]);
            }
            return [
                "code" => 202,
                "credito" => $response,
            ];
        }

        return [
            "code" => 10,
            "message" => "Ocurrio un error, intenta nuevamente.",
        ];
    }

   
  
    public function actionCancelVenta()
    {
        $request = Yii::$app->request->post();

        if ($request["Venta"]["id"] && isset($request["Venta"]["nota"]) && $request["Venta"]["nota"]) {

            $venta        = Venta::findOne($request["Venta"]["id"]);

            $totalCobrado   = 0;
            $is_add         = true;
            /*VENTA GENERADA EN RUTA o PUNTO DE VENTA*/
            if (!$venta->ruta_sucursal_id  ||  $venta->is_tpv_ruta == Venta::IS_TPV_RUTA_ON) {

                foreach ($venta->cobroTpvVenta as $key => $item_pago) {
                    if ($item_pago->metodo_pago != CobroVenta::COBRO_CREDITO)
                        $totalCobrado += floatval($item_pago->cantidad);

                    if ($item_pago->metodo_pago == CobroVenta::COBRO_CREDITO) {
                        $credito = Credito::find()->andWhere(["venta_id" => $item_pago->venta_id])->one();
                        if ($credito) {
                            $credito->status = Credito::STATUS_CANCEL;
                            $credito->update();
                        }
                    }

                    $item_pago->is_cancel = CobroVenta::IS_CANCEL_ON;
                    $item_pago->update();
                }
            }


            /*VENTA GENERADA EN PREVENTA*/
            if ($venta->ruta_sucursal_id) {
                foreach ($venta->transaccion as $key => $item_tra) {
                    if (VentaTokenPay::getOperacionVentaCount($item_tra->token_pay) ==  1) {
                        foreach (CobroVenta::getVentaRutaOperacion($item_tra->token_pay) as $key => $item_pago) {

                            if ($item_pago->metodo_pago == CobroVenta::COBRO_CREDITO) {
                                $credito = Credito::find()->andWhere(["trans_token_venta" => $item_tra->token_pay])->one();
                                if ($credito) {
                                    $credito->status = Credito::STATUS_CANCEL;
                                    $credito->update();
                                }
                            }
                            $item_pago->is_cancel = CobroVenta::IS_CANCEL_ON;
                            $item_pago->update();
                        }
                    } else {
                        /* COMPLICADO GENERAR UN ALGOTIMO QUE CONTEMPLE MULTIPLES PAGOS Y MULTIPLES TRANSACCIONES*/
                        $is_add = false;
                    }
                }
            }
            if ($is_add) {
                foreach ($venta->ventaDetalle as $key => $item_detalle) {
                    $Producto   = Producto::findOne($item_detalle->producto_id);
                    $origen     = null;
                    if (!$venta->ruta_sucursal_id  ||  $venta->is_tpv_ruta == Venta::IS_TPV_RUTA_ON) {
                        $origen     = $venta->sucursal_id;
                    } else {
                        $origen     = $venta->ruta_sucursal_id;
                    }




                    if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO)
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", ["=", "sucursal_id", $origen], ["=", "producto_id", $Producto->sub_producto_id]])->one();

                    else
                        $InvProducto = InvProductoSucursal::find()->andWhere(["and", ["=", "sucursal_id", $origen], ["=", "producto_id", $item_detalle->producto_id]])->one();

                    if (isset($InvProducto->id)) {
                        if ($Producto->is_subproducto == Producto::TIPO_SUBPRODUCTO) {


                            $InvProducto2 = InvProductoSucursal::find()->andWhere(["and", ["=", "sucursal_id", $origen], ["=", "producto_id", $Producto->sub_producto_id]])->one();

                            if (isset($InvProducto2->id)) {
                                // SUB PRODUCTO SI SE ENCUENTRA REGISTRADO EN INVENTARIO

                                $InvProducto2->cantidad = floatval($InvProducto2->cantidad) + (floatval($item_detalle->cantidad) * $Producto->sub_cantidad_equivalente);
                                $InvProducto2->save();
                            } else {
                                // SUB PRODUCTO NO SE ENCUENTRA REGISTRADO EN INVENTARIO

                                $InvProductoSucursal  =  new InvProductoSucursal();
                                $InvProductoSucursal->sucursal_id   = $origen;
                                $InvProductoSucursal->producto_id   = $item_detalle->producto_id;
                                $InvProductoSucursal->cantidad      = $item_detalle->cantidad;
                                $InvProductoSucursal->save();
                            }
                        } else {
                            $InvProducto->cantidad = floatval($InvProducto->cantidad) +  floatval($item_detalle->cantidad);
                            $InvProducto->save();
                        }
                    } else {
                        // EL PRODUCTO NO SE ENCUENTRA EN INVENTARIO
                        $InvProductoSucursal  =  new InvProductoSucursal();
                        $InvProductoSucursal->sucursal_id   = $origen;
                        $InvProductoSucursal->producto_id   = $item_detalle->producto_id;
                        $InvProductoSucursal->cantidad      = $item_detalle->cantidad;
                        $InvProductoSucursal->save();
                    }


                    TransProductoInventario::saveTransVenta($origen, $item_detalle->id, $item_detalle->producto_id, $item_detalle->cantidad, TransProductoInventario::TIPO_ENTRADA);
                }
            }


            if ($is_add) {
                $venta->status = Venta::STATUS_CANCEL;
                $venta->nota_cancelacion = $request["Venta"]["nota"];

                if ($venta->update()) {
                    Yii::$app->session->setFlash('success', "SE REALIZO CORRECTAMENTE LA CANCELACION DE LA VENTA ");
                    return $this->redirect([
                        'view',
                        'id' => $venta->id
                    ]);
                }
            }

            Yii::$app->session->setFlash('warning', 'NO SE PUEDE CANCEL LA VENTA, EL PAGO RELACCIONADO CORRESPONDE A MULTIPLES NOTAS DE VENTAS ó MULTIPLES METODOS DE PAGO - [GENERA UNA DEVOLUCIÓN]');
            return $this->redirect([
                'view',
                'id' => $venta->id
            ]);
        }

        Yii::$app->session->setFlash('danger', 'Verifica tu información, no se pudo realizar la operación');

        return $this->redirect(['index']);
    }

    public function actionProductoAjax($q = false)
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

            $user = ViewProducto::getProductoSeachAjax($text);
            // Obtenemos user


            // Devolvemos datos YII2 SELECT2
            if ($q) {
                return $user;
            }

            // Devolvemos datos CHOSEN.JS
            $response = ['q' => $text, 'results' => $user];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
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
            // Obtenemos user


            // Devolvemos datos YII2 SELECT2
            if ($q) {
                return $user;
            }

            // Devolvemos datos CHOSEN.JS
            $response = ['q' => $text, 'results' => $user];

            return $response;
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionGetProducto()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {

            if ($request->get('producto_id')) {

                $producto  = Producto::findOne(trim($request->get('producto_id')));
                return [
                    "code" => 202,
                    "producto" => [
                        "id" => $producto->id,
                        "producto" => $producto->nombre,
                        "tipo_text" => $producto->unidadMedida ? $producto->unidadMedida->nombre : '--',
                    ],
                ];
            }

            return [
                "code" => 10,
                "message" => "Error al buscar el producto, intenta nuevamente",
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }

    public function actionTipoCambioAjax()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {
            return [
                "code" => 202,
                "tipoCambio" => TipoCambio::getUltimoTipoCambio()
            ];
        }
        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }


    public function actionPostFactura($venta_id)
    {
        $venta = Venta::findOne($venta_id);

        $params = [
            'Serie' => '01',
            'Folio' => $venta->id,
            // 'Date' => '2022-03-30',
            'PaymentForm' => '01',
            'PaymentConditions' => 'CREDITO A SIETE DIAS',
            'Currency' => 'MXN',
            'CfdiType' => 'I',
            'PaymentMethod' => 'PUE',
            'ExpeditionPlace' => '72160',
            "Receiver" => [
                "Rfc"=> "XAXX010101000",
                "Name"=> "PUBLICO EN GENERAL",
                "CfdiUse"=> "S01",
                "FiscalRegime"=> "616",
                "TaxZipCode" => 72160
            ],
            'Items' => [],
            "GlobalInformation" => [
                "Periodicity"=> "01",
                "Months"=> "08",
                "Year"=> 2025
            ]
        ];

        $item = [];
        $rate = 0.160000;

        $scale = 6;


        // Convertir a número con 6 decimales para el JSON
        $toNumber = function($s) use ($scale) {
            // number_format garantiza 6 decimales; cast a float deja numero (JSON lo tomará como number)
            return (float) number_format((float) $s, $scale, '.', '');
        };


        foreach ($venta->ventaDetalle as $key => $itemVenta) {

            $subtotal = $toNumber( MathUtils::mul($itemVenta->precio_venta, $itemVenta->cantidad));
            $iva = $toNumber(MathUtils::mul($subtotal, $rate));


            array_push($item, 
                [
                    'ProductCode' => $itemVenta->producto->clave_sat,
                    'IdentificationNumber' => $itemVenta->producto->clave,
                    'Description' => $itemVenta->producto->nombre,
                    'Unit' => 'Pieza',
                    'UnitCode' => 'H87',
                    'UnitPrice' => $itemVenta->precio_venta,
                    'Quantity' => $itemVenta->cantidad,
                    'Subtotal' =>  $subtotal,
                    'TaxObject' => '02',
                    'Taxes' => [
                        [
                            'Base' =>  $subtotal,
                            'Rate' =>  $rate,
                            'Total'=>  $iva,
                            'Name' => 'IVA',
                            'IsRetention' => false,
                        ],
                    ],
                    'Total' => $toNumber( MathUtils::add($subtotal,$iva))
                ]);
        }

        $params['Items'] = $item;
        
        // $factura = Yii::$app->facturama->createInvoice($params);
        // return $this->asJson($factura);
        
        try {
            
            $connection = \Yii::$app->db;
            $transaction = $connection->beginTransaction();
            
            $factura = Yii::$app->facturama->createInvoice($params);

            if($factura->Status === 'active' ){
                
                $connection->createCommand()
                ->insert('factura', [
                        'facturaid'        => $factura->Id,
                        'uuid'             => $factura->Complement->TaxStamp->Uuid,
                        'venta_id'         => $venta->id,
                        'monto'            => $venta->total,
                        'tipo'             => 'I',
                        'xml'              => '',
                        'pdf'              => '',
                        'created_at'       => time(),
                ])->execute();            
    
                $transaction->commit();
                
                Yii::$app->session->setFlash('success', 'SE GENERO CORRECTAMENTE LA FACTURA');

            }else{

                Yii::$app->session->setFlash('danger', 'Info' .  json_encode($factura));
            }
    
        
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('danger', 'Error : '. $e->getMessage());
        }

        return $this->redirect(['view', 'id' => $venta->id]);
    }
    
    public function actionGetFactura($venta_id)
    {
          $query = (new Query())
            ->select([
                'facturaid'
            ])
            ->from('factura')
            ->andWhere(['venta_id' => $venta_id ])
            ->one();
        try {
            $id = $query['facturaid'];
            $pdf = Yii::$app->facturama->downloadInvoice('pdf', $id);
            // $myfile = fopen('factura'.$id.'.'.'pdf', 'a+');
            // fwrite($myfile, base64_decode(end($pdf)));
            // fclose($myfile);
            // printf('<pre>%s<pre>', var_export(true));

            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            Yii::$app->response->headers->add('Content-Type', 'application/pdf');
            Yii::$app->response->headers->add('Content-Disposition', "attachment; filename=factura-{$id}.pdf");

            return  base64_decode(end($pdf));
        } catch (\Exception $e) {
            return $this->asJson(['error' => $e->getMessage()]);
        }
    }

    
 

    // public function actionPostCancelacionVenta()
    // {

    //     $request = Yii::$app->request->post();
    //     Yii::$app->response->format = Response::FORMAT_JSON;
    //     if (isset($request["venta_id"]) && $request["venta_id"] && isset($request["sucursal_id"]) && $request["sucursal_id"]) {
    //         if (Venta::setCancelacionVentaRuta($request["venta_id"], $request["sucursal_id"], $request["nota_cancelacion"])) {
    //             return [
    //                 "code"   => 202,
    //                 "message" =>  "Se cancelo correctamente",
    //             ];
    //         }
    //     }

    //     return [
    //         "code" => 10,
    //         "message" => "Ocurrio un error, intenta nuevamente.",
    //     ];
    // }
 

    // public function actionVentaInfo()
    // {

    //     $request = Yii::$app->request;
    //     Yii::$app->response->format = Response::FORMAT_JSON;
    //     // Cadena de busqueda
    //     if ($request->validateCsrfToken() && $request->isAjax) {

    //         if ($request->get('venta_id')) {

    //             $venta      = Venta::findOne(trim($request->get('venta_id')));
    //             $infVenta   = Venta::ventaInfo(trim($request->get('venta_id')));
    //             //$invenRuta  = Reparto::getInventario($venta->reparto_id);

    //             return [
    //                 "code"          => 202,
    //                 "venta"         => $infVenta,
    //                 "inventario"    => InvProductoSucursal::getStockRutaObject($venta->reparto->sucursal_id),
    //             ];
    //         }

    //         return [
    //             "code" => 10,
    //             "message" => "Error al buscar el producto, intenta nuevamente",
    //         ];
    //     }
    //     throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    // }


    //------------------------------------------------------------------------------------------------//
    // BootstrapTable list
    //------------------------------------------------------------------------------------------------//
    /**
     * Return JSON bootstrap-table
     * @param  array $_GET
     * @return json
     */
    public function actionVentasJsonBtt()
    {
        return ViewVenta::getJsonBtt(Yii::$app->request->get());
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
                $model = Venta::findOne($name);
                break;

            case 'view':
                $model = ViewVenta::findOne($name);
                break;
        }

        if ($model !== null)
            return $model;

        else
            throw new NotFoundHttpException('La página solicitada no existe.');
    }




    ############################################################################################################################################################################
    #                                              VENTA MOSTRADOR 
    ############################################################################################################################################

    public function actionMenuVenta()
    {

        return $this->render('mostrador/menu', [
            'model' => new Venta(),
        ]);
    }


    public function actionCreate()
    {
        if (AperturaCaja::getAperturaActual()) {
            return $this->render('mostrador/venta', [
                //'model' => $model,
            ]);
        }
        Yii::$app->session->setFlash('danger', 'No se puede realizar una venta, no hay una apertura de caja activa.');
        return $this->redirect(['apertura-caja/index']);
    }

    public function actionAjaxClientes()
    {
        $request = Yii::$app->request;
        $response = [
            'data' => [],
            'message' => 'No se encontraron resultados',
        ];

        Yii::$app->response->format = Response::FORMAT_JSON;
        // Cadena de busqueda
        if ($request->validateCsrfToken() && $request->isAjax) {
            $q = trim($request->get('q'));

            //return $q;
            $clientes = Cliente::find()
                ->select(['id', 'nombre'])
                //->where(['like', 'nombre', $q])
                ->where([
                    'or',
                    ['like', 'nombre', $q],
                    ['like', 'telefono', $q],
                    ['like', 'email', $q],
                    ['like', 'rfc', $q],
                ])
                ->asArray()
                ->all();
            //return $clientes;

            if ($clientes) {
                $response = [
                    'data' => $clientes,
                    'message' => 'Resultados encontrados -',
                ];


                return $response;
            }
            return [
                'data' => [],
                'message' => 'No se encontraron resultados --',
            ];
        }

        return $response;
    }
    public function actionAjaxProductos()
    {
        $request = Yii::$app->request;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($request->isAjax && $request->validateCsrfToken()) {
            $q = trim($request->post('q'));
            $clienteId = $request->post('cliente_id');
            $sucursalId = Yii::$app->user->identity->sucursal_id;
            $sucursalId = 1;

            if ($q) {
                return Producto::get_productos_venta($q, $sucursalId, $clienteId);
            }
        }

        throw new BadRequestHttpException('Solo se soporta peticiones AJAX');
    }


    public function actionGuardarVenta()
    {
        $post = Yii::$app->request->post();
        $sucursal_id = Yii::$app->user->identity->sucursal_id ?? 1;


        if ($post) {

            $cliente_id = $post['cliente_id'] ?? null;
            $carrito = $post['carrito'] ?? null;
            $pagos = $post['pagos'] ?? null;
            $model = new Venta();

            $total = 0;
            $iva_global = 0;
            $ieps_global = 0;
            $total_impuestos = 0;
            foreach ($carrito as $item) {
                $total += $item['precio'] * $item['cantidad'];
                $iva_global += $item['iva'] * $item['cantidad']; // Suponiendo un IVA del 16%
                $ieps_global += $item['ieps'] * $item['cantidad']; // Suponiendo un IEPS del 8%
            }
            $total_impuestos = $iva_global + $ieps_global + $total;

            $model->sucursal_id = $sucursal_id;
            $model->cliente_id = $cliente_id;
            $model->moneda = $post['moneda'] ?? Venta::MONEDA_MXN;
            $model->tipo_cambio = $post['tipo_cambio'] ?? 1;
            $model->nota_cancelacion = $post['observaciones'] ?? null;
            $model->tipo = Venta::TIPO_GENERAL;
            $model->is_tpv_ruta = Venta::IS_TPV_RUTA_OFF;
            $model->is_especial = Venta::VENTA_GENERAL;

            $model->status = Venta::STATUS_VENTA;
            $model->total = $total_impuestos;
            $model->iva = $iva_global;
            $model->ieps = $ieps_global;
            $model->subtotal = $total;

            if ($model->save()) {
                foreach ($carrito as $item) {
                    $InvProducto = InvProductoSucursal::find()->andWhere(["and", ["=", "sucursal_id", $sucursal_id], ["=", "producto_id", $item['id']]])->one();
                    $InvProducto->cantidad = $InvProducto->cantidad - $item['cantidad'];
                    $InvProducto->save();
                    $venta_producto = new VentaDetalle();
                    $venta_producto->venta_id = $model->id;
                    $venta_producto->producto_id = $item['id'];
                    $venta_producto->cantidad = $item['cantidad'];
                    $venta_producto->precio_venta = $item['precio'];
                    $venta_producto->iva = $item['iva'] * $item['cantidad'];
                    $venta_producto->ieps = $item['ieps'] * $item['cantidad'];
                    $venta_producto->precio_impuestos = $item['precio'] + $item['iva'] + $item['ieps']; // Suponiendo un IVA del 16% y un IEPS del 8%
                    $venta_producto->is_reparto_entrega = 20;
                    $venta_producto->is_conversion = 20;
                    $venta_producto->apply_bodega = 20;
                    $venta_producto->save();
                }
                foreach ($pagos as $pago) {
                    $cobroVenta = new CobroVenta();
                    $cobroVenta->venta_id = $model->id;
                    $cobroVenta->metodo_pago = $pago['metodo'];
                    $cobroVenta->cantidad = $pago['monto'];
                    $cobroVenta->banco = $pago['banco'];
                    $cobroVenta->cuenta = $pago['cuenta'];
                    $cobroVenta->tipo = CobroVenta::TIPO_VENTA;
                    $cobroVenta->tipo_cobro_pago = CobroVenta::PERTENECE_COBRO;
                    $cobroVenta->cantidad_pago = $total;
                    $cobroVenta->save();
                    if ((int)$pago['metodo'] == CobroVenta::COBRO_CREDITO) {
                        #$model->fecha_credito = isset($model["fecha_liquidacion"]) ? strtotime($model["fecha_liquidacion"]) : time();
                        $Credito = new  Credito();
                        $Credito->venta_id  = $model->id;
                        $Credito->monto      = $pago['monto'];
                        $Credito->fecha_credito = isset($model["fecha_credito"]) ? strtotime($model["fecha_credito"]) : time();
                        $Credito->tipo       = CobroVenta::PERTENECE_COBRO;
                        $Credito->created_by = Yii::$app->user->identity->id;
                        $Credito->save();
                    }
                }
                Yii::$app->session->setFlash('success', 'Venta guardada correctamente.');
                return $this->asJson([
                    'success' => true,
                    'id' => $model->id,
                ]);
            }
            return $this->asJson([
                'success' => false,
                'errors' => $model->getErrors(),
            ]);
        }
    }
}

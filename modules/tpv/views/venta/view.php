<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\widgets\CreatedByView;
use app\models\venta\Venta;
use app\models\venta\VentaDetalle;
use app\models\producto\Producto;
use app\models\cobro\CobroVenta;


$this->title = "Folio #" . str_pad($model->id, 6, "0", STR_PAD_LEFT);

$this->params['breadcrumbs'][] = ['label' => 'Remisiones', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->id;
$cobroTotal = 0;
?>

<?php if(!Venta::applyFactura($model->id)) : ?>
<p><?= Html::a("Generar factura", ['post-factura', 'venta_id' => $model->id], [ 'class' => 'btn btn-success']) ?></p>
<?php else : ?>
    <?= Html::a("Descargar factura", ['get-factura', 'venta_id' => $model->id], [ 'class' => 'btn btn-success']) ?>
<?php endif ?>


<div class="tpv-pre-captura-view">
    <div class="alert alert-warning">
        <h5><?= Venta::$statusList[$model->status] ?></h5>
    </div>
    <div class="row">
        <div class="col-md-9">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Información de cliente</h5>
                </div>
                <div class="ibox-content">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'CLIENTE',
                                'format'    => 'raw',
                                'value'     =>  isset($model->cliente->id) ?  Html::a($model->cliente->nombre . " " . $model->cliente->apellidos, ['/crm/cliente/view', 'id' => $model->cliente->id], ['class' => 'text-primary']) : ' *** PUBLICO EN GENERAL **',
                            ]
                        ],
                    ]) ?>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body text-center">
                    <div class="row">
                        <div class="col">
                            <span class="h3 font-bold m-t block"> <?= $model->moneda ?></span>
                            <small class="h5  m-b block">MONEDA</small>
                        </div>
                        <?php if (strtoupper($model->moneda) !== 'MXN'): ?>
                        <div class="col">
                            <div class=" m-l-md">
                                <span class="h3 font-bold m-t block"> <?= number_format($model->tipo_cambio, 2)  ?></span>
                                <small class="h5 m-b block">TIPO DE CAMBIO</small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>



                <div class="panel-body text-center">
                    <div class="row">
                        <div class="col">
                            <span class="h3 font-bold m-t block"> <?= $model->getTotalUnidades()  ?></span>
                            <small class="h5  m-b block">UNIDADES</small>
                        </div>
                        <div class="col">
                            <div class=" m-l-md">
                                <span class="h3 font-bold m-t block">$ <?= number_format($model->subtotal, 2)  ?></span>
                                <small class="h5 m-b block">SUBTOTAL VENTA</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class=" m-l-md">
                                <span class="h3 font-bold m-t block">$ <?= number_format($model->iva, 2)  ?></span>
                                <small class="h5 m-b block">IVA</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class=" m-l-md">
                                <span class="h3 font-bold m-t block">$ <?= number_format($model->ieps, 2)  ?></span>
                                <small class="h5 m-b block">IEPS</small>
                            </div>
                        </div>
                        <div class="col">
                            <div class=" m-l-md">
                                <span class="h3 font-bold m-t block">$ <?= number_format($model->total, 2)  ?></span>
                                <small class="h5 m-b block">TOTAL VENTA</small>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
 
            <div class="ibox">
                <div class="ibox-title">
                    <h3>Cobros realizado</h3>
                </div>
                <div class="ibox-content">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Tipo</th>
                                <th style="text-align: center;">Metodo de pago</th>
                                <th style="text-align: center;">Cobro</th>

                            </tr>
                        </thead>
                        <tbody style="text-align: center;">
                        <tbody>

                            <?php foreach ($model->cobroTpvVenta as $key => $item): ?>
                                <tr>
                                    <td class="text-center"><?= CobroVenta::$tipoList[$item->tipo] ?></td>
                                    <td class="text-center">
                                        <?= CobroVenta::$servicioTpvList[$item->metodo_pago] ?>
                                        <?php if ( $item->metodo_pago != CobroVenta::COBRO_CREDITO ): ?>
                                            <p><strong style="font-size: 16px;color: #000;">Banco [ <?= $item->banco ?> / Cuenta <?= $item->cuenta ?> ]</strong></p>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center"><?= number_format($item->cantidad, 2) ?></td>

                                    <?php $cobroTotal =  $cobroTotal + $item->cantidad; ?>

                                </tr>
                            <?php endforeach ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td style="font-size: 17px" class="text-right text-main text-semibold " colspan="2">TOTAL</td>
                                <td style="font-size: 17px"><?= number_format($model->total, 2) ?></td>
                            </tr>
                            <tr>
                                <td style="font-size: 17px" class="text-right text-main text-semibold " colspan="2">PAGADO</td>
                                <td style="font-size: 17px"><?= number_format($cobroTotal, 2) ?></td>
                            </tr>
                        </tfoot>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-title">
                    <h3>Productos relacionados</h3>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-bordered invoice-summary">
                            <thead>
                                <tr class="bg-trans-dark">

                                    <th class="min-col text-center text-uppercase">PRODUCTO</th>
                                    <th class="min-col text-center text-uppercase">CANTIDAD</th>
                                    <th class="min-col text-center text-uppercase">U.M</th>
                                    <th class="min-col text-center text-uppercase">IVA</th>
                                    <th class="min-col text-center text-uppercase">IEPS</th>
                                    <th class="min-col text-center text-uppercase">COSTO</th>

                                </tr>
                            </thead>
                            <tbody style="text-align: center;">
                                <?php foreach ($model->ventaDetalle as $key => $item): ?>
                                    <tr>
                                        <td><?= Html::a($item->producto->nombre . "[" . $item->producto->clave . "]", ["/inventario/arqueo-inventario/view", "id" => $item->producto_id], ["class" => "", "target" => "_blank"])  ?> </td>
                                        <td><?= $item->cantidad  ?> </td>
                                        <td><?= $item->producto->unidadMedida->nombre ?? '--'  ?> </td>
                                        <td><?= $item->iva ? number_format($item->iva, 2) : 0 ?> </td>
                                        <td><?= $item->ieps ? number_format($item->ieps, 2) : 0 ?> </td>
                                        <td><?= $item->precio_venta ? number_format($item->precio_venta * $item->cantidad, 2) : 0 ?> </td>
                                    </tr>

                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel" style="background-color: #cbb70e;color: #fff;">
                <div class="panel-body text-center ">
                    <div class="row">
                        <div class="col">
                            <div class=" m-l-md">
                                <span class="h5 font-bold m-t block"> <i class="fa fa-cubes"></i> <?= $model->sucursalVende->nombre  ?></span>
                                <small class="  block">VENTA</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <iframe width="100%" class="panel" height="500px" src="<?= Url::to(['imprimir-pagare-ticket', 'id' => $model->id])  ?>"></iframe>

            <div class="panel">
                <?= Html::a('IMPRIMIR REMISION', false, ['class' => 'btn btn-warning btn-lg btn-block', 'id' => 'imprimir-ticket', 'style' => '    padding: 6%;']) ?>
            </div>
 

            <?php if ($can["cancel"]): ?>
                <div class="panel">
                    <?= Html::button('CANCELAR VENTA',  ['class' => 'btn btn-danger btn-lg btn-block', "data-target" => "#modal-cancelacion", "data-toggle" => "modal", 'style' => '    padding: 6%;']) ?>
                </div>
            <?php endif ?>

            <?= app\widgets\CreatedByView::widget(['model' => $model]) ?>
        </div>
    </div>
</div>




<?= Html::a("Nueva venta", ['/tpv/venta/create'], ["class" => "btn", "style" => "position: fixed;
    top: 0;
    right: 5%;
    font-size: 15px;
    z-index: 100000;
    border-radius: 50%;
    width: 100px;
    height: 100px;
    background: #8c45ce;
    color: #ffffff;
    font-weight: 800;
    box-shadow: 3px 5px 5px black;
    padding: 25px;
    border-color: #ab30e0;"]) ?>



 
<script>
    var venta_id = <?= $model->id ?>;

    $(function() {
        $('body').addClass('mini-navbar');
    });

    $('#imprimir-ticket').click(function(event) {
        event.preventDefault();
        window.open("<?= Url::to(['imprimir-pagare-ticket', 'id' => $model->id])  ?>",
            'imprimir',
            'width=600,height=500');
    });
 
</script>

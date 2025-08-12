<?php
use yii\helpers\Html;
use yii\helpers\Url;
use Da\QrCode\QrCode;
use app\models\venta\Venta;
use app\models\venta\VentaDetalle;
use app\models\sucursal\Sucursal;
use app\models\esys\EsysSetting;
use app\models\cobro\CobroVenta;
use app\models\Esys;
use app\models\producto\Producto;


$color      = "#OOOOOO";
$colorGris  = "#D8D8D8";


$qrCode = (new QrCode($id))
            ->setSize(100)
            ->setMargin(2)
            ->setErrorCorrectionLevel('medium');
$code = [];

$code['qrBase64'] =  $qrCode->writeDataUri();


$array_cliente_id   =  [];
$suma_asegurada     = 0;
$total_pieza        = 0;
$pesoPAQUETE        = 0;

$total_operacion    = 0;
foreach ($model as $key => $item_venta) {
    $total_operacion = $total_operacion + $item_venta->total;
}
?>
<body style="font-family: 'Segoe UI', Arial, sans-serif; background: #fff; color: #222; margin: 0; padding: 0;">
    <table style="max-width: 350px; margin: 0 auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 18px 12px 8px 12px; font-size: 12px;">
        <tr>
            <td colspan="2" align="center" style="padding-bottom: 8px;">
                <img src="https://grupofertigar.com/wp-content/uploads/2023/10/GFP-Color-02.png" height="90" alt="GRUPO FERTIGA" style="max-width:120px; margin-bottom:6px;">
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center" style="font-size: 1.2em; font-weight: bold; letter-spacing: 2px; color: #1a1a1a; padding-bottom: 4px;">Ticket #<?= str_pad($id,6,"0",STR_PAD_LEFT) ?></td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 0.98em; padding-bottom: 2px;">
                <span style="font-weight:bold; color:#333;">Fecha:</span> <?= Esys::fecha_en_texto($model[0]->created_at,true) ?><br>
                <span style="font-weight:bold; color:#333;">Cajero:</span> <?= isset($model[0]->updatedBy->nombreCompleto) ?$model[0]->updatedBy->nombreCompleto : $model[0]->createdBy->nombreCompleto ?><br>
                <span style="font-weight:bold; color:#333;">Cliente:</span> <?= isset($model[0]->cliente->id) ?  $model[0]->cliente->nombreCompleto: 'PUBLICO EN GENERAL'  ?>
            </td>
        </tr>
        <tr><td colspan="2" style="border-top: 1px dashed #bbb; padding-top: 8px;"></td></tr>
        <tr><td colspan="2" style="padding:0;">
            <table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
                <thead>
                    <tr>
                        <th style="text-align:left; font-size:12px; border-bottom:1px solid #e0e0e0; background:#f5f5f5;">Producto</th>
                        <th style="text-align:center; font-size:12px; border-bottom:1px solid #e0e0e0; background:#f5f5f5;">Cant.</th>
                        <th style="text-align:right; font-size:12px; border-bottom:1px solid #e0e0e0; background:#f5f5f5;">P.U.</th>
                        <th style="text-align:right; font-size:12px; border-bottom:1px solid #e0e0e0; background:#f5f5f5;">Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($model_detalle_venta as $key => $item): ?>
                    <tr>
                        <td style="font-weight:500; color:#222;">
                            <?= isset( $item->producto->id) ?  $item->producto->nombre : ''  ?>
                            <?php if ( $item->apply_bodega == VentaDetalle::APPLY_BODEGA_ON ): ?>
                                <br><span style="font-size:10px;color:#c00;font-weight:bold;">*** ABASTECE BODEGA ***</span>
                            <?php endif ?>
                        </td>
                        <td style="text-align:center;">
                            <?= $item->cantidad   ?>
                            <br><span style="font-size:11px; color:#888;">
                                <?= isset($item->producto->unidadMedida) && isset($item->producto->unidadMedida->clave) ? $item->producto->unidadMedida->clave : 'N/A' ?>
                            </span>
                        </td>
                        <td style="text-align:right;">$<?= number_format($item->precio_venta,2)   ?></td>
                        <td style="text-align:right;">$<?= number_format($item->precio_venta * $item->cantidad,2)  ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </td></tr>
        <tr><td colspan="2" style="border-top:2px solid #222;"></td></tr>
        <tr>
            <td colspan="2" align="right" style="font-size: 1.1em; font-weight: bold; color:#000; padding: 5px 0 2px 0;">TOTAL DE VENTA: $<?= number_format($total_operacion,2) ?></td>
        </tr>
        <tr><td colspan="2" align="center" style="font-size: 1em; font-weight: bold; color:#000; padding: 5px 0 2px 0;">RESUMEN DE PAGOS</td></tr>
        <tr><td colspan="2" style="padding:0;">
            <table style="width:100%; border-collapse:collapse;">
                <tbody>
                <?php foreach ($model[0]->cobroTpvVenta as $key => $cobro): ?>
                    <tr>
                        <td colspan="2" style="text-align:left; font-size:12px;">
                            <?= CobroVenta::$servicioTpvList[$cobro->metodo_pago] ?>
                            <?php if ($cobro->metodo_pago == CobroVenta::COBRO_OTRO ): ?>
                                <br><span style="font-size:11px; color:#888;">CONCEPTO: <?= $cobro->nota_otro ?></span>
                            <?php endif ?>
                            <?php if ($cobro->metodo_pago == CobroVenta::COBRO_TARJETA_DEBITO || $cobro->metodo_pago == CobroVenta::COBRO_TARJETA_CREDITO ): ?>
                                <br><span style="font-size:11px; color:#888;">CARGO EXTRA: $<?= $cobro->cargo_extra ?></span>
                            <?php endif ?>
                        </td>
                        <td style="text-align:right; font-size:12px; font-weight:bold; color:#000;">$<?= number_format($cobro->cantidad,2) ?></td>
                        <td style="text-align:right; font-size:11px; color:#888;">
                            <?php /*if ($cobro->metodo_pago == CobroVenta::COBRO_EFECTIVO): ?>
                                Pago: $<?= number_format($cobro->cantidad_pago,2) ?><br>Cambio: $<?= number_format($cobro->cantidad_pago-$cobro->cantidad,2) ?>
                            <?php endif */?>
                        </td>
                    </tr>
                <?php endforeach ?>
                <?php if ($model[0]->transaccion): ?>
                    <?php foreach ($model[0]->transaccion as $key => $item_transaccion): ?>
                        <?php foreach (CobroVenta::getVentaRutaOperacion($item_transaccion->token_pay) as $key => $item_cobro): ?>
                            <tr>
                                <td colspan="2" style="text-align:left; font-size:12px;">
                                    <?= CobroVenta::$servicioTpvList[$item_cobro->metodo_pago] ?>
                                </td>
                                <td style="text-align:right; font-size:12px; font-weight:bold; color:#000;">$<?= number_format($item_cobro->cantidad,2) ?></td>
                                <td></td>
                            </tr>
                        <?php endforeach ?>
                    <?php endforeach ?>
                <?php endif ?>
                </tbody>
            </table>
        </td></tr>
        <tr><td colspan="2" align="center" style="padding:10px 0 0 0;">
            <img src="<?= $code['qrBase64'] ?>" alt="QR" width="80" height="80" style="display:block; margin:0 auto;">
        </td></tr>
        <tr><td colspan="2" align="center" style="font-family: Georgia, serif; line-height: 15px; font-size:13px; color:#666; padding-top:10px;">
            Consulta términos y condiciones en <strong>grupofertigar.com</strong>
        </td></tr>
        <tr><td colspan="2" style="padding:10px 0 0 0;"><hr style="border:none; border-top:1.5px dashed #bbb; margin:0;"></td></tr>
    </table>
</body>
    <table width="100%">
        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>
        <tr>
            <td colspan="2" align="right" style=" font-size: 16px; padding: 5px"><strong style="font-weight: bold;color:#000">TOTAL DE VENTA: $<?= number_format($total_operacion,2) ?></strong></td>
        </tr>
        <tr>
            <td colspan="2" align="center" style=" font-size: 16px; padding: 5px"><strong style="font-weight: bold;color:#000">RESUMEN</strong></td>
        </tr>

        <?php foreach ($model[0]->cobroTpvVenta as $key => $cobro): ?>
            <tr>
                <td width="80%" style="border: none; text-align: right;">

                    <p><strong style="font-size: 16px;color: #000;"><?= CobroVenta::$servicioTpvList[$cobro->metodo_pago] ?></strong></p>
                    <?php if ($cobro->metodo_pago == CobroVenta::COBRO_OTRO ): ?>
                        <p><strong style="font-size: 16px;color: #000;">CONCEPTO [ <?= $cobro->nota_otro ?> ]</strong></p>
                    <?php endif ?>

                    <?php if ($cobro->metodo_pago == CobroVenta::COBRO_TARJETA_DEBITO || $cobro->metodo_pago == CobroVenta::COBRO_TARJETA_CREDITO ): ?>
                        <p><strong style="font-size: 16px;color: #000;">CARGO EXTRA [ $<?= $cobro->cargo_extra ?> ]</strong></p>
                    <?php endif ?>
                    <?php if ($cobro->metodo_pago == CobroVenta::COBRO_EFECTIVO): ?>
                        <p><strong style="font-size: 16px;color: #000;">Pago con </strong></p>
                        <p><strong style="font-size: 16px;color: #000;">Cambio </strong></p>
                    <?php endif ?>
                </td>
                <br>
                <td width="20%" style="border: none; text-align: right;">
                   <p style="font-weight: bold; font-size: 16px; color: #000;">$<?= number_format($cobro->cantidad,2) ?>

                   </p>
                   <?php if ($cobro->metodo_pago == CobroVenta::COBRO_EFECTIVO): ?>
                        <p style="font-weight: bold; font-size: 16px; color: #000;">$<?= number_format($cobro->cantidad_pago,2) ?>
                        </p>
                        <p style="font-weight: bold; font-size: 16px; color: #000;">$<?= number_format($cobro->cantidad_pago-$cobro->cantidad,2) ?>
                        </p>
                    <?php endif ?>
                </td>
                <br>
            </tr>
        <?php endforeach ?>

        <?php if ($model[0]->transaccion): ?>
            <?php foreach ($model[0]->transaccion as $key => $item_transaccion): ?>
                <?php foreach (CobroVenta::getVentaRutaOperacion($item_transaccion->token_pay) as $key => $item_cobro): ?>
                    <tr>
                        <td width="80%" style="border: none; text-align: right;">
                            <p><strong style="font-size: 16px;color: #000;"><?= CobroVenta::$servicioTpvList[$item_cobro->metodo_pago] ?></strong></p>
                        </td>
                        <br>
                        <td width="20%" style="border: none; text-align: right;">
                           <p style="font-weight: bold; font-size: 16px; color: #000;">$<?= number_format($item_cobro->cantidad,2) ?></p>
                        </td>
                        <br>
                    </tr>
                <?php endforeach ?>
            <?php endforeach ?>
        <?php endif ?>
    </table>
    <table width="100%">


        <tr style="padding: 0">
            <td colspan="2" style="padding: 0">
                <hr  style="font-size: 15px; font-weight: bold; height: 5px; background-color: black; color: black">
            </td>
        </tr>

        <tr>
            <td colspan="2"></td>
        </tr>

        <tr>
          <td colspan="2" align="center" style="font-family: Georgia, serif; line-height: 15px; font-size:16px;">
                Consulta terminos y condiciones en <strong></strong>
          </td>
        </tr>

        <tr>
            <td colspan="2"><br><br><br><br></td>
        </tr>
        <tr>
            <td colspan="2">
                <hr>
            </td>
        </tr>

       
        <tr>
        </tr>
    </table>

</body>




<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\venta\Venta;
use app\models\venta\VentaDetalle;
use app\models\producto\Producto;
use app\models\Esys;

$folio = isset($id) ? $id : '000061';
$fecha = isset($model[0]->created_at) ? Esys::fecha_en_texto($model[0]->created_at,true) : date('d/M/Y');
$lugar = 'URUAPAN, MICH.';
$beneficiario = 'GRUPO KER AGRO S DE RL DE CV';
$domicilio = 'URUAPAN-CUATRO CAMINOS KM 61 SIN NUMERO';
$telefono = '4524523428';
$email = 'murillogerica496@gmail.com';
$subtotal = $venta_model->subtotal;
$iva = $venta_model->iva;
$ieps = $venta_model->ieps;
$total = $venta_model->total;
$cantidad = $total;
$cantidad_letra = 'sesenta y ocho mil setecientos dieciocho Pesos 32/100 M.N.'; // Puedes generar esto dinámicamente si tienes función
$fecha_pago = $fecha;
$serie = '1';
$serie_total = '1';
$interes_ordinario = '2';
$interes_moratorio = '5';
$tribunal = 'Primer Partido Judicial del Estado de Michoacán';
$fecha_suscripcion = $fecha;

?>
<body style="font-family: Arial, sans-serif; background: #f4f7fb; color: #222; margin: 0; padding: 0;">
	<table style="width: 820px; margin: 30px auto; border:2px solid #2e7d32; border-radius:10px; background: #fff; box-shadow:0 2px 12px #b3c6e0;">
		<tr>
			<td colspan="7" style="padding:0;">
				<div style="height:22px; background:#2e7d32; width:100%; margin-bottom:5px;"></div>
				<div style="padding:0 30px 0 30px;">
					<table style="width:100%; border:none; margin-bottom:8px;">
						<tr>
							<td style="width:60%; vertical-align:top;">
								<span style="font-weight:bold; font-size:18px;">GRUPO KER AGRO S DE RL DE CV</span><br>
								<span style="font-size:14px;"><b>Dirección:</b> URUAPAN-CUATRO CAMINOS KM 61 SIN NUMERO<br>AVIACION</span><br>
								<span style="font-size:14px;"><b>Teléfono:</b> 4524523428</span><br>
								<span style="font-size:14px;"><b>Email:</b> murillogerica496@gmail.com</span>
							</td>
							<td style="width:40%; text-align:right; vertical-align:top;">
								<img src="https://grupofertigar.com/wp-content/uploads/2023/10/GFP-Color-02.png" alt="GRUPO FERTIGA" style="height:60px; margin-bottom:5px;">
							</td>
						</tr>
					</table>
				</div>
				<div style="text-align:right; padding:0 30px 0 0;">
					<div style="font-size:22px; font-weight:bold; letter-spacing:2px; color:#222;">DESGLOSE DE PRODUCTOS</div>
				</div>
			</td>
		</tr>
		<tr><td colspan="7" style="border-bottom:2px solid #bbb;"></td></tr>
		<tr>
			<td colspan="7" style="padding:0 30px 0 30px;">
				<table style="width:100%; border-collapse:collapse; border:1.5px solid #bbb; background:#fafdff;">
					<thead>
						<tr>
							<th style="border-bottom:2px solid #bbb; background:#f5f5f5; padding:7px; text-align:center; color:#222;">CANTIDAD</th>
							<th style="border-bottom:2px solid #bbb; background:#f5f5f5; padding:7px; text-align:left; color:#222;">DESCRIPCIÓN</th>
							<th style="border-bottom:2px solid #bbb; background:#f5f5f5; padding:7px; text-align:center; color:#222;">UNIDAD</th>
							<th style="border-bottom:2px solid #bbb; background:#f5f5f5; padding:7px; text-align:center; color:#222;">IVA</th>
							<th style="border-bottom:2px solid #bbb; background:#f5f5f5; padding:7px; text-align:center; color:#222;">IEPS</th>
							<th style="border-bottom:2px solid #bbb; background:#f5f5f5; padding:7px; text-align:right; color:#222;">P. UNITARIO</th>
							<th style="border-bottom:2px solid #bbb; background:#f5f5f5; padding:7px; text-align:right; color:#222;">IMPORTE</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($model_detalle_venta as $item): ?>
						<tr>
							<td style="text-align:center; padding:5px; border-bottom:1px solid #eee; background:#fff;"> <?= $item->cantidad ?> </td>
							<td style="text-align:left; padding:5px; border-bottom:1px solid #eee; background:#fff;"> <?= isset($item->producto->nombre) ? Html::encode($item->producto->nombre) : '' ?> </td>
							<td style="text-align:center; padding:5px; border-bottom:1px solid #eee; background:#fff;">  <?= isset($item->producto->unidadMedida) && isset($item->producto->unidadMedida->clave) ? $item->producto->unidadMedida->clave : 'N/A' ?> </td>
							<td style="text-align:center; padding:5px; border-bottom:1px solid #eee; background:#fff;">  <?= isset($item->iva) ? $item->iva : 'N/A' ?> </td>
							<td style="text-align:center; padding:5px; border-bottom:1px solid #eee; background:#fff;">  <?= isset($item->ieps) ? $item->ieps : 'N/A' ?> </td>
							<td style="text-align:right; padding:5px; border-bottom:1px solid #eee; background:#fff;"> $<?= number_format($item->precio_venta,2) ?> </td>
							<td style="text-align:right; padding:5px; border-bottom:1px solid #eee; background:#fff;"> $<?= number_format($item->precio_venta * $item->cantidad,2) ?> </td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</td>
		</tr>
		<tr><td colspan="7" style="padding:0 0 10px 0;"></td></tr>
		<tr>
			<td colspan="3"></td>
			<td colspan="2" style="text-align:right; font-weight:bold; color:#2e7d32;">SUBTOTAL:</td>
			<td style="text-align:right; padding-right:20px; color:#2e7d32;">$<?= number_format($subtotal,2) ?></td>
			<td></td>
		</tr>
		<tr>
			<td colspan="3"></td>
			<td colspan="2" style="text-align:right; font-weight:bold; color:#2e7d32;">IVA:</td>
			<td style="text-align:right; padding-right:20px; color:#2e7d32;">$<?= number_format($iva,2) ?></td>
			<td></td>
		</tr>
		<tr>
			<td colspan="3"></td>
			<td colspan="2" style="text-align:right; font-weight:bold; color:#2e7d32;">IEPS:</td>
			<td style="text-align:right; padding-right:20px; color:#2e7d32;">$<?= number_format($ieps,2) ?></td>
			<td></td>
		</tr>
		<tr>
			<td colspan="3"></td>
			<td colspan="2" style="text-align:right; font-weight:bold; font-size:1.1em; color:#2e7d32;">TOTAL:</td>
			<td style="text-align:right; padding-right:20px; font-size:1.1em; font-weight:bold; color:#2e7d32;">$<?= number_format($total,2) ?></td>
			<td></td>
		</tr>
	</table>
</body>

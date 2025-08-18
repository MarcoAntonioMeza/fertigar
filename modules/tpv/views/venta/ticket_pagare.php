<?php

use yii\helpers\Html;
use app\models\Esys;
use Luecano\NumeroALetras\NumeroALetras;


$formatter = new NumeroALetras();
$total_str =  $formatter->toWords($model[0]->total);

$folio = isset($id) ? $id : '000061';
$fecha = isset($model[0]->created_at) ? Esys::fecha_en_texto($model[0]->created_at, true) : date('d/M/Y');
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

$moneda = $model[0]->moneda;

$nombre_cliente = isset($model[0]->cliente->nombre) ? $model[0]->cliente->nombre : 'N/A';
$telefono_cliente = isset($model[0]->cliente->telefono) ? $model[0]->cliente->telefono : 'N/A';
$email_cliente = isset($model[0]->cliente->email) ? $model[0]->cliente->email : 'N/A';
$direccion_cliente = isset($model[0]->cliente) ? $model[0]->cliente->getDireccionCompleta() : 'N/A';
#print_r($model[0]);
#print_r($total_str);

?>

<body style="font-family: Arial, sans-serif; background: #f4f7fb; color: #222; margin: 0; padding: 0; font-size:0.85em;">
	<table style="width:100%;">
		<tr>
			<td style="width:50%; text-align:left; vertical-align:top;">
				<img src="https://grupofertigar.com/wp-content/uploads/2023/10/GFP-Color-02.png" alt="GRUPO FERTIGA" style="height:60px; margin-bottom:5px;">
				<div style="font-weight:bold; font-size:15px; margin-top:8px;"><?= Html::encode($beneficiario) ?></div>
				<div style="font-size:12px;"><b>Dirección:</b> <?= Html::encode($domicilio) ?></div>
				<div style="font-size:12px;"><b>Teléfono:</b> <?= Html::encode($telefono) ?></div>
				<div style="font-size:12px;"><b>Email:</b> <?= Html::encode($email) ?></div>
			</td>
			<td style="width:50%; text-align:left; vertical-align:top;">
				<div style="font-weight:bold; font-size:15px;"><?= Html::encode($nombre_cliente) ?></div>
				<div style="font-size:12px;"><b>Dirección:</b> <?= Html::encode($direccion_cliente) ?></div>
				<div style="font-size:12px;"><b>Teléfono:</b> <?= Html::encode($telefono_cliente) ?></div>
				<div style="font-size:12px;"><b>Email:</b> <?= Html::encode($email_cliente) ?></div>
			</td>
		</tr>
	</table>
	<div style="height:18px; background:#2e7d32; width:100%; margin:15px 0 10px 0;"></div>
	<table style="width:100%; border-collapse:collapse; border:1.2px solid #bbb; background:#fafdff; font-size:0.92em;">
		<thead>
			<tr>
				<th style="border-bottom:1.2px solid #bbb; background:#f5f5f5; padding:4px; text-align:center; color:#222; font-size:0.95em;">CANTIDAD</th>
				<th style="border-bottom:1.2px solid #bbb; background:#f5f5f5; padding:4px; text-align:left; color:#222; font-size:0.95em;">DESCRIPCIÓN</th>
				<th style="border-bottom:1.2px solid #bbb; background:#f5f5f5; padding:4px; text-align:center; color:#222; font-size:0.95em;">UNIDAD</th>
				<th style="border-bottom:1.2px solid #bbb; background:#f5f5f5; padding:4px; text-align:center; color:#222; font-size:0.95em;">IVA</th>
				<th style="border-bottom:1.2px solid #bbb; background:#f5f5f5; padding:4px; text-align:center; color:#222; font-size:0.95em;">IEPS</th>
				<th style="border-bottom:1.2px solid #bbb; background:#f5f5f5; padding:4px; text-align:right; color:#222; font-size:0.95em;">P. UNITARIO</th>
				<th style="border-bottom:1.2px solid #bbb; background:#f5f5f5; padding:4px; text-align:right; color:#222; font-size:0.95em;">IMPORTE</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($model_detalle_venta as $item): ?>
				<tr>
					<td style="text-align:center; padding:3px; border-bottom:1px solid #eee; background:#fff;"><?= $item->cantidad ?></td>
					<td style="text-align:left; padding:3px; border-bottom:1px solid #eee; background:#fff;"><?= isset($item->producto->nombre) ? Html::encode($item->producto->nombre) : '' ?></td>
					<td style="text-align:center; padding:3px; border-bottom:1px solid #eee; background:#fff;"><?= isset($item->producto->unidadMedida) && isset($item->producto->unidadMedida->clave) ? $item->producto->unidadMedida->clave : 'N/A' ?></td>
					<td style="text-align:center; padding:3px; border-bottom:1px solid #eee; background:#fff;"><?= isset($item->iva) ? $item->iva : 'N/A' ?></td>
					<td style="text-align:center; padding:3px; border-bottom:1px solid #eee; background:#fff;"><?= isset($item->ieps) ? $item->ieps : 'N/A' ?></td>
					<td style="text-align:right; padding:3px; border-bottom:1px solid #eee; background:#fff;">$<?= number_format($item->precio_venta, 2) ?></td>
					<td style="text-align:right; padding:3px; border-bottom:1px solid #eee; background:#fff;">$<?= number_format($item->precio_venta * $item->cantidad, 2) ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<table style="width:100%; margin-top:8px; font-size:0.95em;">
		<tr>
			<td style="width:60%;"></td>
			<td style="text-align:right; font-weight:bold; color:#2e7d32;">SUBTOTAL:</td>
			<td style="text-align:right; padding-right:20px; color:#2e7d32;">$<?= number_format($subtotal, 2) ?> <?= $moneda ?></td>
		</tr>
		<tr>
			<td></td>
			<td style="text-align:right; font-weight:bold; color:#2e7d32;">IVA:</td>
			<td style="text-align:right; padding-right:20px; color:#2e7d32;">$<?= number_format($iva, 2) ?> <?= $moneda ?></td>
		</tr>
		<tr>
			<td></td>
			<td style="text-align:right; font-weight:bold; color:#2e7d32;">IEPS:</td>
			<td style="text-align:right; padding-right:20px; color:#2e7d32;">$<?= number_format($ieps, 2) ?> <?= $moneda ?></td>
		</tr>
		<tr>
			<td></td>
			<td style="text-align:right; font-weight:bold; font-size:1.1em; color:#2e7d32;">TOTAL:</td>
			<td style="text-align:right; padding-right:20px; font-size:1.1em; font-weight:bold; color:#2e7d32;">$<?= number_format($total, 2) ?> <?= $moneda ?></td>
		</tr>

	</table>

	<div style="background:#2e7d32; width:100%; margin:12px 0 8px 0; color:#fff; font-weight:bold; font-size:1em; padding:6px 0; text-align:center; text-shadow:0 1px 2px #222; border-radius:4px;">
		IMPORTE CON LETRA: <?= $total_str ?> <?= $moneda ?>
	</div>



	<div style="margin:16px 0 0 0; padding:6px 10px; background:#f8f9fa; border-radius:7px; border:1px solid #bbb; font-size:0.68em; color:#222; line-height:1.25; box-shadow:0 1px 4px #bbb2;" class="text-justify">
		PAGARÉ MERCANTÍL SIN PROTESTO Bo. POR_______________________________________
		<br>
		Pagare No <?= $id ?> URUAPAN, MICH. a <?= $fecha_suscripcion ?>
		<br>
		<br>
		<p> Debo y pagaré incondicionalmente por este pagaré a la orden de <?= $beneficiario ?>, en esta
		ciudad de Uruapan en el domicilio URUAPAN-CUATRO CAMINOS KM 61 SIN NUMERO la cantidad de $<?= number_format($total, 2) ?>
		<?= $total_str ?> <?= $moneda ?>.
		</p>

		<b>Cantidad recibida a mi entera satisfacción, debiendo realizar el pago el día correspondiente.</b><br>
		Valor recibido a mi entera satisfacción. Este pagaré forma parte de una serie numerada del 1 al <b><?= $serie_total ?></b>, y todos están sujetos a la condición de que, al no pagarse cualquiera de ellos a su vencimiento, serán exigibles todos los que le sigan en número, además de los ya vencidos, desde la fecha de vencimiento de este documento hasta el día de su liquidación.<br>
		Este pagaré generará un interés ordinario de <b><?= $interes_ordinario ?>%</b> (<?= strtoupper($formatter->toWords($interes_ordinario)) ?> por ciento) mensual por concepto de interés ordinario por todo el tiempo que permanezca insoluto el adeudo. Igualmente, me obligo a pagar, en caso de mora, un interés moratorio equivalente al <b><?= $interes_moratorio ?>%</b> (<?= strtoupper($formatter->toWords($interes_moratorio)) ?> por ciento) mensual a partir de la fecha en que se constituya en mora y hasta su total liquidación.<br>
		La cantidad resultante de los intereses podrá ser capitalizada de conformidad con el artículo 363 del Código de Comercio.<br>
		Los deudores renuncian al fuero que por razón de su domicilio presente o futuro pudiera corresponderles y se someten a la jurisdicción de los tribunales competentes del <b><?= $tribunal ?></b>.

		<br><br>
		Suscriptor: <?= $nombre_cliente ?>
	</div>

</body>
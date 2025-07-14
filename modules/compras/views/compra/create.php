<?php

$this->title = 'Nueva compra';
$this->params['breadcrumbs'][] = 'NUEVA COMPRA';
$this->params['breadcrumbs'][] = ['label' => 'Compras', 'url' => ['index']];

?>


<div class="sucursales-sucursal-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

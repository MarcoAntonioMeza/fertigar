<?php

use app\models\apertura\AperturaCaja;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\models\Esys;



//$this->title = 'MOSTRADOR';
#$this->params['breadcrumbs'][] = ['label' => 'Ventas', 'url' => ['index']];
?>

<style>
    .tpv-tabs {
        max-width: 900px;
        margin: 40px auto 0 auto;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 4px 32px rgba(0, 0, 0, 0.13);
        padding: 40px 40px 30px 40px;
        min-height: 420px;
    }

    .tpv-tabs .nav-tabs {
        border-bottom: 3px solid #e9ecef;
        margin-bottom: 35px;
        font-size: 1.25em;
    }

    .tpv-tabs .nav-link {
        color: #007bff;
        font-weight: 700;
        border: none;
        border-bottom: 4px solid transparent;
        border-radius: 0;
        transition: all 0.2s;
        font-size: 1.25em;
        padding: 18px 50px 14px 50px;
        letter-spacing: 0.5px;
    }

    .tpv-tabs .nav-link.active {
        color: #fff;
        background: #007bff;
        border-bottom: 4px solid #0056b3;
        border-radius: 12px 12px 0 0;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.08);
    }

    .tpv-tabs .tab-content {
        padding: 30px 0 0 0;
        min-height: 220px;
    }

    .tpv-tabs .tab-pane a {
        display: inline-block;
        margin-top: 18px;
        font-size: 1.25em;
        color: #007bff;
        text-decoration: none;
        border-bottom: 2px dashed #007bff;
        transition: color 0.2s;
        padding-bottom: 2px;
    }

    .tpv-tabs .tab-pane a:hover {
        color: #0056b3;
        border-bottom: 2px solid #0056b3;
    }

    .tpv-tabs .icon {
        font-size: 2.8em;
        margin-bottom: 12px;
        color: #007bff;
        display: block;
    }

    .tpv-tabs .tab-pane h4 {
        font-size: 2em;
        margin-bottom: 18px;
        color: #0056b3;
    }

    .tpv-tabs .tab-pane p {
        font-size: 1.15em;
        color: #444;
    }
</style>

<div class="tpv-tabs">
    <div class="row" style="margin-bottom: 35px;">
        <?php if (AperturaCaja::getAperturaActual()): ?>
            <div class="col-md-6 text-center mb-3 mb-md-0">
                <a href="<?= \yii\helpers\Url::to(['venta/create']) ?>" class="btn btn-success btn-lg" style="font-size:1.3em;padding:18px 50px 18px 50px; border-radius: 10px; box-shadow: 0 2px 8px rgba(40,167,69,0.08);">
                    <span class="icon">🛒</span> Nueva Venta
                </a>
            </div>
        <?php else: ?>
            <div class="col-md-6 text-center mb-3 mb-md-0">
                <a href="javascript:void(0);" class="btn btn-success btn-lg disabled" style="font-size:1.3em;padding:18px 50px 18px 50px; border-radius: 10px; box-shadow: 0 2px 8px rgba(40,167,69,0.08); pointer-events: none; opacity: 0.6;">
                    <span class="icon">🛒</span> Nueva Venta
                </a>
            </div>
        <?php endif; ?>

        <!--<div class="col-md-6 text-center">
            <a href="<?= \yii\helpers\Url::to(['venta/pago-credito']) ?>" class="btn btn-info btn-lg" style="font-size:1.3em;padding:18px 50px 18px 50px; border-radius: 10px; box-shadow: 0 2px 8px rgba(23,162,184,0.08);">
                <span class="icon">💳</span> Pago Crédito
            </a>
        </div>-->
    </div>
    <ul class="nav nav-tabs" id="tpvTab" role="tablist">
        <li class="nav-item" role="presentation" style="width:100%;">
            <a class="nav-link active" id="caja-tab" data-toggle="tab" href="#caja" role="tab" aria-controls="caja" aria-selected="true" style="width:100%;text-align:center;">
                <span class="icon">💵</span> Caja
            </a>
        </li>
    </ul>
    <div class="tab-content" id="tpvTabContent">
        <div class="tab-pane fade show active text-center" id="caja" role="tabpanel" aria-labelledby="caja-tab">
            <?php if (!AperturaCaja::getAperturaActual()): ?>
                <div class="panel panel-default" style="max-width:500px;margin:30px auto 0 auto;box-shadow:0 2px 12px rgba(0,0,0,0.08);border-radius:12px;">
                    <div class="panel-heading" style="background:#007bff;color:#fff;border-radius:12px 12px 0 0;padding:18px 0;">
                        <h3 style="margin:0;">Apertura de Caja</h3>
                    </div>
                    <?php $apertura = ActiveForm::begin(["id" => "formAperturaCaja", "action" => "apertura-caja-create", 'options' => ['style' => 'padding:0 24px 24px 24px;']]) ?>
                    <div class="panel-body" style="padding:28px 0 0 0;">
                        <div class="row">
                            <div class="col-12 text-right">
                                <small><?= Esys::fecha_en_texto(time())  ?></small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-center">
                                <h4 style="margin:10px 0 18px 0;">Cajero: <span style="color:#007bff;"><?= Yii::$app->user->identity->nombre . " " . Yii::$app->user->identity->apellidos ?></span></h4>
                            </div>
                        </div>
                        <div class="row" style="margin-top:18px;">
                            <div class="col-12 text-center">
                                <label for="inputCantidadApertura" style="font-size:1.2em;font-weight:600;">Total en caja</label>
                                <?= Html::input("number", "cantidad_apertura", 0, ["class" => "form-control text-center", "style" => "font-size:2em;max-width:220px;margin:0 auto;display:inline-block;", "id" => "inputCantidadApertura", "min" => 0, "step" => "0.01"]) ?>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer" style="background:transparent;border:none;padding-top:24px;">
                        <div class="row">
                            <div class="col-12 text-center">
                                <?= Html::submitButton('Aperturar Caja', ['class' => 'btn btn-primary btn-lg', "style" => "font-size:1.3em;padding:10px 40px 10px 40px;", "id" => "btnAperturaAdd"]) ?>
                            </div>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            <?php else: ?>

            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Si no tienes Bootstrap JS incluido, agrégalo para que funcionen los tabs -->
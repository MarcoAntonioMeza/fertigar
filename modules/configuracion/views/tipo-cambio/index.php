<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\date\DatePicker;
use app\assets\BootstrapTableAsset;


BootstrapTableAsset::register($this);

$this->title = 'Tipo de cambio';

$this->params['breadcrumbs'][] = 'Ventas';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . " - $this->title - " . date('Y-m-d H.i');
$bttUrl       = Url::to(['tipo-cambio-json-btt']);

?>

<div class='ibox'>
    <div class='ibox-content'>
        <div class="tipo-cambio-index">
            <div class="row padding">
                <div class="col-sm-4">
                    <?= Html::label("FECHA DE TASA", null, ["class" => "control-label"])?>
                    <?=  DatePicker::widget([
                        'name' => 'CatalgoTasa[fecha]',
                        'options' => ['placeholder' => '----/---/--','style' => 'font-size:24px;','onchange' => 'fechaChangeTiie28()', 'id' => 'catalogotasa-fecha'],
                        'value' => date('Y-m-d'),
                        'type' => DatePicker::TYPE_COMPONENT_APPEND,
                        'pickerIcon' => '<i class="fa fa-calendar"></i>',
                        'removeIcon' => '<i class="fa fa-trash"></i>',
                        'language' => 'es',
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                        ]
                    ])?>
                    <br>
                    <?= Html::button("GUARDAR", ["class" => "btn btn-primary btn-block btn-zoom", "id" => "btnSaveTasa"]) ?>
                </div>
                <div class="col-sm-8">
                    <div class="row">
                        <div class="col-sm-5">
                            <?= Html::label("TIPO DE CAMBIO DDLS", null, ["class" => "control-label"])?>
                            <?= Html::input("number",null, null, ["class" => "form-control text-center", "step" =>  '0.01', "style" => "font-size: 24px;font-weight: 700;", 'id' => 'inputTipoCambio' ])?>
                        </div>
                    </div>
                    
                </div>
            </div>
            
            <table class="bootstrap-table">
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    var $catalogoTasaFecha  = $('#catalogotasa-fecha'),
        $btnSaveTasa        = $('#btnSaveTasa'),
        $inputTipoCambio     = $('#inputTipoCambio'),
    VAR_PATH_URL            	= $('body').data('url-root');
        bttBuilder = null;
    $(document).ready(function(){
        var columns = [
                {
                    field: 'fecha',
                    title: 'FECHA',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                },
                
                {
                    field: 'tipo_cambio',
                    title: 'Tipo de cambio',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter : btf.conta.money
                },
                {
                    field: 'created_at',
                    title: 'Creado',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.date,
                },
                {
                    field: 'created_by',
                    title: 'Creado por',
                    sortable: true,
                    visible: false,
                    formatter: btf.user.created_by,
                },

            ],
            params = {
                id      : 'tipoCambio',
                element : '.tipo-cambio-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    sortName : 'fecha',
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });


   

    $btnSaveTasa.click(function(){
        swal({
            title: "<h4> ESTAS SEGURO QUE DESEAS GUARDAR EL TIPO DE CAMBIO </h4>",
            text: '<strong style="font-size: 24px;font-weight: 700;color: #000;">CONFIRMAR OPERACION </strong>',
            type: "warning",
            html: true,
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "CONFIRMACION",
            closeOnConfirm: false
        }, function () {
            funct_postTasaUpdate();
        });
    });

    var funct_postTasaUpdate = function(){
        $.post(VAR_PATH_URL + "configuracion/tipo-cambio/post-tipo-cambio", { fecha: $catalogoTasaFecha.val(), tipo_cambio : $inputTipoCambio.val()   }, function(response){
            $('.bootstrap-table').bootstrapTable('refresh');
            if( response.code == 202 ){
                toast2Bold("SE REALIZO CORRECTAMENTE", "LA OPERACIÓN SE GUARDO CORRECTAMENTE", "success");
                $inputTipoCambio.val(null);
                
                
            }else{
                toast2("", "OCURRIO UN ERROR, INTENTA NUEVAMENTE ", "warning");
            }
            
        },'json');
    }

</script>



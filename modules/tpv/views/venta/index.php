<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;
use app\models\venta\Venta;
use app\models\sucursal\Sucursal;
use kartik\daterange\DateRangePicker;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Remisiones';
$this->params['breadcrumbs'][] = $this->title;

$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['ventas-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
$bttUrlUpdate = Url::to(['update?id=']);
$bttUrlDelete = Url::to(['delete?id=']);

?>

<div class="ibox">
    <div class="ibox-content">

        <div class="tpv-venta-index">
            <div class="btt-toolbar" >
                <?= Html::hiddenInput('status', Venta::STATUS_VENTA) ?>
                <div class="row" style="width: 800px;">
                    <div class="col-sm-3">
                        <?= $can['create']?
                            Html::a('<i class="fa fa-plus"></i> NUEVA REMISION', ['menu-venta'], ['class' => 'btn btn-success btn-lg add']): '' ?>
                    </div>
                    <div class="col-sm-8">
                        <strong class="pad-rgt">Filtrar [FECHA]:</strong>
                        <?= DateRangePicker::widget([
                            'name'           => 'date_range',
                            //'presetDropdown' => true,
                            'hideInput'      => true,
                            'useWithAddon'   => true,
                            'convertFormat'  => true,
                            'startAttribute' => 'from_date',
                            'endAttribute' => 'to_date',
                            'pluginOptions'  => [
                                'locale' => [
                                    'format'    => 'Y-m-d',
                                    'separator' => ' - ',

                                ],
                                'opens' => 'left',
                                "autoApply" => true,
                            ],
                        ])
                        ?>

                    </div>
                </div>
            </div>
                
                
            
            <table class="bootstrap-table"></table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        var can     = JSON.parse('<?= json_encode($can) ?>'),
            actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver venta" class="fa fa-eye"></a>'
            ].join(''); },
            columns = [
                {
                    field: 'id',
                    title: 'ID',
                    align: 'center',
                    width: '60',
                    sortable: true,
                    switchable:false,
                },
                {
                    field: 'pertenece',
                    title: 'Pertenece',
                    sortable: true,
                    formatter: btf.color.bold
                },
                {
                    field: 'cliente',
                    title: 'Cliente',
                    sortable: true,
                },
                {
                    field: 'ruta_asignada',
                    title: 'RUTA',
                    formatter: btf.color.green,
                    sortable: true,
                    visible: false,
                },
                {
                    field: 'tipo',
                    title: 'Tipo',
                    switchable: false,
                    formatter: btf.tpv.tipo,
                    align:'center',
                    sortable: true,
                },

                {
                    field: 'total',
                    title: 'Total',
                    align: 'center',
                    formatter: btf.conta.money,
                    sortable: true,
                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.tpv.status,
                },
                {
                    field: 'created_at',
                    title: 'Creado',
                    align: 'center',
                    sortable: true,
                    switchable: false,
                    formatter: btf.time.datetime,
                },
                {
                    field: 'created_by',
                    title: 'Creado por',
                    sortable: true,
                    visible: false,
                    formatter: btf.user.created_by,
                },
                {
                    field: 'updated_at',
                    title: 'Modificado',
                    align: 'center',
                    sortable: true,
                    visible: false,
                    formatter: btf.time.date,
                },
                {
                    field: 'updated_by',
                    title: 'Modificado por',
                    sortable: true,
                    visible: false,
                    formatter: btf.user.updated_by,
                },
                {
                    field: 'action',
                    title: 'Acciones',
                    align: 'center',
                    switchable: false,
                    width: '100',
                    class: 'btt-icons',
                    formatter: actions,
                    tableexportDisplay:'none',
                },
            ],
            params = {
                id      : 'venta',
                element : '.tpv-venta-index',
                url     : '<?= $bttUrl ?>',
                bootstrapTable : {
                    columns : columns,
                    exportOptions : {"fileName":"<?= $bttExport ?>"},
                    onDblClickRow : function(row, $element){
                        window.location.href = '<?= $bttUrlView ?>' + row.id;
                    },
                }
            };

        bttBuilder = new MyBttBuilder(params);
        bttBuilder.refresh();
    });
</script>

<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\BootstrapTableAsset;
BootstrapTableAsset::register($this);

$bttUrl         = Url::to(['compras-proveedor-json-btt']);
$bttExport      = Yii::$app->name . ' - ' . str_replace( '"', "", $this->title) . ' - ' . date('Y-m-d H.i');
$bttUrlView   = Url::to(['/compras/compra/view?id=']);
?>

<div class="ibox">
    <div class="ibox-content">
        <div class="proveedor-compra-index">
            <div class="btt-toolbar filter-top">
                <?= Html::hiddenInput('proveedor_id', $model->id) ?>
            </div>
            <table class="bootstrap-table"></table>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
         var  $filters = $('.btt-toolbar :input');
            actions = function(value, row) { return [
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver venta" class="fa fa-eye"></a>'
            ].join(''); },
            columns = [
                {
                    field: 'id',
                    title: 'FOLIO',
                    align: 'center',
                    sortable: true,
                    switchable:false,
                },
                {
                    field: 'total',
                    title: 'TOTAL DE LA COMPRA',
                    align: 'center',
                    formatter: btf.conta.money,
                    sortable: true,
                },
                {
                    field: 'sucursal',
                    title: 'SUCURSAL QUE RECIBE',
                    align: 'center',
                    formatter: btf.color.bold,
                    sortable: true,
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
                id      : 'proveedorCompre',
                element : '.proveedor-compra-index',
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
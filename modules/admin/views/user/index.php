<?php
use yii\helpers\Html;
use app\assets\BootstrapTableAsset;
use kartik\daterange\DateRangePicker;
use app\models\user\User;
use yii\helpers\Url;
use app\models\auth\AuthAssignment;
use app\models\esys\EsysListaDesplegable;

BootstrapTableAsset::register($this);
/* @var $this yii\web\View */

$this->title = 'Usuarios';
$this->params['breadcrumbs'][] = 'admin';
$this->params['breadcrumbs'][] = $this->title;



$bttExport    = Yii::$app->name . ' - ' . $this->title . ' - ' . date('Y-m-d H.i');
$bttUrl       = Url::to(['users-json-btt']);
$bttUrlView   = Url::to(['view?id=']);
$bttUrlUpdate = Url::to(['update?id=']);
$bttUrlDelete = Url::to(['delete?id=']);

?>


<div class="admin-user-index">
    <div class="ibox">
        <div class="ibox-content">
            <div class="btt-toolbar">
                <div class="row" style="width: 800px;">
                    <div class="col-sm-4">
                        
                            <?= $can['create']?
                                Html::a('<i class="fa fa-plus"></i>&nbsp;Nuevo usuario', ['create'], ['class' => 'btn btn-success add']): '' ?>
                        
                    </div>
                    <div class="col-sm-8">
                        <div class="form-group">
                            <?= Html::dropDownList('perfil', null, AuthAssignment::getItemsAssignments(), ['prompt' => '-- PERFILES --', 'class' => 'max-width-170px']) ?>
                            <?= Html::dropDownList('departamento_id', null, EsysListaDesplegable::getItems('departamento_laboral'), ['prompt' => '-- DEPARTAMENTOS --']) ?>
                        </div>
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
                '<a href="<?= $bttUrlView ?>' + row.id + '" title="Ver usuario" class="fa fa-eye"></a>',
                (can.update && row.status != <?= User::STATUS_DELETED ?> ? '<a href="<?= $bttUrlUpdate ?>' + row.id + '" title="Editar usuario" class="fa fa-pencil"></a>': ''),
                (can.delete && row.status != <?= User::STATUS_DELETED ?> ? '<a href="<?= $bttUrlDelete ?>' + row.id + '" title="Eliminar usuario" class="fa fa-trash" data-confirm="Confirma que deseas eliminar el usuario" data-method="post"></a>': '')
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
                    field: 'username',
                    title: 'Nombre de usuario',
                    visible: false,
                    sortable: true,
                },
                {
                    field: 'email',
                    title: 'Correo electrónico',
                    sortable: true,
                },
                {
                    field: 'nombre_completo',
                    title: 'Nombre completo',
                    switchable: false,
                    sortable: true,
                },
                {
                    field: 'perfil',
                    title: 'Perfil',
                    align: 'center',
                    sortable: true,
                },
                {
                    field: 'fecha_nac',
                    title: 'Fecha de nacimiento',
                    align: 'center',
                    sortable: true,
                    visible: false,
                    formatter: btf.time.date,
                },
                {
                    field: 'telefono',
                    title: 'Teléfono',
                    align: 'center',
                    sortable: true,
                },
                
                
                {
                    field: 'departamento',
                    title: 'Departamento',
                    sortable: true,
                },
                {
                    field: 'status',
                    title: 'Estatus',
                    align: 'center',
                    formatter: btf.status.opt_o,
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
                id      : 'usuario',
                element : '.admin-user-index',
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


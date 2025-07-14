<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use app\models\sucursal\Sucursal;
use app\models\proveedor\Proveedor;
use app\models\compra\Compra;
use app\models\cobro\CobroVenta;
/* @var $this yii\web\View */
/* @var $model app\models\sucursales\Sucursal */
/* @var $form yii\widgets\ActiveForm */
?>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>

<?php $form = ActiveForm::begin(['id' => 'form-sucursal', 'options' => ['enctype' => 'multipart/form-data']]) ?>


<div class="sucursales-sucursal-form" id="carrito-app">

    <div class="row">
        <div class="col-md-12">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-6">
                        <h3 class="text-center">Nueva compra</h3>
                    </div>
                    <div class="col-md-3"></div>
                </div>
            </div>
            <div class="panel">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3">
                            <?= $form->field($model, 'proveedor_id')->widget(Select2::classname(), [
                                'data' => Proveedor::get_proveedores_list(),
                                'options' => ['placeholder' => 'Seleccione un proveedor...'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ]); ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'tipo_moneda')->dropDownList(Compra::$tipo_moneda_list); ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'destino_tipo')->dropDownList(Compra::$tipo_destino_list, ['id' => 'compra-destino_tipo']); ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'fecha_entrega')->widget(DatePicker::classname(), [
                                'options' => ['placeholder' => 'Selecciona fecha ...'],
                                // 'type' => DatePicker::TYPE_INLINE,
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'format' => 'yyyy-mm-dd',
                                    'todayHighlight' => true,
                                ]
                            ]); ?>
                        </div>
                    </div>
                    <div class="row " id="row-almacen" style="display:none;">
                        <div class="col-md-6">
                            <?= $form->field($model, 'sucursal_id')->widget(
                                Select2::classname(),
                                [
                                    'data' => Sucursal::getItemsAlmacen(),
                                    'options' => ['placeholder' => 'Selecciona una sucursal...'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                ]
                            ); ?>
                        </div>
                    </div>
                    <div class="row" id="row-cliente" style="display:none;">
                        <div class="col-md-6">
                            <?= $form->field($model, 'cliente_id')->widget(
                                Select2::classname(),
                                [
                                    'language' => 'es',
                                    'data' => isset($model->cliente_id)  && $model->cliente_id ? [$model->cliente->id => $model->cliente->nombre] : [],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'minimumInputLength' => 3,
                                        'language'   => [
                                            'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                        ],
                                        'ajax' => [
                                            'url'      => Url::to(['cliente-ajax']),
                                            'dataType' => 'json',
                                            'cache'    => true,
                                            'processResults' => new JsExpression('function(data, params){  return {results: data} }'),
                                        ],
                                    ],
                                    'options' => [
                                        'placeholder' => 'Selecciona al cliente...',
                                    ],
                                ]
                            ) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4"></div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'tipo_pago')->dropDownList(\app\models\cobro\CobroVenta::$servicioList); ?>
                        </div>
                        <div class="col-md-4"></div>
                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'cliente_id')->widget(
                                Select2::classname(),
                                [
                                    'language' => 'es',
                                    'data' => isset($model->cliente_id)  && $model->cliente_id ? [$model->cliente->id => $model->cliente->nombre] : [],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'minimumInputLength' => 3,
                                        'language'   => [
                                            'errorLoading' => new JsExpression("function () { return 'Esperando los resultados...'; }"),
                                        ],
                                        'ajax' => [
                                            'url'      => Url::to(['cliente-ajax']),
                                            'dataType' => 'json',
                                            'cache'    => true,
                                            'processResults' => new JsExpression('function(data, params){  return {results: data} }'),
                                        ],
                                    ],
                                    'options' => [
                                        'placeholder' => 'Selecciona al cliente...',
                                    ],
                                ]
                            )->label(false); ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'sucursal_id')->widget(
                                Select2::classname(),
                                [
                                    'language' => 'es',

                                    'data' => Sucursal::getItemsAlmacen(),
                                    'options' => ['placeholder' => 'Selecciona una sucursal...'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                ]
                            )->label(false); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Carrito de compras con Vue.js -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-4">
                    <b>Agregar productos a la compra</b>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4 text-center" style="font-size: 2.2em;"> {{ monedaSymbol }}{{ calcularTotal() }}</div>

            </div>

        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-5">
                    <?= Select2::widget([
                        'name' => 'producto_id_vue',
                        'id' => 'producto_id_vue',
                        'data' => [], // Se llenará dinámicamente
                        'options' => ['placeholder' => 'Selecciona un producto...'],
                    ]); ?>
                </div>
                <div class="col-md-2">
                    <input type="number" min="1" class="form-control" v-model="nuevo.cantidad" placeholder="Cantidad">
                </div>
                <div class="col-md-2">
                    <input type="number" min="0" step="0.01" class="form-control" v-model="nuevo.costo" placeholder="Costo">
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-success" @click="agregarProducto">Agregar</button>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Costo</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, idx) in carrito" :key="idx">
                                <td>{{ item.producto_nombre }}</td>
                                <td>{{ item.cantidad }}</td>
                                <td>{{ getMonedaSymbol() }}{{ item.costo }}</td>
                                <td>{{ getMonedaSymbol() }}{{ (item.cantidad * item.costo).toFixed(2) }}</td>
                                <td><button type="button" class="btn btn-danger btn-xs" @click="quitarProducto(idx)">Quitar</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <input type="hidden" name="carrito_json" :value="JSON.stringify(carrito)">
        </div>

        <!-- Botón Guardar -->
        <div class="row m-4">
            <div class="col-md-12 text-center" style="margin-top:20px;">
                <?= Html::submitButton($model->isNewRecord ? 'Guardar compra' : 'Actualizar compra', ['class' => 'btn btn-primary btn-zoom']) ?>
            </div>
        </div>
    </div>



</div>
<?php ActiveForm::end(); ?>

<!-- Vue.js CDN -->
<script>
    var allProductos = <?= json_encode(\app\models\producto\Producto::find()->select(['nombre', 'id', 'proveedor_id'])->asArray()->all()) ?>;

    function getProductosByProveedor(proveedor_id) {
        let productos = {};
        allProductos.forEach(function(item) {
            if (item.proveedor_id == proveedor_id) {
                productos[item.id] = item.nombre;
            }
        });
        return productos;
    }

    var app = new Vue({
        el: '#carrito-app',
        data: {
            carrito: [],
            nuevo: {
                producto_id: '',
                cantidad: '',
                costo: ''
            },
            productosProveedor: {},
            proveedor_id: '',
            tipo_moneda: $('#compra-tipo_moneda').val() || '10' // 10: MXN, 20: USD
        },
        watch: {
            proveedor_id(val) {
                this.productosProveedor = getProductosByProveedor(val);
                this.nuevo.producto_id = '';
                if (window.jQuery) {
                    $('#producto_id_vue').val('').trigger('change');
                }
            },
            // Sincroniza tipo_moneda si cambia desde el dropdown
            tipo_moneda(val) {
                // Forzar actualización de la vista si es necesario
            }
        },
        methods: {
            //Calcular el total final de la compra con símbolo de moneda dinámico
            calcularTotal() {
                let total = 0;
                this.carrito.forEach(function(item) {
                    total += item.cantidad * item.costo;
                });
                return this.getMonedaSymbol() + total.toFixed(2);
            },
            getMonedaSymbol() {
                if (this.tipo_moneda == '20') return 'US$'; // 20: USD
                return '$'; // 10: MXN (default)
            },
            agregarProducto() {
                if (!this.nuevo.producto_id || !this.nuevo.cantidad || !this.nuevo.costo) {
                    alert('Selecciona producto, cantidad y costo');
                    return;
                }
                let producto_nombre = this.productosProveedor[this.nuevo.producto_id] || '';
                // Agrega sucursal_id y cliente_id si aplica
                let sucursal_id = $('#compra-sucursal_id').val() || null;
                let cliente_id = $('#compra-cliente_id').val() || null;
                // Calcula el total del producto
                let total = this.nuevo.cantidad * this.nuevo.costo;
                this.carrito.push({
                    producto_id: this.nuevo.producto_id,
                    producto_nombre: producto_nombre,
                    cantidad: this.nuevo.cantidad,
                    costo: this.nuevo.costo,
                    sucursal_id: sucursal_id,
                    cliente_id: cliente_id,
                    total: total
                });
                this.nuevo.producto_id = '';
                this.nuevo.cantidad = '';
                this.nuevo.costo = '';
                if (window.jQuery) {
                    $('#producto_id_vue').val('').trigger('change');
                }
            },
            quitarProducto(idx) {
                this.carrito.splice(idx, 1);
            }
        }
    });
    // Cambia aquí la URL a la ruta real de tu controlador que devuelva los productos por proveedor
    var urlProductosProveedor = "<?= Url::to(['get-producto']) ?>";

    function cargarProductosProveedor(proveedor_id) {
        $.get(urlProductosProveedor, {
            proveedor_id: proveedor_id
        }, function(data) {
            var productos = data;
            var $select = $('#producto_id_vue');
            $select.empty();
            $select.append($('<option>', {
                value: '',
                text: 'Selecciona un producto...'
            }));
            $.each(productos, function(id, nombre) {
                $select.append($('<option>', {
                    value: id,
                    text: nombre
                }));
            });
            $select.val('').trigger('change');
            // Actualiza el listado de productos en Vue
            app.productosProveedor = productos;
        });
    }

    $(document).on('change', '#compra-proveedor_id', function() {
        var proveedor_id = $(this).val();
        cargarProductosProveedor(proveedor_id);
        app.nuevo.producto_id = '';
    });
    // Inicializa productos si ya hay proveedor seleccionado
    $(function() {
        var proveedor_id = $('#compra-proveedor_id').val();
        if (proveedor_id) cargarProductosProveedor(proveedor_id);
    });
    // Sincroniza Select2 con Vue
    $(document).on('change', '#producto_id_vue', function() {
        app.nuevo.producto_id = $(this).val();
    });
    // Sincroniza tipo_moneda con Vue
    $(document).on('change', '#compra-tipo_moneda', function() {
        app.tipo_moneda = $(this).val();
    });
    $(document).on('change', '#compra-destino_tipo', function() {
        var tipo = $(this).val();
        if (tipo == '10') {
            $('#row-almacen').show();
            $('#row-cliente').hide();
        } else if (tipo == '20') {
            $('#row-almacen').hide();
            $('#row-cliente').show();
        } else {
            $('#row-almacen').hide();
            $('#row-cliente').hide();
        }
    });
    // Inicializa visibilidad al cargar
    $(function() {
        var tipo = $('#compra-destino_tipo').val();
        if (tipo == '10') {
            $('#row-almacen').show();
            $('#row-cliente').hide();
        } else if (tipo == '20') {
            $('#row-almacen').hide();
            $('#row-cliente').show();
        }
    });
</script>
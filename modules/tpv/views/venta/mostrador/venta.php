<?php

use app\models\cobro\CobroVenta;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
?>
<div id="punto-cobro-app">
    <div class="panel panel-primary shadow-lg" style="border-radius: 20px; overflow: hidden; min-height: 90vh;">
        <div class="panel-heading" style="background: #007bff; color: #fff; padding: 24px 36px; font-size: 2em; font-weight: bold; letter-spacing: 1px;">
            Punto de Cobro
        </div>
        <div class="panel-body" style="padding: 36px 36px 24px 36px;">
            <div class="row" style="min-height: 70vh;">
                <div class="col-lg-8 col-md-7 d-flex flex-column justify-content-between">
                    <div>
                        <div class="row mb-3 align-items-end">
                            <div class="col-md-12 mb-2">
                                <label><b>Buscar cliente</b></label>
                                <?= Select2::widget([
                                    'name' => 'cliente_id_vue',
                                    'language' => 'es',
                                    'data' => [],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'minimumInputLength' => 3,
                                        'language' => [
                                            'errorLoading' => new \yii\web\JsExpression("function () { return 'Esperando los resultados...'; }"),
                                        ],
                                        'ajax' => [
                                            'url' => \yii\helpers\Url::to(['ajax-clientes']),
                                            'dataType' => 'json',
                                            'cache' => true,
                                            'delay' => 250,
                                            'data' => new \yii\web\JsExpression('function(params) { return {q:params.term}; }'),
                                            'processResults' => new \yii\web\JsExpression('function(data){
                                                // Mapear el JSON para Select2
                                                var results = [];
                                                if(Array.isArray(data.data)) {
                                                    results = data.data.map(function(item){
                                                        return {id: item.id, text: item.nombre + (item.rfc ? " ("+item.rfc+")" : "")};
                                                    });
                                                }
                                                return {results: results};
                                            }'),
                                        ],
                                    ],
                                    'options' => [
                                        'placeholder' => 'Selecciona al cliente...',
                                        'id' => 'select2-cliente-vue',
                                    ],
                                ]);
                                ?>
                                <div v-if="clienteSeleccionado" class="alert alert-info mt-2 p-2">
                                    <b>Cliente seleccionado:</b> {{ clienteSeleccionado.nombre }}
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label><b>Moneda</b></label>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <select v-model="moneda" class="form-control" style="width: 110px; display: inline-block;">
                                        <option value="MXN">MXN</option>
                                        <option value="USD">USD</option>
                                    </select>
                                    <span v-if="moneda === 'MXN'">
                                        <img src="https://flagcdn.com/mx.svg" width="32" height="22" style="border-radius:4px;box-shadow:0 1px 4px #aaa;" alt="MXN">
                                    </span>
                                    <span v-else>
                                        <img src="https://flagcdn.com/us.svg" width="32" height="22" style="border-radius:4px;box-shadow:0 1px 4px #aaa;" alt="USD">
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-8 mb-2">
                                <label><b>Buscar producto</b></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" v-model="productoBusqueda" placeholder="Nombre, clave...">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" @click="buscarProductosAjax(productoBusqueda)"><i class="fa fa-search"></i> Buscar</button>
                                    </div>
                                </div>
                                <ul v-if="productosFiltrados.length && productoBusqueda" class="list-group mt-1" style="max-height: 220px; overflow-y: auto;">
                                    <li v-for="p in productosFiltrados" :key="p.id" class="list-group-item list-group-item-action" @click="seleccionarProducto(p)">
                                        {{ p.nombre }} <span v-if="p.clave">[{{ p.clave }}]</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div v-if="productoSeleccionado" class="row mb-3 align-items-end">
                            <div class="row m-3 text-center">
                                <div class="col-md-6">
                                    <b>Producto:</b> {{ productoSeleccionado.nombre }}
                                </div>

                                <div class="col-md-6">
                                    <b>DESCRIPCIÓN:</b> {{ productoSeleccionado.descripcion }}
                                </div>
                            </div>

                            <div class="row text-center m-3">
                                <div class="col-md-4">
                                    <input type="number" min="1" class="form-control" v-model.number="nuevoProducto.cantidad" placeholder="Cantidad">
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">{{ simboloMoneda }}</span>
                                        </div>
                                        <input type="number" min="0" step="0.01" class="form-control" v-model.number="nuevoProducto.precio" placeholder="Precio" disabled>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-success w-100" @click="agregarProducto">Agregar</button>
                                </div>

                            </div>


                        </div>
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-bordered table-hover shadow-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Producto</th>
                                            <th>Cantidad</th>

                                            <th>Precio</th>
                                            <th>IVA</th>
                                            <th>IEPS</th>

                                            <th>Total</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, idx) in carrito" :key="idx">
                                            <td>{{ item.nombre }}</td>
                                            <td>{{ formatNumber(item.cantidad) }}</td>
                                            <td>{{ simboloMoneda }}{{ formatNumber(item.precio, 2) }}</td>
                                            <td>{{ formatNumber(item.iva, 2) }}</td>
                                            <td>{{ formatNumber(item.ieps, 2) }}</td>
                                            <td>{{ simboloMoneda }}{{ formatNumber(item.cantidad * item.precio, 2) }}</td>
                                            <td><button class="btn btn-danger btn-xs" @click="quitarProducto(idx)">Quitar</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="text-right mb-2">
                                    <b>Total: {{ simboloMoneda }}{{ formatNumber(totalCarrito, 2) }}</b>
                                </div>
                                <div class="form-group mt-4">
                                    <label><b>Métodos de pago</b></label>
                                    <div class="row align-items-end mb-2">
                                        <div class="col-md-6">
                                            <?= Select2::widget([
                                                'name' => 'nuevoMetodoPago',
                                                'data' => CobroVenta::$servicioList,
                                                'options' => [
                                                    'placeholder' => 'Selecciona método...',
                                                    'id' => 'nuevoMetodoPagoSelect',
                                                    'style' => 'width:100%;',
                                                    'onchange' => 'appVue.nuevoMetodoPago = this.value;',
                                                ],
                                                'pluginOptions' => [
                                                    'allowClear' => true
                                                ],
                                            ]);
                                            ?>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" min="0.01" step="0.01" class="form-control" v-model.number="nuevoMontoPago" placeholder="Cantidad a cubrir">
                                        </div>
                                        <div class="col-md-2">
                                            <button class="btn btn-primary w-100" @click="agregarMetodoPago" :disabled="!nuevoMetodoPago || !nuevoMontoPago || nuevoMontoPago <= 0">Agregar</button>
                                        </div>
                                    </div>
                                    <div v-if="pagos.length" class="mb-2">
                                        <table class="table table-sm table-bordered table-striped" style="background:#f9fafb;">
                                            <thead>
                                                <tr>
                                                    <th>Método</th>
                                                    <th>Cantidad</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(pago, idx) in pagos" :key="idx">
                                                    <td>{{ mostrarNombreMetodo(pago.metodo) }}</td>
                                                    <td>{{ simboloMoneda }}{{ pago.monto.toFixed(2) }}</td>
                                                    <td><button class="btn btn-danger btn-xs" @click="eliminarPago(idx)">Quitar</button></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="text-right" :class="{'text-danger': totalPagos > totalCarrito}">
                                            <b>Total pagos: {{ simboloMoneda }}{{ formatNumber(totalPagos, 2) }}</b>
                                            <span v-if="totalPagos > totalCarrito" class="badge badge-danger ml-2">¡Excede el total!</span>
                                        </div>
                                        <div class="text-right mt-2">
                                            <span :class="{'badge badge-success': restante <= 0, 'badge badge-warning': restante > 0}" style="font-size:1.15em; padding:10px 18px;">
                                                Restante a pagar: {{ simboloMoneda }}{{ formatNumber(restante > 0 ? restante : 0, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4 mb-2">
                        <button class="btn btn-lg btn-success px-5 py-3 font-weight-bold shadow" :disabled="!puedeGuardar || guardando" @click="guardarVenta">
                            <span v-if="guardando" class="spinner-border spinner-border-sm mr-2"></span>
                            <i class="fa fa-save mr-2"></i> Guardar venta
                        </button>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5">
                    <div class="notas-panel">
                        <label for="notas"><b>Notas</b></label>
                        <textarea id="notas" v-model="notas" class="form-control" rows="12" placeholder="Notas adicionales para la venta..." style="resize:vertical; min-height: 180px;"></textarea>
                        <div v-if="notas.trim()" class="mt-3">
                            <div class="alert alert-warning sticky-note-alert" style="font-size:1.1em; background: #fffbe6; color: #856404; border: 1.5px solid #ffe58f; border-radius: 10px; box-shadow: 0 2px 8px #ffe58f80;">
                                <b>Nota:</b> {{ notas }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group mt-4" style="display: none;">
                <label><b>JSON a enviar</b></label>
                <textarea class="form-control" rows="7" readonly v-model="jsonVenta"></textarea>
            </div>
        </div>
    </div>
</div>

<style>
    #punto-cobro-app {
        background: #f4f8fb;
        min-height: 100vh;
        padding: 0;
    }

    .panel {
        box-shadow: 0 8px 40px rgba(0, 0, 0, 0.15);
        border-radius: 20px;
        border: none;
        margin: 0 auto;
        max-width: 99vw;
    }

    .panel-heading {
        border-radius: 20px 20px 0 0;
    }

    .panel-body {
        border-radius: 0 0 20px 20px;
    }

    #punto-cobro-app label {
        font-size: 1.25em;
        color: #0056b3;
        font-weight: 700;
    }

    #punto-cobro-app .form-control {
        font-size: 1.2em;
        border-radius: 10px;
        padding: 14px 16px;
    }

    #punto-cobro-app .list-group-item-action {
        cursor: pointer;
        transition: background 0.15s;
        font-size: 1.1em;
        padding: 12px 18px;
    }

    #punto-cobro-app .list-group-item-action:hover {
        background: #e9f5ff;
    }

    #punto-cobro-app .alert-info {
        background: #e9f7fd;
        color: #007bff;
        border-radius: 10px;
        font-size: 1.2em;
        padding: 12px 18px;
    }

    #punto-cobro-app .table {
        background: #f8f9fa;
        border-radius: 14px;
        overflow: hidden;
        margin-top: 28px;
        font-size: 1.15em;
    }

    #punto-cobro-app th {
        background: #007bff;
        color: #fff;
        font-size: 1.18em;
        text-align: center;
        padding: 16px 0;
    }

    #punto-cobro-app td {
        text-align: center;
        vertical-align: middle;
        padding: 14px 0;
    }

    #punto-cobro-app .btn-success {
        font-size: 1.15em;
        padding: 10px 32px;
        border-radius: 10px;
    }

    #punto-cobro-app .btn-danger {
        font-size: 1.05em;
        padding: 7px 22px;
        border-radius: 10px;
    }

    #punto-cobro-app .btn-primary {
        font-size: 1.1em;
        padding: 10px 24px;
        border-radius: 10px;
    }

    #punto-cobro-app .text-right {
        font-size: 1.5em;
        color: #0056b3;
        margin-top: 18px;
        font-weight: 800;
    }

    #punto-cobro-app .notas-panel {
        background: #f8f9fa;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.07);
        padding: 24px 18px 18px 18px;
        margin-top: 10px;
        min-height: 300px;
    }

    #punto-cobro-app textarea.form-control {
        font-size: 1.15em;
        border-radius: 10px;
        min-height: 180px;
        background: #fff;
    }

    .sticky-note-alert {
        font-family: 'Segoe UI', 'Arial', sans-serif;
        background: #fffbe6 !important;
        color: #856404 !important;
        border: 1.5px solid #ffe58f !important;
        border-radius: 10px !important;
        box-shadow: 0 2px 8px #ffe58f80 !important;
        margin-bottom: 0;
    }

    .input-group-prepend .input-group-text {
        font-size: 1.2em;
        font-weight: bold;
        background: #e9f7fd;
        border-radius: 8px 0 0 8px;
        border: 1px solid #ced4da;
    }
</style>

<!-- Vue.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
<script>
    var appVue = new Vue({
        el: '#punto-cobro-app',
        data: {
            clienteBusqueda: '',
            productoBusqueda: '',
            clienteSeleccionado: null,
            productoSeleccionado: null,
            nuevoProducto: {
                cantidad: 1,
                precio: 0
            },
            carrito: [],
            moneda: 'MXN',
            notas: '',

            servicioList: {
                EFECTIVO: 'EFECTIVO',
                CHEQUE: 'CHEQUE',
                TRANFERENCIA: 'TRANFERENCIA',
                TARJETA_CREDITO: 'TARJETA DE CREDITO',
                TARJETA_DEBITO: 'TARJETA DE DEBITO',
                DEPOSITO: 'DEPOSITO',
                CREDITO: 'CREDITO',
            },
            nuevoMetodoPago: '',
            nuevoMontoPago: '',
            pagos: [],
            guardando: false,

            clientesAjax: [],
            productosAjax: [],
            clientesLoading: false,
            productosLoading: false,
            clientesTimeout: null,
            productosTimeout: null,

            toastMsg: '',
            toastTimeout: null,
        },
        computed: {
            clientesFiltrados() {
                return this.clientesAjax.length ? this.clientesAjax : [];
            },
            productosFiltrados() {
                return this.productosAjax.length ? this.productosAjax : [];
            },
            totalCarrito() {
                return this.carrito.reduce((acc, item) => acc + (item.cantidad * item.precio), 0).toFixed(2);
            },
            simboloMoneda() {
                return this.moneda === 'MXN' ? '$' : 'US$';
            },
            totalPagos() {
                return this.pagos.reduce((acc, p) => acc + (parseFloat(p.monto) || 0), 0);
            },
            restante() {
                let r = parseFloat(this.totalCarrito) - this.totalPagos;
                return r > 0 ? r : 0;
            },
            puedeGuardar() {
                return this.clienteSeleccionado && this.carrito.length && this.totalPagos >= parseFloat(this.totalCarrito);
            },
            jsonVenta() {
                return JSON.stringify({
                    cliente_id: this.clienteSeleccionado ? this.clienteSeleccionado.id : null,
                    carrito: this.carrito,
                    pagos: this.pagos,
                    notas: this.notas
                }, null, 2);
            },
        },
        watch: {
            clienteBusqueda(val) {
                this.clientesAjax = [];
            },
            productoBusqueda(val) {
                this.productosAjax = [];
            },
            clienteSeleccionado(val) {
                // Limpiar productos al cambiar cliente
                this.productosAjax = [];
                this.productoBusqueda = '';
            },
            clientesAjax(val) {
                if (this.clienteBusqueda && Array.isArray(val)) {
                    alert('No se encontraron clientes.');
                }
            },
            productosAjax(val) {
                if (this.productoBusqueda && Array.isArray(val) && val.length === 0) {
                    // alert('No se encontraron productos.');
                }
            },
        },
        methods: {
            seleccionarCliente(cliente) {
                this.clienteSeleccionado = cliente;
                this.clienteBusqueda = cliente.nombre;
            },
            seleccionarProducto(producto) {
                this.productoSeleccionado = producto;
                this.productoBusqueda = producto.nombre;
                this.nuevoProducto = {
                    cantidad: 1,
                    precio: parseFloat(producto.precio) || 0
                };
            },
            agregarProducto() {
                if (!this.productoSeleccionado || !this.nuevoProducto.cantidad || !this.nuevoProducto.precio) {
                    alert('Selecciona producto, cantidad y precio');
                    return;
                }
                //Validamos stock
                if (parseFloat(this.nuevoProducto.cantidad) > parseFloat(this.productoSeleccionado.stock)) {
                    alert('Cantidad excede el stock disponible.');
                    return;
                }
                this.carrito.push({
                    id: this.productoSeleccionado.id,
                    nombre: this.productoSeleccionado.nombre,
                    cantidad: this.nuevoProducto.cantidad,
                    iva: this.productoSeleccionado.iva || 0,
                    ieps: this.productoSeleccionado.ieps || 0,
                    precio: this.nuevoProducto.precio
                });
                this.productoSeleccionado = null;
                this.productoBusqueda = '';
                this.nuevoProducto = {
                    cantidad: 1,
                    precio: 0
                };
            },
            quitarProducto(idx) {
                this.carrito.splice(idx, 1);
            },
            agregarMetodoPago() {
                if (!this.nuevoMetodoPago || !this.nuevoMontoPago || this.nuevoMontoPago <= 0) return;
                if (this.totalPagos + parseFloat(this.nuevoMontoPago) > parseFloat(this.totalCarrito)) {
                    alert('La suma de los pagos no puede exceder el total de la venta.');
                    return;
                }
                this.pagos.push({
                    metodo: this.nuevoMetodoPago,
                    monto: parseFloat(this.nuevoMontoPago)
                });
                this.nuevoMetodoPago = '';
                this.nuevoMontoPago = '';
                // Limpiar Select2
                if (window.$ && $('#nuevoMetodoPagoSelect').length) {
                    $('#nuevoMetodoPagoSelect').val(null).trigger('change');
                }
            },
            eliminarPago(idx) {
                this.pagos.splice(idx, 1);
            },
            mostrarNombreMetodo(key) {
                return this.servicioList[key] || key;
            },
            buscarClientesAjax(q) {
                var self = this;
                self.clientesLoading = true;
                $.post('<?= \yii\helpers\Url::to(['ajax-clientes']); ?>', {
                    q: q
                }, function(data) {
                    self.clientesAjax = Array.isArray(data.data) ? data.data : [];
                    self.clientesLoading = false;
                }, 'json').fail(function() {
                    self.clientesLoading = false;
                });
            },
            buscarProductosAjax(q) {
                var self = this;
                var clienteId = self.clienteSeleccionado ? self.clienteSeleccionado.id : null;
                self.productosLoading = true;
                if (clienteId === undefined || clienteId === null || clienteId === '') {
                    alert('Por favor, selecciona un cliente antes de buscar productos.');
                    self.productosLoading = false;
                    return;
                }
                $.post('<?= \yii\helpers\Url::to(['ajax-productos']); ?>', {
                    q: q,
                    cliente_id: clienteId
                }, function(data) {
                    self.productosAjax = Array.isArray(data) ? data : [];
                    self.productosLoading = false;
                }, 'json').fail(function() {
                    self.productosLoading = false;
                });
            },
            guardarVenta() {
                if (!this.puedeGuardar) return;
                this.guardando = true;

                $.post('<?= \yii\helpers\Url::to(['guardar-venta']); ?>', {
                    carrito: this.carrito,
                    pagos: this.pagos,
                    cliente_id: this.clienteSeleccionado ? this.clienteSeleccionado.id : null,
                    observaciones: this.observaciones
                }, function(data) {
                    if (!data.success) {
                        alert('Error al guardar la venta: ' + (data.message || ''));
                        this.guardando = false;
                        return;
                    }

                    setTimeout(() => {
                        this.guardando = false;
                        //alert('Venta guardada correctamente.');
                        window.location.href = '<?= \yii\helpers\Url::to(['view', 'id' => '']); ?>' + data.id;
                        // Aquí podrías limpiar el formulario si lo deseas
                    }, 100);
                }).fail(function() {
                    // Manejar errores aquí
                });
                // Simulación de guardado (reemplaza por AJAX real)
                //setTimeout(() => {
                //    this.guardando = false;
                //    alert('Venta guardada correctamente.');
                //    // Aquí podrías limpiar el formulario si lo deseas
                //}, 1500);
            },
            formatNumber(value, decimals = 0) {
                if (value == null || value === '') return '';
                return Number(value).toLocaleString('es-MX', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                });
            },
        },
        mounted() {
            this.clientesLoading = false;

        }
    });
    // Si Vue no se inicializa en 2s, mostrar error (loader ya no existe)
    setTimeout(function() {
        if (!window.appVue || !appVue.$el || !appVue.$el.__vue__) {
            alert('No se pudo inicializar la interfaz. Verifica la consola del navegador.');
        }
    }, 2000);

    // Integración Select2 con Vue para cliente
    $(document).on('change', '#select2-cliente-vue', function(e) {
        var option = $(this).select2('data')[0];
        if (option) {
            appVue.clienteSeleccionado = {
                id: option.id,
                nombre: option.text
            };
        } else {
            appVue.clienteSeleccionado = null;
        }
    });
    // Integración Select2 con Vue para método de pago
    $(document).on('change', '#nuevoMetodoPagoSelect', function(e) {
        var option = $(this).select2('data')[0];
        if (option) {
            appVue.nuevoMetodoPago = option.id;
        } else {
            appVue.nuevoMetodoPago = '';
        }
    });
</script>
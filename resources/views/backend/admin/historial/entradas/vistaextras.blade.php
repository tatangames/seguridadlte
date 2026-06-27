@extends('adminlte::page')

@section('title', 'Agregar Extras — Entrada #{{ $entrada->id }}')

@section('content_header')
    <h1>Agregar Extras — Entrada #{{ $entrada->id }}</h1>
@stop

@section('plugins.Sweetalert2', true)
@include('backend.urlglobal')

@section('content_top_nav_right')
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet"/>

    <li class="nav-item dropdown">
        <a href="#" class="nav-link" data-toggle="dropdown">
            <i class="fas fa-cogs"></i>
            <span class="d-none d-md-inline">{{ Auth::guard('admin')->user()->nombre }}</span>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <a href="{{ route('admin.perfil') }}" class="dropdown-item">
                <i class="fas fa-user mr-2"></i> Editar Perfil
            </a>
        </div>
    </li>
    <li class="nav-item">
        <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="nav-link btn btn-link border-0 bg-transparent">
                <i class="fas fa-sign-out-alt"></i>
                <span class="d-none d-md-inline">Cerrar Sesión</span>
            </button>
        </form>
    </li>
@endsection

@section('css')
    <style>
        #fila-total td {
            font-weight: bold;
            background-color: #f4f6f9;
            font-size: 1.05rem;
        }
    </style>
@stop

@section('content')

    <div id="divcontenedor">

        {{-- Info de la entrada --}}
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-10">
                        <div class="card card-gray-dark">
                            <div class="card-header">
                                <h3 class="card-title">
                                    Entrada #{{ $entrada->id }} —
                                    {{ $entrada->proveedor->nombre ?? '—' }}
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="text-muted">Fecha</label>
                                        <p><strong>{{ date('d/m/Y', strtotime($entrada->fecha)) }}</strong></p>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="text-muted">Lote / Factura</label>
                                        <p><strong>{{ $entrada->lote ?? '—' }}</strong></p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="text-muted">Descripción</label>
                                        <p><strong>{{ $entrada->descripcion ?? '—' }}</strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Botones --}}
        <section class="content-header">
            <div class="row">
                <button type="button" style="margin-left:15px" onclick="abrirModal()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Agregar Material
                </button>
                <a href="{{ route('admin.historial.entradas.index') }}"
                   style="margin-left:10px"
                   class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </section>

        {{-- Modal buscar material --}}
        <div class="modal fade" id="modalRepuesto" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h4 class="modal-title text-white">
                            <i class="fas fa-plus mr-2"></i>Agregar Material
                        </h4>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formulario-repuesto">
                            <div class="form-group">
                                <label>Material <span class="text-danger">*</span></label>
                                <div style="position:relative">
                                    <input id="repuesto" data-info="0" autocomplete="off"
                                           class="form-control" style="width:100%"
                                           onkeyup="buscarMaterial(this)" maxlength="400" type="text"
                                           placeholder="Escriba para buscar...">
                                    <div class="droplista" style="position:absolute;z-index:9;width:100%;display:none;
                                         background:#fff;border:1px solid #ced4da;border-radius:4px;
                                         max-height:200px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,0.1)">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Cantidad <span class="text-danger">*</span></label>
                                        <input type="number" id="cantidad" min="1" max="1000000"
                                               class="form-control" autocomplete="off" placeholder="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Precio <span class="text-danger">*</span></label>
                                        <input type="number" id="precio-producto" min="0" max="1000000"
                                               step="0.0001" autocomplete="off"
                                               class="form-control" placeholder="0.0000">
                                    </div>
                                </div>
                            </div>
                            {{-- Preview subtotal --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-success font-weight-bold">
                                            <i class="fas fa-calculator"></i> Subtotal:
                                        </label>
                                        <input type="text" id="preview-subtotal"
                                               class="form-control font-weight-bold text-success"
                                               readonly placeholder="$0.0000"
                                               style="background:#f4f9f4; font-size:1.1rem;">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="agregarFila()">
                            <i class="fas fa-plus mr-1"></i>Agregar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla de detalle --}}
        <section class="content-header">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h2>Materiales a Agregar</h2>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-gray-dark">
                    <div class="card-header">
                        <h3 class="card-title">
                            Detalle
                            <span id="contadorFilas" class="badge badge-light ml-2">0 materiales</span>
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0" id="matriz">
                                <thead>
                                <tr>
                                    <th style="width:3%">#</th>
                                    <th style="width:40%">Material</th>
                                    <th style="width:10%">Cantidad</th>
                                    <th style="width:13%">Precio Unit.</th>
                                    <th style="width:13%">Subtotal</th>
                                    <th style="width:10%">Opciones</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                <tr id="fila-total">
                                    <td colspan="4" class="text-right">TOTAL GENERAL:</td>
                                    <td id="total-general" class="text-success">$0.0000</td>
                                    <td></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div style="margin: 0 15px 25px; text-align:right">
            <button type="button" class="btn btn-success" onclick="preguntaGuardar()">
                <i class="fas fa-save mr-1"></i> Guardar Extras
            </button>
        </div>

    </div>

@stop

@section('js')
    <script src="{{ asset('js/toastr.min.js') }}"></script>
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>

    <script>
        const ID_ENTRADA = {{ $entrada->id }};
        window.seguroBuscador = true;
        window.txtContenedorGlobal = null;

        $(document).ready(function () {
            $(document).click(function (e) {
                if (!$(e.target).closest('#repuesto, .droplista').length) {
                    $('.droplista').hide();
                }
            });

            // Preview subtotal en tiempo real
            $('#cantidad, #precio-producto').on('input', function () {
                calcularPreviewSubtotal();
            });
        });

        document.getElementById('cantidad').addEventListener('keypress', function (e) {
            if (e.key < '0' || e.key > '9') e.preventDefault();
        });

        // ── Preview subtotal en el modal ──────────────────────────────
        function calcularPreviewSubtotal() {
            var cantidad = parseFloat(document.getElementById('cantidad').value) || 0;
            var precio   = parseFloat(document.getElementById('precio-producto').value) || 0;
            document.getElementById('preview-subtotal').value = '$' + (cantidad * precio).toFixed(4);
        }

        // ── Recalcular total general de la tabla ──────────────────────
        function recalcularTotal() {
            var total = 0;
            $("input[name='arraySubtotal[]']").each(function () {
                total += parseFloat($(this).attr('data-subtotal')) || 0;
            });
            document.getElementById('total-general').textContent = '$' + total.toFixed(4);
        }

        function abrirModal() {
            document.getElementById('formulario-repuesto').reset();
            document.getElementById('preview-subtotal').value = '';
            $('#repuesto').attr('data-info', '0');
            $('.droplista').hide().html('');
            $('#modalRepuesto').modal({ backdrop: 'static', keyboard: false });
        }

        function agregarFila() {
            var repuesto = document.querySelector('#repuesto');
            var cantidad = document.getElementById('cantidad').value.trim();
            var precio   = document.getElementById('precio-producto').value.trim();

            var reglaEntero  = /^[1-9]\d*$/;
            var reglaDecimal = /^([0-9]+\.?[0-9]{0,4})$/;

            if (repuesto.dataset.info == 0 || repuesto.value === '') { toastr.error('Selecciona un material de la lista'); return; }
            if (cantidad === '')             { toastr.error('Cantidad es requerida'); return; }
            if (!reglaEntero.test(cantidad)) { toastr.error('Cantidad debe ser un entero mayor a 0'); return; }
            if (precio === '')               { toastr.error('Precio es requerido'); return; }
            if (!reglaDecimal.test(precio))  { toastr.error('Precio inválido'); return; }
            if (parseFloat(precio) < 0)      { toastr.error('Precio no puede ser negativo'); return; }

            var subtotal = (parseFloat(cantidad) * parseFloat(precio)).toFixed(4);
            var nFilas   = $('#matriz > tbody > tr').length + 1;

            var markup = `<tr>
                <td><span class="num-fila">${nFilas}</span></td>
                <td>
                    <input name="descripcionArray[]" type="hidden"
                           data-info="${repuesto.dataset.info}" value="${repuesto.value}">
                    ${repuesto.value}
                </td>
                <td>
                    <input name="cantidadArray[]" type="hidden" value="${cantidad}">
                    ${cantidad}
                </td>
                <td>
                    <input name="arrayPrecio[]" data-precio="${precio}" type="hidden" value="${precio}">
                    $${parseFloat(precio).toFixed(4)}
                </td>
                <td>
                    <input name="arraySubtotal[]" type="hidden"
                           data-subtotal="${subtotal}" value="${subtotal}">
                    <span class="font-weight-bold text-success">$${subtotal}</span>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-xs" onclick="borrarFila(this)">
                        <i class="fas fa-trash"></i> Borrar
                    </button>
                </td>
            </tr>`;

            $('#matriz tbody').append(markup);
            recalcularTotal();
            actualizarContador();

            document.getElementById('formulario-repuesto').reset();
            document.getElementById('preview-subtotal').value = '';
            $('#repuesto').attr('data-info', '0');
            $('.droplista').hide().html('');
            $('#modalRepuesto').modal('hide');
            toastr.success('Material agregado');
        }

        function borrarFila(el) {
            el.closest('tr').remove();
            renumerarFilas();
            recalcularTotal();
            actualizarContador();
        }

        function renumerarFilas() {
            $('#matriz tbody tr').each(function (i) {
                $(this).find('.num-fila').text(i + 1);
            });
        }

        function actualizarContador() {
            var n = $('#matriz > tbody > tr').length;
            $('#contadorFilas').text(n + (n === 1 ? ' material' : ' materiales'));
        }

        function buscarMaterial(e) {
            if (seguroBuscador) {
                seguroBuscador = false;
                txtContenedorGlobal = e;
                let texto = e.value;
                if (texto === '') $(e).attr('data-info', 0);

                axios.post(urlAdmin + '/admin/buscar/material', { query: texto })
                    .then((response) => {
                        seguroBuscador = true;
                        $(e).siblings('.droplista').fadeIn().html(response.data);
                    })
                    .catch(() => { seguroBuscador = true; });
            }
        }

        function modificarValor(edrop) {
            let texto = $(edrop).text();
            $(txtContenedorGlobal).val(texto).attr('data-info', edrop.id);
            $(txtContenedorGlobal).siblings('.droplista').hide();
        }

        function preguntaGuardar() {
            var nFilas = $('#matriz > tbody > tr').length;
            if (nFilas === 0) { toastr.error('Agrega al menos un material'); return; }

            Swal.fire({
                title: '¿Guardar materiales extras?',
                text: 'Se agregarán ' + nFilas + ' material(es) a la entrada #' + ID_ENTRADA,
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, guardar'
            }).then((result) => {
                if (result.value) guardarExtras();
            });
        }

        function guardarExtras() {
            var descripcionAtributo = $("input[name='descripcionArray[]']").map(function () { return $(this).attr('data-info'); }).get();
            var cantidad            = $("input[name='cantidadArray[]']").map(function () { return $(this).val(); }).get();
            var arrayPrecio         = $("input[name='arrayPrecio[]']").map(function () { return $(this).attr('data-precio'); }).get();

            const contenedorArray = [];
            for (var i = 0; i < cantidad.length; i++) {
                contenedorArray.push({
                    idMaterial:   descripcionAtributo[i],
                    infoCantidad: cantidad[i],
                    infoPrecio:   arrayPrecio[i],
                });
            }

            openLoading();
            const formData = new FormData();
            formData.append('id_entrada',      ID_ENTRADA);
            formData.append('contenedorArray', JSON.stringify(contenedorArray));

            axios.post(urlAdmin + '/admin/historial/entradas/extras/guardar', formData)
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 2) {
                        toastr.success('Materiales agregados correctamente');
                        $('#matriz tbody').empty();
                        recalcularTotal();
                        actualizarContador();
                    } else {
                        toastr.error('Error al guardar');
                    }
                })
                .catch(() => { closeLoading(); toastr.error('Error al guardar'); });
        }
    </script>
@endsection

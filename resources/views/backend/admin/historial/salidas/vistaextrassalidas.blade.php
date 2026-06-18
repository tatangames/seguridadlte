@extends('adminlte::page')

@section('title', 'Agregar Extras — Salida #{{ $salida->id }}')

@section('content_header')
    <h1>Agregar Extras a Salida #{{ $salida->id }}</h1>
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

@section('content')

    <style>
        #matriz        { table-layout: fixed; width: 100%; }
        #matriz-busqueda { table-layout: fixed; }
        *:focus        { outline: none; }
        #modalCantidad .modal-dialog { max-width: 95%; }

        .seccion-header {
            background: linear-gradient(135deg, #1a3a6b 0%, #2156af 100%);
            border-radius: 10px 10px 0 0;
            padding: 12px 18px;
        }
        .seccion-header h3 {
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            margin: 0;
        }

        .card-info {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 18px rgba(33,86,175,.13);
            margin-bottom: 20px;
        }
        .card-info .card-body { padding: 22px 24px; }

        .field-label {
            font-size: 11px;
            font-weight: 700;
            color: #6b7a99;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 5px;
            display: block;
        }

        .divider-azul {
            border: none;
            border-top: 2px solid #e8eef8;
            margin: 18px 0;
        }

        #matriz thead tr th {
            background: #2156af;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            border: none !important;
            padding: 10px 12px;
            white-space: nowrap;
        }
        #matriz tbody tr { transition: background .15s; }
        #matriz tbody tr:hover { background: #eef3ff !important; }
        #matriz tbody td { vertical-align: middle; font-size: 13px; padding: 8px 10px; }

        .btn-guardar-salida {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 28px;
            font-weight: 400;
            font-size: 14px;
            letter-spacing: .03em;
            box-shadow: 0 4px 14px rgba(40,167,69,.35);
            transition: all .2s;
        }
        .btn-guardar-salida:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(40,167,69,.45);
            color: #fff;
        }

        .info-value {
            font-weight: 600;
            color: #1a3a6b;
        }
    </style>

    <div id="divcontenedor">

        {{-- ══ SECCIÓN: INFORMACIÓN DE SALIDA ══ --}}
        <section class="content" style="margin-bottom:0">
            <div class="container-fluid">
                <div class="card card-info">

                    <div class="seccion-header">
                        <h3><i class="fas fa-info-circle mr-2"></i>Información de Salida #{{ $salida->id }}</h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 col-sm-6 mb-3">
                                <span class="field-label"><i class="fas fa-calendar-alt mr-1"></i>Fecha</span>
                                <p class="info-value mb-0">{{ date('d/m/Y', strtotime($salida->fecha)) }}</p>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <span class="field-label"><i class="fas fa-user mr-1"></i>Empleado</span>
                                <p class="info-value mb-0">{{ $salida->empleado->nombre ?? '—' }}</p>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <span class="field-label"><i class="fas fa-users mr-1"></i>Colaborador</span>
                                <p class="info-value mb-0">{{ $salida->colaborador ?? '—' }}</p>
                            </div>
                            <div class="col-md-4 col-sm-6 mb-3">
                                <span class="field-label"><i class="fas fa-align-left mr-1"></i>Descripción</span>
                                <p class="info-value mb-0">{{ $salida->descripcion ?? '—' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between align-items-center"
                         style="border-top:2px solid #e8eef8; background:#f8faff; border-radius:0 0 10px 10px">
                        <a href="{{ route('admin.historial.salidas.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Volver
                        </a>
                        <button type="button" onclick="abrirModal()" class="btn btn-primary btn-sm"
                                style="border-radius:6px; font-weight:400">
                            <i class="fas fa-search mr-1"></i> Buscar Material
                        </button>
                    </div>

                </div>
            </div>
        </section>

        {{-- ══ SECCIÓN: DETALLE ══ --}}
        <section class="content">
            <div class="container-fluid">
                <div class="card card-info">

                    <div class="seccion-header" style="display:flex; justify-content:space-between; align-items:center">
                        <h3><i class="fas fa-list mr-2"></i>Materiales a Sacar</h3>
                        <span id="contador-filas"
                              style="background:rgba(255,255,255,.2); color:#fff; border-radius:20px; padding:2px 12px; font-size:12px; font-weight:700">
                            0 ítems
                        </span>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0" id="matriz"
                                   style="table-layout:fixed; width:100%">
                                <thead>
                                <tr>
                                    <th style="width:5%">#</th>
                                    <th style="width:60%">Material</th>
                                    <th style="width:15%">Cant. Salida</th>
                                    <th style="width:10%">Opciones</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between align-items-center"
                         style="border-top:2px solid #e8eef8; background:#f8faff; border-radius:0 0 10px 10px">
                        <small class="text-muted">Agregue materiales usando el buscador</small>
                        <button type="button" class="btn-guardar-salida" onclick="preguntaGuardar()">
                            <i class="fas fa-save mr-1"></i> Guardar Extras
                        </button>
                    </div>

                </div>
            </div>
        </section>

        {{-- ══ MODAL: BUSCAR MATERIAL ══ --}}
        <div class="modal fade" id="modalRepuesto" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header" style="background:#2156af">
                        <h4 class="modal-title" style="color:#fff">
                            <i class="fas fa-search mr-2"></i>Buscar Material
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formulario-repuesto">
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="field-label">
                                        Material
                                        <span class="badge badge-success ml-1">Solo con inventario disponible</span>
                                    </label>
                                    <table class="table" id="matriz-busqueda">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <input id="inputBuscador" autocomplete="off"
                                                       class="form-control" style="width:100%"
                                                       onkeyup="buscarMaterial(this)"
                                                       maxlength="300" type="text"
                                                       placeholder="Escribir nombre del material…">
                                                <div class="droplista"
                                                     style="position:absolute; z-index:9; width:95% !important"></div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div id="tablaRepuesto"></div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ MODAL: CANTIDAD / SALIDA DE MATERIAL ══ --}}
        <div class="modal fade" id="modalCantidad" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header" style="background:#1a3a6b">
                        <h4 class="modal-title" style="color:#fff">
                            <i class="fas fa-boxes mr-2"></i>Salida de Material
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formulario-material">
                            <div class="card-body">

                                <input type="hidden" id="id-material-seleccionado">

                                <div class="form-row mb-3">
                                    <div class="col-md-9">
                                        <label class="field-label">Material</label>
                                        <input type="text" disabled class="form-control" id="info-material">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="field-label">U/M</label>
                                        <input type="text" disabled class="form-control" id="info-medida">
                                    </div>
                                </div>

                                <hr class="divider-azul">

                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm" id="matrizM">
                                        <thead>
                                        <tr>
                                            <th>Fecha Ingreso</th>
                                            <th>Valor</th>
                                            <th>Cant. Actual</th>
                                            <th>Cant. Salida</th>
                                        </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button"
                                class="btn btn-success"
                                style="font-weight:400; border-radius:6px"
                                onclick="agregarAlDetalle()">
                            <i class="fas fa-plus mr-1"></i> Agregar al Detalle
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ MODAL: ERROR DE VALIDACIÓN ══ --}}
        <div class="modal fade" id="modalErrorExtras" tabindex="-1">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header" style="background:#6c757d">
                        <h4 class="modal-title" style="color:#fff">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            No se pudo guardar
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="contenido-error-extras"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- fin #divcontenedor --}}

@stop

@section('js')
    <script src="{{ asset('js/toastr.min.js') }}"></script>
    <script src="{{ asset('js/axios.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>

    <script>
        const ID_SALIDA = {{ $salida->id }};
        window.seguroBuscador = true;

        $(document).ready(function () {
            $(document).click(function () { $('.droplista').hide(); });
        });

        // ── Abrir modal buscador ──────────────────────────────────────────
        function abrirModal() {
            document.getElementById('tablaRepuesto').innerHTML = '';
            document.getElementById('formulario-repuesto').reset();
            $('#modalRepuesto').modal('show');
        }

        // ── Validar teclas numéricas ──────────────────────────────────────
        function validateInput(event) {
            const key = event.key;
            if (['Backspace','ArrowLeft','ArrowRight','Delete','Tab'].includes(key)) return true;
            if (key === 'e' || key === 'E' || key === '-' || isNaN(Number(key))) return false;
            return true;
        }

        // ── Validar cantidad salida (bloquea y avisa si supera máximo) ────
        function validateCantidadSalida(input, maxCantidad) {
            input.value = input.value.replace(/[^0-9]/g, '');
            if (Number(input.value) > maxCantidad) {
                input.value = maxCantidad;
                input.classList.add('is-invalid');
                toastr.warning('Cantidad ajustada al máximo disponible: ' + maxCantidad);
            } else {
                input.classList.remove('is-invalid');
            }
        }

        // ── Buscar material (dropdown) ────────────────────────────────────
        function buscarMaterial(e) {
            if (seguroBuscador) {
                seguroBuscador = false;
                var row   = $(e).closest('tr');
                var texto = e.value;
                axios.post(urlAdmin + '/admin/buscar/material/disponible', { query: texto })
                    .then((response) => {
                        seguroBuscador = true;
                        $(row).find('.droplista').fadeIn().html(response.data);
                    })
                    .catch(() => { seguroBuscador = true; });
            }
        }

        // ── Seleccionar material → abrir modal cantidades ─────────────────
        function modificarValor(edrop) {
            openLoading();
            $('#matrizM tbody tr').remove();

            var formData = new FormData();
            formData.append('id', edrop.id);

            axios.post(urlAdmin + '/admin/buscar/material/disponibilidad', formData)
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {

                        if (response.data.disponible === 1) {
                            toastr.info('NO HAY INVENTARIO DISPONIBLE');
                            return;
                        }

                        $('#id-material-seleccionado').val(edrop.id);
                        $('#info-material').val(response.data.nombreMaterial);
                        $('#info-medida').val(response.data.nombreMedida);

                        $.each(response.data.arrayIngreso, function (key, val) {
                            var markup =
                                '<tr>' +
                                '<td><input disabled value="' + val.fechaIngreso + '" class="form-control form-control-sm" type="text"></td>' +
                                '<td><input disabled value="' + val.precioFormat + '" class="form-control form-control-sm" type="text"></td>' +
                                '<td>' +
                                '<input name="arrayCantidadActual[]" disabled ' +
                                'data-cantidadActualFila="' + val.cantidadActual + '" ' +
                                'value="' + val.cantidadActual + '" class="form-control form-control-sm" type="number">' +
                                '</td>' +
                                '<td>' +
                                '<input class="form-control form-control-sm" ' +
                                'data-idfilaentradadetalle="' + val.id + '" ' +
                                'name="arrayCantidadSalida[]" min="0" max="' + val.cantidadActual + '" type="number" ' +
                                'onkeydown="return validateInput(event);" ' +
                                'oninput="validateCantidadSalida(this, ' + val.cantidadActual + ');">' +
                                '</td>' +
                                '</tr>';
                            $('#matrizM tbody').append(markup);
                        });

                        $('#modalCantidad').modal('show');

                    } else {
                        toastr.error('Error al cargar material');
                    }
                })
                .catch(() => { closeLoading(); toastr.error('Error'); });
        }

        // ── Agregar filas al detalle ──────────────────────────────────────
        function agregarAlDetalle() {
            var arrayIdEntradaDetalle = $("input[name='arrayCantidadSalida[]']").map(function () {
                return $(this).attr('data-idfilaentradadetalle');
            }).get();
            var arrayCantidadSalida = $("input[name='arrayCantidadSalida[]']").map(function () {
                return $(this).val();
            }).get();
            var arrayCantidadActual = $("input[name='arrayCantidadActual[]']").map(function () {
                return $(this).attr('data-cantidadActualFila');
            }).get();

            colorBlancoTabla();
            var habraSalida = true;

            for (var a = 0; a < arrayCantidadSalida.length; a++) {
                var fc = arrayCantidadSalida[a];

                if (fc !== '') {
                    if (fc <= 0) {
                        colorRojoTabla(a);
                        alertaMensaje('info', 'Error', 'Fila #' + (a + 1) + ': No se permite cero');
                        return;
                    }
                    habraSalida = false;
                }
                if (Number(fc) > Number(arrayCantidadActual[a])) {
                    colorRojoTabla(a);
                    alertaMensaje('info', 'Error', 'Fila #' + (a + 1) + ': Supera cantidad actual');
                    return;
                }
            }

            if (habraSalida) { toastr.error('Registrar mínimo 1 salida'); return; }

            var nombreTexto = document.getElementById('info-material').value;
            var nFilas      = $('#matriz > tbody > tr').length;

            for (var z = 0; z < arrayCantidadSalida.length; z++) {
                var fc = arrayCantidadSalida[z];
                if (fc !== '' && fc != 0) {
                    nFilas++;
                    var markup =
                        '<tr>' +
                        '<td><p id="fila' + nFilas + '" class="form-control" style="max-width:55px">' + nFilas + '</p></td>' +
                        '<td>' +
                        '<input name="idmaterialArray[]" type="hidden" data-idmaterialArray="' + arrayIdEntradaDetalle[z] + '">' +
                        '<input disabled value="' + nombreTexto + '" class="form-control form-control-sm" type="text">' +
                        '</td>' +
                        '<td><input name="salidaArray[]" disabled data-cantidadSalida="' + fc + '" value="' + fc + '" class="form-control form-control-sm" type="text"></td>' +
                        '<td><button type="button" class="btn btn-danger btn-block btn-sm" onclick="borrarFila(this)">Borrar</button></td>' +
                        '</tr>';
                    $('#matriz tbody').append(markup);
                }
            }

            actualizarContador();
            $('#modalCantidad').modal('hide');
            document.getElementById('inputBuscador').value = '';

            Swal.fire({
                position: 'center',
                type: 'success',
                title: 'Agregado al Detalle',
                showConfirmButton: false,
                timer: 1500
            });
        }

        // ── Preguntar antes de guardar ────────────────────────────────────
        function preguntaGuardar() {
            if ($('#matriz > tbody > tr').length === 0) {
                toastr.error('Agrega al menos un material');
                return;
            }
            colorBlancoTabla();
            Swal.fire({
                title: '¿Guardar materiales extras?',
                text: 'Se agregarán a la salida #' + ID_SALIDA,
                type: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, guardar'
            }).then((result) => {
                if (result.value) guardarExtras(); // ✅ FIX: result.value en lugar de result.isConfirmed
            });
        }

        // ── Guardar extras ────────────────────────────────────────────────
        function guardarExtras() {
            var idEntradaDetalle = $("input[name='idmaterialArray[]']").map(function () {
                return $(this).attr('data-idmaterialArray');
            }).get();
            var salidaCantidad = $("input[name='salidaArray[]']").map(function () {
                return $(this).attr('data-cantidadSalida');
            }).get();

            var contenedorArray = [];
            for (var i = 0; i < salidaCantidad.length; i++) {
                contenedorArray.push({
                    infoIdEntradaDeta: idEntradaDetalle[i],
                    infoCantidad:      salidaCantidad[i],
                });
            }

            openLoading();
            var formData = new FormData();
            formData.append('id_salida',       ID_SALIDA);
            formData.append('contenedorArray', JSON.stringify(contenedorArray));

            axios.post(urlAdmin + '/admin/historial/salidas/extras/guardar', formData)
                .then((response) => {
                    closeLoading();

                    if (response.data.success === 10) {
                        Swal.fire({
                            title: 'Extras guardados',
                            type: 'success',
                            allowOutsideClick: false,
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'Aceptar'
                        }).then((r) => {
                            if (r.value) {
                                $('#matriz tbody tr').remove();
                                actualizarContador();
                            }
                        });

                    } else if (response.data.success === 2) {
                        var html =
                            '<div class="alert alert-info">' +
                            '<h5><i class="fas fa-boxes mr-1"></i> Cantidad insuficiente en inventario</h5>' +
                            '<hr>' +
                            '<p><strong>Fila afectada:</strong> #' + response.data.fila + '</p>' +
                            '<p><strong>Cantidad solicitada:</strong> ' + response.data.solicitado + ' unidades</p>' +
                            '<p><strong>Disponible real:</strong> ' + response.data.disponible + ' unidades</p>' +
                            '<hr>' +
                            '<p class="mb-0" style="font-size:13px; color:white">' +
                            'Elimine esa fila y vuelva a buscar el material para ver el stock actualizado.' +
                            '</p>' +
                            '</div>';
                        document.getElementById('contenido-error-extras').innerHTML = html;
                        $('#modalErrorExtras').modal('show');

                    } else {
                        toastr.error('Error al guardar');
                    }
                })
                .catch(() => { closeLoading(); toastr.error('Error al guardar'); });
        }

        // ── Utilidades tabla ──────────────────────────────────────────────
        function borrarFila(el) {
            el.closest('tr').remove();
            setearFila();
            actualizarContador();
        }

        function setearFila() {
            var table  = document.getElementById('matriz');
            var conteo = 0;
            for (var r = 1; r < table.rows.length; r++) {
                conteo++;
                table.rows[r].cells[0].children[0].innerHTML = conteo;
            }
        }

        function actualizarContador() {
            var n = $('#matriz > tbody > tr').length;
            $('#contador-filas').text(n + (n === 1 ? ' ítem' : ' ítems'));
        }

        function colorRojoTabla(index) {
            $('#matrizM tr:eq(' + (index + 1) + ')').css('background', '#f8d7da');
        }

        function colorBlancoTabla() {
            $('#matrizM tbody tr').css('background', 'white');
        }
    </script>
@endsection

@extends('adminlte::page')

@section('title', 'Registro de Salidas')

@section('content_header')
    <h1>Registro de Salidas</h1>
@stop

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Sweetalert2', true)

@include('backend.urlglobal')

@section('content_top_nav_right')
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/select2.min.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('css/buttons_estilo.css') }}" rel="stylesheet">

    <li class="nav-item dropdown">
        <a href="#" class="nav-link" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-cogs"></i>
            <span class="d-none d-md-inline">
                {{ Auth::guard('admin')->user()->nombre }}
            </span>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <a href="{{ route('admin.perfil') }}" class="dropdown-item">
                <i class="fas fa-user mr-2"></i>
                Editar Perfil
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
        #matriz {
            table-layout: fixed;
            width: 100%;
        }

        #matriz-busqueda {
            table-layout: fixed;
        }

        .cursor-pointer:hover {
            cursor: pointer;
            color: #401fd2;
            font-weight: bold;
        }

        *:focus { outline: none; }

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

        #jefe-inmediato {
            background: #f0f4ff;
            border: 1px solid #c8d8f8;
            color: #1a3a6b;
            font-weight: 600;
            border-radius: 6px;
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
    </style>

    <div id="divcontenedor">

        {{-- ══ SECCIÓN: INFORMACIÓN ══ --}}
        <section class="content" style="margin-bottom: 0">
            <div class="container-fluid">
                <div class="card card-info" style="border-radius:10px">

                    <div class="seccion-header">
                        <h3><i class="fas fa-info-circle mr-2"></i>Información de Salida</h3>
                    </div>

                    <div class="card-body">

                        {{-- Fila 1: Fecha --}}
                        <div class="row">
                            <div class="col-md-3 col-sm-12 mb-3">
                                <label class="field-label"><i class="fas fa-calendar-alt mr-1"></i>Fecha</label>
                                <input type="date" class="form-control" id="fecha">
                            </div>
                        </div>

                        {{-- Fila 2: Distrito --}}
                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-3">
                                <label class="field-label"><i class="fas fa-map-marker-alt mr-1"></i>Distrito</label>
                                <select class="form-control" id="select-distrito" onchange="buscarUnidad()">
                                    <option value="0" selected disabled>Seleccionar distrito…</option>
                                    @foreach($arrayDistritos as $sel)
                                        <option value="{{ $sel->id }}">{{ $sel->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Fila 3: Unidad --}}
                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-3">
                                <label class="field-label"><i class="fas fa-building mr-1"></i>Unidad</label>
                                <select class="form-control" id="select-unidad" onchange="buscarEmpleado()">
                                    <option value="0" disabled selected>Seleccionar unidad…</option>
                                </select>
                            </div>
                        </div>

                        {{-- Fila 4: Empleado y Jefe Inmediato --}}
                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-3">
                                <label class="field-label"><i class="fas fa-user mr-1"></i>Empleado</label>
                                <select class="form-control" id="select-empleado" style="width:100%">
                                    <option value="0" disabled selected>Seleccionar empleado…</option>
                                </select>
                            </div>

                            <div class="col-md-6 col-sm-12 mb-3">
                                <label class="field-label"><i class="fas fa-user-tie mr-1"></i>Jefe Inmediato</label>
                                <input type="text" disabled class="form-control" autocomplete="off"
                                       id="jefe-inmediato" placeholder="Se cargará al seleccionar empleado">
                            </div>
                        </div>

                        {{-- Fila 5: Material Línea --}}
                        <div class="row">
                            <div class="col-md-6 col-sm-12 mb-3">
                                <label class="field-label"><i class="fas fa-tag mr-1"></i>Número de Equipo</label>
                                <input type="text" maxlength="100" class="form-control"
                                       id="linea-editar" autocomplete="off" placeholder="Línea de material…">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-5">
                                <label>Jefe Firma <span class="text-danger">*</span></label>
                                <select class="form-control" id="select-jefefirma">
                                    @foreach($arrayJefeFirma as $sel)
                                        <option value="{{ $sel->id }}">
                                            {{ $sel->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


                        <hr class="divider-azul">

                        <div class="row align-items-end">
                            <div class="col-md-8 mb-2">
                                <label class="field-label"><i class="fas fa-align-left mr-1"></i>Observaciones <small style="text-transform:none; font-weight:400">(Opcional)</small></label>
                                <input type="text" class="form-control" autocomplete="off"
                                       maxlength="800" id="descripcion" placeholder="Observaciones de la salida…">
                            </div>
                            <div class="col-md-4 mb-2 d-flex justify-content-end">
                                <button type="button" onclick="verPDfTemporal()"
                                        class="btn btn-success btn-sm mr-2"
                                        style="border-radius:6px; font-weight:400">
                                    <i class="fas fa-file-pdf mr-1"></i> PDF Temporal
                                </button>
                                <button type="button" id="botonaddmaterial" onclick="abrirModal()"
                                        class="btn btn-primary btn-sm"
                                        style="border-radius:6px; font-weight:400">
                                    <i class="fas fa-search mr-1"></i> Buscar Material
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        {{-- ══ SECCIÓN: DETALLE ══ --}}
        <section class="content">
            <div class="container-fluid">
                <div class="card card-info">

                    <div class="seccion-header" style="border-radius:10px 10px 0 0; display:flex; justify-content:space-between; align-items:center">
                        <h3><i class="fas fa-list mr-2"></i>Detalle de Salida</h3>
                        <span id="contador-filas" style="background:rgba(255,255,255,.2); color:#fff; border-radius:20px; padding:2px 12px; font-size:12px; font-weight:700">
                            0 ítems
                        </span>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0" id="matriz"
                                   style="table-layout:fixed; width:100%">
                                <thead>
                                <tr>
                                    <th style="width:4%">#</th>
                                    <th style="width:22%">Material</th>
                                    <th style="width:8%">Salida</th>
                                    <th style="width:9%">Reemplazo</th>
                                    <th style="width:11%">Recomendación</th>
                                    <th style="width:10%">Mes Reemplazo</th>
                                    <th style="width:8%">Opciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between align-items-center"
                         style="border-top:2px solid #e8eef8; background:#f8faff; border-radius:0 0 10px 10px">
                        <small class="text-muted">Agregue materiales usando el buscador</small>
                        <button type="button" class="btn-guardar-salida" onclick="preguntaGuardar()">
                            <i class="fas fa-save mr-1"></i>Guardar Salida
                        </button>
                    </div>

                </div>
            </div>
        </section>

        {{-- ══ MODAL: BUSCAR MATERIAL ══ --}}
        <div class="modal fade" id="modalRepuesto">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header" style="background:#2156af">
                        <h4 class="modal-title" style="color:#fff"><i class="fas fa-search mr-2"></i>Buscar Material</h4>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formulario-repuesto">
                            <div class="card-body">
                                <div class="form-group">
                                    <label class="field-label">
                                        Material — Regresa: Nombre / Medida / Marca / Normativa / Color / Talla
                                        <span class="badge badge-success ml-1">Solo con inventario</span>
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
                                                <div class="droplista" id="midropmenu"
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
        <div class="modal fade" id="modalCantidad">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header" style="background:#1a3a6b">
                        <h4 class="modal-title" style="color:#fff"><i class="fas fa-boxes mr-2"></i>Salida de Material</h4>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formulario-material">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">

                                        <input type="hidden" id="id-material-seleccionado">

                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label class="field-label">Material</label>
                                                <input type="text" disabled class="form-control" id="info-material">
                                            </div>
                                        </div>

                                        <div class="form-row mb-3">
                                            <div class="col-md-4">
                                                <label class="field-label">U/M</label>
                                                <input type="text" disabled class="form-control" id="info-medida">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="field-label">Marca</label>
                                                <input type="text" disabled class="form-control" id="info-marca">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="field-label">Normativa</label>
                                                <input type="text" disabled class="form-control" id="info-normativa">
                                            </div>
                                        </div>

                                        <hr class="divider-azul">

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm" id="matrizM">
                                                <thead>
                                                <tr>
                                                    <th>Fecha Ingreso</th>
                                                    <th>Factura/Lote</th>
                                                    <th>Valor</th>
                                                    <th>Proveedor</th>
                                                    <th>Meses Reemplazo</th>
                                                    <th>Reemplazo</th>
                                                    <th>Recomendación</th>
                                                    <th>Cant. Actual</th>
                                                    <th>Cant. Salida</th>
                                                </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>

                                    </div>
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
        <div class="modal fade" id="modalErrorSalida">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header" style="background:#6c757d">
                        <h4 class="modal-title" style="color:#fff">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            No se pudo guardar la salida
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="contenido-error-salida"></div>
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

    <script src="{{ asset('js/jquery.dataTables.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/dataTables.bootstrap4.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/bootstrap-input-spinner.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/custom-editors.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        $(document).ready(function () {

            var hoy = new Date();
            document.getElementById('fecha').value = hoy.toJSON().slice(0, 10);

            window.seguroBuscador = true;

            $(document).click(function () { $(".droplista").hide(); });

            var s2opts = {
                theme: "bootstrap-5",
                language: { noResults: function () { return "Búsqueda no encontrada"; } }
            };
            $('#select-distrito').select2(s2opts);
            $('#select-unidad').select2(s2opts);
            $('#select-empleado').select2(s2opts);

            $('#select-empleado').on('change', function () {
                var jefeNombre = $(this).find(':selected').data('jefe');
                if (jefeNombre && jefeNombre !== '' && jefeNombre !== 'undefined') {
                    $('#jefe-inmediato').val(jefeNombre);
                } else {
                    $('#jefe-inmediato').val('Sin jefe asignado');
                }
            });
        });
    </script>

    <script>

        // ── Abrir modal buscador ──────────────────────────────────────────
        function abrirModal() {
            document.getElementById('tablaRepuesto').innerHTML = "";
            document.getElementById("formulario-repuesto").reset();
            $('#modalRepuesto').modal('show');
        }

        // ── Validar teclas numéricas ──────────────────────────────────────
        function validateInput(event) {
            const key = event.key;
            if (["Backspace","ArrowLeft","ArrowRight","Delete","Tab"].includes(key)) return true;
            if (key === "e" || key === "E" || key === "-" || isNaN(Number(key))) return false;
            return true;
        }

        // ── Buscar material (dropdown) ────────────────────────────────────
        function buscarMaterial(e) {
            if (seguroBuscador) {
                seguroBuscador = false;
                var row   = $(e).closest('tr');
                var texto = e.value;
                axios.post(urlAdmin + '/admin/buscar/material/disponible', { 'query': texto })
                    .then((response) => {
                        seguroBuscador = true;
                        $(row).each(function () {
                            $(this).find(".droplista").fadeIn();
                            $(this).find(".droplista").html(response.data);
                        });
                    })
                    .catch(() => { seguroBuscador = true; });
            }
        }

        // ── Seleccionar material → abrir modal cantidades ─────────────────
        function modificarValor(edrop) {
            openLoading();
            var formData = new FormData();
            formData.append('id', edrop.id);
            $("#matrizM tbody tr").remove();

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
                        $('#info-marca').val(response.data.nombreMarca);
                        $('#info-normativa').val(response.data.nombreNormativa);

                        $.each(response.data.arrayIngreso, function (key, val) {
                            var nombreLote = val.lote != null ? val.lote : "";

                            var markup = "<tr>" +
                                "<td><input disabled value='" + val.fechaIngreso + "' class='form-control form-control-sm' type='text'></td>" +
                                "<td><input disabled value='" + nombreLote + "' class='form-control form-control-sm' type='text'></td>" +
                                "<td><input disabled value='" + val.precioFormat + "' class='form-control form-control-sm' type='text'></td>" +
                                "<td><input disabled value='" + val.proveedor + "' class='form-control form-control-sm' type='text'></td>" +
                                "<td>" +
                                "<input class='form-control form-control-sm' name='arrayMesesReemplazo[]' value='0' min='0' max='100' type='number' " +
                                "onkeydown=\"return validateInput(event);\" oninput=\"validateCantidadMaxReemplazo(this, 100);\">" +
                                "</td>" +
                                "<td><select name='arraySelect1[]' class='form-control form-control-sm'><option value='1'>SI</option><option value='0'>NO</option></select></td>" +
                                "<td><select name='arraySelect2[]' class='form-control form-control-sm'><option value='1'>SI</option><option value='0'>NO</option></select></td>" +
                                "<td>" +
                                "<input name='arrayCantidadActual[]' disabled " +
                                "data-cantidadActualFila='" + val.cantidadActual + "' " +
                                "value='" + val.cantidadActual + "' class='form-control form-control-sm' type='number'>" +
                                "</td>" +
                                "<td>" +
                                "<input class='form-control form-control-sm' " +
                                "data-idfilaentradadetalle='" + val.id + "' " +
                                "name='arrayCantidadSalida[]' min='0' max='" + val.cantidadActual + "' type='number' " +
                                "onkeydown=\"return validateInput(event);\" " +
                                "oninput=\"validateCantidadSalida(this, " + val.cantidadActual + ");\">" +
                                "</td>" +
                                "</tr>";

                            $("#matrizM tbody").append(markup);
                        });

                        $('#modalCantidad').modal('show');
                    } else {
                        toastr.error('Error al cargar material');
                    }
                })
                .catch(() => { toastr.error('Error'); closeLoading(); });
        }

        // ── Agregar filas al detalle ──────────────────────────────────────
        function agregarAlDetalle() {
            var arrayIdEntradaDetalle    = $("input[name='arrayCantidadSalida[]']").map(function () { return $(this).attr("data-idfilaentradadetalle"); }).get();
            var arrayCantidadSalida      = $("input[name='arrayCantidadSalida[]']").map(function () { return $(this).val(); }).get();
            var arrayCantidadActual      = $("input[name='arrayCantidadActual[]']").map(function () { return $(this).attr("data-cantidadActualFila"); }).get();
            var arrayMesesReemplazo      = $("input[name='arrayMesesReemplazo[]']").map(function () { return $(this).val(); }).get();
            var arraySelectReemplazo     = $("select[name='arraySelect1[]']").map(function () { return $(this).val(); }).get();
            var arraySelectRecomendacion = $("select[name='arraySelect2[]']").map(function () { return $(this).val(); }).get();

            colorBlancoTabla();
            var habraSalida = true;

            for (var a = 0; a < arrayCantidadSalida.length; a++) {
                var filaCantidad           = arrayCantidadSalida[a];
                var infoFilaCantidadActual = arrayCantidadActual[a];
                var infoMesReemplazo       = arrayMesesReemplazo[a];

                if (filaCantidad !== '') {
                    if (filaCantidad <= 0) {
                        colorRojoTabla(a);
                        alertaMensaje('info', 'Error', 'Fila #' + (a + 1) + ": No se permite cero");
                        return;
                    }
                    habraSalida = false;
                }
                if (filaCantidad > Number(infoFilaCantidadActual)) {
                    colorRojoTabla(a);
                    alertaMensaje('info', 'Error', 'Fila #' + (a + 1) + ": Supera cantidad actual");
                    return;
                }
                if (infoMesReemplazo < 0) {
                    colorRojoTabla(a);
                    alertaMensaje('info', 'Error', 'Fila #' + (a + 1) + ": Mes de reemplazo no puede ser negativo");
                    return;
                }
            }

            if (habraSalida) { toastr.error('Registrar mínimo 1 salida'); return; }

            var nombreTexto = document.getElementById('info-material').value;
            var nFilas      = $('#matriz >tbody >tr').length;

            for (var z = 0; z < arrayCantidadSalida.length; z++) {
                var fc = arrayCantidadSalida[z];
                if (fc !== '' && fc != 0) {
                    nFilas++;
                    var textoR  = arraySelectReemplazo[z]     == 1 ? "SI" : "NO";
                    var textoRc = arraySelectRecomendacion[z] == 1 ? "SI" : "NO";

                    var markup = "<tr>" +
                        "<td><p id='fila" + nFilas + "' class='form-control' style='max-width:55px'>" + nFilas + "</p></td>" +
                        "<td>" +
                        "<input name='idmaterialArray[]' type='hidden' data-idmaterialArray='" + arrayIdEntradaDetalle[z] + "'>" +
                        "<input disabled value='" + nombreTexto + "' class='form-control form-control-sm' type='text'>" +
                        "</td>" +
                        "<td><input name='salidaArray[]' disabled data-cantidadSalida='" + fc + "' value='" + fc + "' class='form-control form-control-sm' type='text'></td>" +
                        "<td><input name='reArrayReemplazo[]' disabled data-idvalorReemplazo='" + arraySelectReemplazo[z] + "' value='" + textoR + "' class='form-control form-control-sm' type='text'></td>" +
                        "<td><input name='reArrayRecomendacion[]' disabled data-idvalorRecomendacion='" + arraySelectRecomendacion[z] + "' value='" + textoRc + "' class='form-control form-control-sm' type='text'></td>" +
                        "<td><input name='reArrayMesReemplazo[]' disabled data-idvalorMesReemplazo='" + arrayMesesReemplazo[z] + "' value='" + arrayMesesReemplazo[z] + "' class='form-control form-control-sm' type='text'></td>" +
                        "<td><button type='button' class='btn btn-danger btn-block btn-sm' onclick='borrarFila(this)'>Borrar</button></td>" +
                        "</tr>";

                    $("#matriz tbody").append(markup);
                }
            }

            actualizarContador();
            $('#modalCantidad').modal('hide');
            document.getElementById('inputBuscador').value = '';

            Swal.fire({ position: 'center', icon: 'success', title: 'Agregado al Detalle', showConfirmButton: false, timer: 1500 });
        }

        // ── Preguntar antes de guardar ────────────────────────────────────
        function preguntaGuardar() {
            colorBlancoTabla();
            Swal.fire({
                title: '¿Guardar Salida?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, guardar'
            }).then((result) => { if (result.isConfirmed) guardarSalida(); });
        }

        // ── Guardar salida ────────────────────────────────────────────────
        function guardarSalida() {
            var fecha         = document.getElementById('fecha').value;
            var distrito      = document.getElementById('select-distrito').value;
            var unidad        = document.getElementById('select-unidad').value;
            var empleado      = document.getElementById('select-empleado').value;
            var descripcion   = document.getElementById('descripcion').value;
            var lineaMaterial = document.getElementById('linea-editar').value;
            var jefeFirma = document.getElementById('select-jefefirma').value;

            if (!fecha)                        { toastr.error('Fecha es requerida');     return; }
            if (!distrito || distrito === '0') { toastr.error('Seleccione un Distrito'); return; }
            if (!unidad   || unidad   === '0') { toastr.error('Seleccione una Unidad');  return; }
            if (!empleado || empleado === '0') { toastr.error('Seleccione un Empleado'); return; }

            if ($('#matriz > tbody > tr').length <= 0) {
                toastr.error('Debe agregar al menos un ítem de salida');
                return;
            }

            var reglaEntero        = /^[0-9]\d*$/;
            var idEntradaDetalle   = $("input[name='idmaterialArray[]']").map(function () { return $(this).attr("data-idmaterialArray"); }).get();
            var salidaCantidad     = $("input[name='salidaArray[]']").map(function ()     { return $(this).attr("data-cantidadSalida"); }).get();
            var arrayReemplazo     = $("input[name='reArrayReemplazo[]']").map(function () { return $(this).attr("data-idvalorReemplazo"); }).get();
            var arrayRecomendacion = $("input[name='reArrayRecomendacion[]']").map(function () { return $(this).attr("data-idvalorRecomendacion"); }).get();
            var arrayMesReemplazo  = $("input[name='reArrayMesReemplazo[]']").map(function () { return $(this).attr("data-idvalorMesReemplazo"); }).get();

            for (var a = 0; a < idEntradaDetalle.length; a++) {
                var ic = salidaCantidad[a];
                if (!ic)                       { colorRojoTabla(a); toastr.error('Fila #' + (a+1) + ' — Cantidad requerida');           return; }
                if (!ic.match(reglaEntero))    { colorRojoTabla(a); toastr.error('Fila #' + (a+1) + ' — Debe ser entero positivo');     return; }
                if (ic <= 0)                   { colorRojoTabla(a); toastr.error('Fila #' + (a+1) + ' — No puede ser cero o negativo'); return; }
                if (ic > 1000000)              { colorRojoTabla(a); toastr.error('Fila #' + (a+1) + ' — Máximo 1,000,000');             return; }
            }

            var contenedorArray = [];
            for (var p = 0; p < salidaCantidad.length; p++) {
                contenedorArray.push({
                    infoIdEntradaDeta: idEntradaDetalle[p],
                    infoCantidad:      salidaCantidad[p],
                    infoReemplazo:     arrayReemplazo[p],
                    infoRecomendacion: arrayRecomendacion[p],
                    infoMesReemplazo:  arrayMesReemplazo[p]
                });
            }

            openLoading();
            var formData = new FormData();
            formData.append('fecha',           fecha);
            formData.append('empleado',        empleado);
            formData.append('descripcion',     descripcion);
            formData.append('lineaEditar',     lineaMaterial);
            formData.append('jefeFirma',       jefeFirma);
            formData.append('contenedorArray', JSON.stringify(contenedorArray));

            axios.post(urlAdmin + '/admin/salida/guardar', formData)
                .then((response) => {
                    closeLoading();

                    if (response.data.success === 1) {
                        toastr.error('Se requiere ítem de salida');
                    }
                    else if (response.data.success === 2) {
                        // Supera unidades disponibles
                        var html =
                            '<div class="alert alert-info">' +
                            '<h5><i class="fas fa-boxes mr-1"></i> Cantidad insuficiente en inventario</h5>' +
                            '<hr>' +
                            '<p><strong>Fila afectada:</strong> #' + response.data.fila + '</p>' +
                            '<p><strong>Cantidad solicitada:</strong> ' + response.data.solicitado + ' unidades</p>' +
                            '<p><strong>Disponible real:</strong> ' + response.data.disponible + ' unidades</p>' +
                            '<hr>' +
                            '<p class="mb-0" style="font-size:13px; color: white">' +
                            'Corrija la cantidad de salida en la fila indicada e intente guardar de nuevo.' +
                            '</p>' +
                            '</div>';
                        document.getElementById('contenido-error-salida').innerHTML = html;
                        $('#modalErrorSalida').modal('show');
                    }
                    else if (response.data.success === 3) {
                        // Fecha de salida anterior a fecha de entrada
                        var html =
                            '<div class="alert alert-warning">' +
                            '<h5><i class="fas fa-calendar-times mr-1"></i> Fecha de salida inválida</h5>' +
                            '<hr>' +
                            '<p><strong>Fila afectada:</strong> #' + response.data.fila + '</p>' +
                            '<p><strong>Fecha de entrada del lote:</strong> ' + response.data.fechaEntrada + '</p>' +
                            '<p><strong>Fecha de salida ingresada:</strong> ' + response.data.fechaSalida + '</p>' +
                            '<hr>' +
                            '<p class="mb-0" style="font-size:13px">' +
                            'No se puede registrar una salida con fecha anterior al ingreso del material. ' +
                            'Corrija la fecha de salida e intente de nuevo.' +
                            '</p>' +
                            '</div>';
                        document.getElementById('contenido-error-salida').innerHTML = html;
                        $('#modalErrorSalida').modal('show');
                    }
                    else if (response.data.success === 10) {
                        reporteFinal(response.data.idsalida);
                        msgActualizado();
                    }
                    else {
                        toastr.error('Error al guardar');
                    }
                })
                .catch(() => { toastr.error('Error al guardar'); closeLoading(); });
        }

        // ── PDF Temporal ──────────────────────────────────────────────────
        function verPDfTemporal() {
            var fecha         = document.getElementById('fecha').value;
            var distrito      = document.getElementById('select-distrito').value;
            var unidad        = document.getElementById('select-unidad').value;
            var empleado      = document.getElementById('select-empleado').value;
            var descripcion   = document.getElementById('descripcion').value;
            var lineaMaterial = document.getElementById('linea-editar').value;
            var jefeFirma = document.getElementById('select-jefefirma').value;

            if (!fecha)                        { toastr.error('Fecha es requerida');     return; }
            if (!distrito || distrito === '0') { toastr.error('Seleccione un Distrito'); return; }
            if (!unidad   || unidad   === '0') { toastr.error('Seleccione una Unidad');  return; }
            if (!empleado || empleado === '0') { toastr.error('Seleccione un Empleado'); return; }

            if ($('#matriz > tbody > tr').length <= 0) {
                toastr.error('Debe agregar al menos un ítem de salida');
                return;
            }

            var reglaEntero        = /^[0-9]\d*$/;
            var idEntradaDetalle   = $("input[name='idmaterialArray[]']").map(function () { return $(this).attr("data-idmaterialArray"); }).get();
            var salidaCantidad     = $("input[name='salidaArray[]']").map(function ()     { return $(this).attr("data-cantidadSalida"); }).get();
            var arrayReemplazo     = $("input[name='reArrayReemplazo[]']").map(function () { return $(this).attr("data-idvalorReemplazo"); }).get();
            var arrayRecomendacion = $("input[name='reArrayRecomendacion[]']").map(function () { return $(this).attr("data-idvalorRecomendacion"); }).get();

            for (var a = 0; a < idEntradaDetalle.length; a++) {
                var ic = salidaCantidad[a];
                if (!ic)                       { colorRojoTabla(a); toastr.error('Fila #' + (a+1) + ' — Cantidad requerida');       return; }
                if (!ic.match(reglaEntero))    { colorRojoTabla(a); toastr.error('Fila #' + (a+1) + ' — Debe ser entero positivo'); return; }
                if (ic <= 0)                   { colorRojoTabla(a); toastr.error('Fila #' + (a+1) + ' — No puede ser cero');        return; }
                if (ic > 1000000)              { colorRojoTabla(a); toastr.error('Fila #' + (a+1) + ' — Máximo 1,000,000');         return; }
            }

            var contenedorArray = [];
            for (var p = 0; p < salidaCantidad.length; p++) {
                contenedorArray.push({
                    infoIdEntradaDeta: idEntradaDetalle[p],
                    infoCantidad:      salidaCantidad[p],
                    infoReemplazo:     arrayReemplazo[p],
                    infoRecomendacion: arrayRecomendacion[p]
                });
            }

            reporteTemporal(contenedorArray, fecha, empleado, descripcion, lineaMaterial, jefeFirma);
        }

        function reporteTemporal(contenedorArray, fecha, empleado, descripcion, lineaMaterial, jefeFirma) {
            var form       = document.createElement('form');
            form.method    = 'POST';
            form.action    = "{{ URL::to('admin/salidas/pdf-temporal') }}";
            form.target    = '_blank';

            var tokenInput   = document.createElement('input');
            tokenInput.type  = 'hidden';
            tokenInput.name  = '_token';
            tokenInput.value = '{{ csrf_token() }}';
            form.appendChild(tokenInput);

            var campos = {
                contenedorArray: JSON.stringify(contenedorArray),
                fecha:           fecha,
                empleado:        empleado,
                descripcion:     descripcion,
                lineaMaterial:   lineaMaterial,
                jefeFirma:       jefeFirma
            };

            for (var key in campos) {
                var input   = document.createElement('input');
                input.type  = 'hidden';
                input.name  = key;
                input.value = campos[key];
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        function reporteFinal(idsalida) {
            window.open("{{ URL::to('admin/salidas/pdfcompleto') }}/" + idsalida);
        }

        // ── Mensajes finales ──────────────────────────────────────────────
        function msgActualizado() {
            Swal.fire({
                title: 'Salida Registrada',
                icon: 'success',
                allowOutsideClick: false,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Aceptar'
            }).then((result) => { if (result.isConfirmed) location.reload(); });
        }

        // ── Utilidades tabla ──────────────────────────────────────────────
        function borrarFila(elemento) {
            elemento.closest('tr').remove();
            setearFila();
            actualizarContador();
        }

        function setearFila() {
            var table  = document.getElementById('matriz');
            var conteo = 0;
            for (var r = 1, n = table.rows.length; r < n; r++) {
                conteo++;
                var el = table.rows[r].cells[0].children[0];
                el.innerHTML = conteo;
            }
        }

        function actualizarContador() {
            var n = $('#matriz > tbody > tr').length;
            $('#contador-filas').text(n + (n === 1 ? ' ítem' : ' ítems'));
        }

        function colorRojoTabla(index) {
            $("#matrizM tr:eq(" + (index + 1) + ")").css('background', '#f8d7da');
        }

        function colorBlancoTabla() {
            $("#matrizM tbody tr").css('background', 'white');
        }

        function validateCantidadSalida(input, maxCantidad) {
            input.value = input.value.replace(/[^0-9]/g, '');
            if (Number(input.value) > maxCantidad) input.value = maxCantidad;
        }

        function validateCantidadMaxReemplazo(input, maxCantidad) {
            input.value = input.value.replace(/[^0-9]/g, '');
            if (Number(input.value) > maxCantidad) input.value = maxCantidad;
        }

        // ── Distrito → poblar Unidades ────────────────────────────────────
        function buscarUnidad() {
            var id = document.getElementById('select-distrito').value;
            if (!id || id == '0') {
                document.getElementById("select-unidad").options.length   = 0;
                document.getElementById("select-empleado").options.length = 0;
                $('#jefe-inmediato').val('');
                return;
            }

            $('#jefe-inmediato').val('');
            openLoading();

            axios.post(urlAdmin + '/admin/empleados/buscarunidad', { 'id': id })
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
                        document.getElementById("select-unidad").options.length   = 0;
                        document.getElementById("select-empleado").options.length = 0;
                        $('#select-unidad').append('<option value="0" disabled selected>Seleccionar unidad…</option>');
                        $.each(response.data.arrayUnidad, function (key, val) {
                            $('#select-unidad').append('<option value="' + val.id + '">' + val.nombre + '</option>');
                        });
                        $('#select-unidad').trigger('change.select2');
                    } else {
                        toastr.error('No se encontraron unidades');
                    }
                })
                .catch(() => { closeLoading(); toastr.error('Error al buscar unidades'); });
        }

        // ── Unidad → poblar Empleados ─────────────────────────────────────
        function buscarEmpleado() {
            var id = document.getElementById('select-unidad').value;
            if (!id || id == '0') {
                document.getElementById("select-empleado").options.length = 0;
                $('#jefe-inmediato').val('');
                return;
            }

            $('#jefe-inmediato').val('');
            openLoading();

            axios.post(urlAdmin + '/admin/empleados/buscarunidad-empleado', { 'id': id })
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
                        document.getElementById("select-empleado").options.length = 0;
                        $('#select-empleado').append('<option value="0" disabled selected>Seleccionar empleado…</option>');
                        $.each(response.data.arrayEmpleados, function (key, val) {
                            var jefeNombre = val.jefe_nombre || '';
                            $('#select-empleado').append(
                                '<option value="' + val.id + '" data-jefe="' + jefeNombre + '">'
                                + val.nombreCompleto +
                                '</option>'
                            );
                        });
                        $('#select-empleado').trigger('change.select2');
                    } else {
                        toastr.error('No se encontraron empleados');
                    }
                })
                .catch(() => { closeLoading(); toastr.error('Error al buscar empleados'); });
        }

    </script>

@endsection

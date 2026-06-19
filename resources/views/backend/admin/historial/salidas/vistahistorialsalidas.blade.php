@extends('adminlte::page')

@section('title', 'Historial / Salidas')

@section('content_header')
    <h1>Historial / Salidas</h1>
@stop

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)

@include('backend.urlglobal')

@section('content_top_nav_right')
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet"/>
    <link href="{{ asset('css/select2.min.css') }}" type="text/css" rel="stylesheet"/>
    <link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" type="text/css" rel="stylesheet"/>

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

    <div id="divcontenedor">

        {{-- ══ FILTROS ══ --}}
        <section class="content">
            <div class="container-fluid">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Distrito</label>
                                    <select id="select-distrito-filtro" class="form-control" style="width:100%">
                                        <option value="">— Todos —</option>
                                        @foreach($arrayDistrito as $d)
                                            <option value="{{ $d->id }}">{{ $d->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Unidad</label>
                                    <select id="select-unidad-filtro" class="form-control" style="width:100%" disabled>
                                        <option value="">— Seleccionar distrito primero —</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Empleado</label>
                                    <select id="select-empleado-filtro" class="form-control" style="width:100%" disabled>
                                        <option value="">— Seleccionar unidad primero —</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha desde</label>
                                    <input type="date" id="fecha-desde-filtro" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Fecha hasta</label>
                                    <input type="date" id="fecha-hasta-filtro" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-9 d-flex align-items-end">
                                <div class="form-group mb-0">
                                    <button type="button" class="btn btn-primary" onclick="buscarConFiltros()">
                                        <i class="fas fa-search mr-1"></i> Buscar
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="limpiarFiltros()">
                                        <i class="fas fa-eraser mr-1"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══ TABLA ══ --}}
        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Listado de Salidas</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="tablaDatatable">
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-filter fa-2x mb-2 d-block"></i>
                                        <p>Selecciona al menos un filtro y presiona <b>Buscar</b> para ver resultados.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- ══ MODAL EDITAR SALIDA ══ --}}
    <div class="modal fade" id="modalEditar" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-edit mr-2"></i>Editar Salida
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <input type="hidden" id="id-editar">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha <span class="text-danger">*</span></label>
                                <input type="date" id="fecha-editar" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Distrito <span class="text-danger">*</span></label>
                                <select id="select-distrito-editar" class="form-control" style="width:100%">
                                    <option value="">— Seleccionar distrito —</option>
                                    @foreach($arrayDistrito as $d)
                                        <option value="{{ $d->id }}">{{ $d->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Unidad <span class="text-danger">*</span></label>
                                <select id="select-unidad-editar" class="form-control" style="width:100%">
                                    <option value="">— Seleccionar distrito primero —</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Empleado <span class="text-danger">*</span></label>
                                <select id="select-empleado-editar" class="form-control" style="width:100%">
                                    <option value="">— Seleccionar unidad primero —</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jefe Firma</label>
                                <input type="text" id="jefe-firma-editar" class="form-control"
                                       maxlength="100" placeholder="Nombre en firma">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cargo Firma</label>
                                <input type="text" id="cargo-firma-editar" class="form-control"
                                       maxlength="100" placeholder="Cargo en firma">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Material Línea</label>
                                <input type="text" id="material-linea-editar" class="form-control"
                                       maxlength="400" placeholder="Material línea">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea id="descripcion-editar" class="form-control"
                                          rows="3" maxlength="800"
                                          placeholder="Descripción opcional"></textarea>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" onclick="editar()">
                        <i class="fas fa-save mr-1"></i>Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ MODAL DETALLE SALIDA ══ --}}
    <div class="modal fade" id="modalDetalle" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-list mr-2"></i>
                        Detalle de Salida —
                        <small class="ml-2" id="detalle-fecha"></small>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="detalle-loading" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                    </div>
                    <div id="detalle-contenido" style="display:none;">
                        <table class="table table-bordered table-striped table-sm">
                            <thead class="thead-dark">
                            <tr>
                                <th style="width:5%">#</th>
                                <th>Material</th>
                                <th class="text-center" style="width:12%">Cantidad</th>
                                <th class="text-right" style="width:14%">Precio Unit.</th>
                                <th class="text-center" style="width:10%">Quitar</th>
                            </tr>
                            </thead>
                            <tbody id="detalle-tbody"></tbody>
                        </table>
                    </div>
                    <div id="detalle-vacio" class="text-center text-muted py-4" style="display:none;">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        <p>Esta salida no tiene materiales registrados.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

@stop

@section('js')
    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}" type="text/javascript"></script>

    <script>

        var detalleIdSalidaActual = null;

        // ════════════════════════════════════════════════════════════
        // SELECT2 — opciones
        // ════════════════════════════════════════════════════════════
        function s2optsEditar() {
            return {
                theme: 'bootstrap-5',
                dropdownParent: $('#modalEditar'),
                width: '100%',
                language: { noResults: function () { return 'Búsqueda no encontrada'; } }
            };
        }

        function s2optsFiltro() {
            return {
                theme: 'bootstrap-5',
                width: '100%',
                language: { noResults: function () { return 'Búsqueda no encontrada'; } }
            };
        }

        // ── Inicializa (o re-inicializa) un select2 ──────────────────
        function initS2(selector, opts) {
            if ($(selector).hasClass('select2-hidden-accessible')) {
                $(selector).select2('destroy');
            }
            $(selector).select2(opts);
        }

        // ── Resetea un select y lo re-inicializa ─────────────────────
        function resetSelect(selector, placeholder, disabled, opts) {
            if ($(selector).hasClass('select2-hidden-accessible')) {
                $(selector).select2('destroy');
            }
            $(selector)
                .empty()
                .append('<option value="">' + placeholder + '</option>')
                .prop('disabled', disabled);
            $(selector).select2(opts);
        }

        // ════════════════════════════════════════════════════════════
        // DOCUMENT READY
        // ════════════════════════════════════════════════════════════
        $(function () {

            // Panel de filtros
            initS2('#select-distrito-filtro', s2optsFiltro());
            initS2('#select-unidad-filtro',   s2optsFiltro());
            initS2('#select-empleado-filtro', s2optsFiltro());

            // Modal editar
            initS2('#select-distrito-editar', s2optsEditar());
            initS2('#select-unidad-editar',   s2optsEditar());
            initS2('#select-empleado-editar', s2optsEditar());

            // ── Cascada filtros: distrito -> unidad ──────────────────
            $('#select-distrito-filtro').on('change', function () {
                var idDistrito = $(this).val();
                resetSelect('#select-unidad-filtro',   '— Seleccionar distrito primero —', true,  s2optsFiltro());
                resetSelect('#select-empleado-filtro', '— Seleccionar unidad primero —',   true,  s2optsFiltro());
                if (!idDistrito) return;

                openLoading();
                axios.post(urlAdmin + '/admin/empleados/buscarunidad', { id: idDistrito })
                    .then(function (r) {
                        closeLoading();
                        if (r.data.success === 1) {
                            var $s = $('#select-unidad-filtro');
                            $s.empty().append('<option value="">— Todas —</option>');
                            r.data.arrayUnidad.forEach(function (v) {
                                $s.append('<option value="' + v.id + '">' + v.nombre + '</option>');
                            });
                            $s.prop('disabled', false);
                            initS2('#select-unidad-filtro', s2optsFiltro());
                        } else { toastr.error('No se encontraron unidades'); }
                    })
                    .catch(function () { closeLoading(); toastr.error('Error al cargar unidades'); });
            });

            // ── Cascada filtros: unidad -> empleado ──────────────────
            $('#select-unidad-filtro').on('change', function () {
                var idUnidad = $(this).val();
                resetSelect('#select-empleado-filtro', '— Seleccionar unidad primero —', true, s2optsFiltro());
                if (!idUnidad) return;

                openLoading();
                axios.post(urlAdmin + '/admin/empleados/buscarunidad-empleado/reporte', { id: idUnidad })
                    .then(function (r) {
                        closeLoading();
                        if (r.data.success === 1) {
                            var $s = $('#select-empleado-filtro');
                            $s.empty().append('<option value="">— Todos —</option>');
                            r.data.arrayEmpleados.forEach(function (v) {
                                $s.append('<option value="' + v.id + '">' + v.nombreCompleto + '</option>');
                            });
                            $s.prop('disabled', false);
                            initS2('#select-empleado-filtro', s2optsFiltro());
                        } else { toastr.error('No se encontraron empleados'); }
                    })
                    .catch(function () { closeLoading(); toastr.error('Error al cargar empleados'); });
            });

            // ── Cascada modal editar: distrito -> unidad (manual) ────
            // Solo se dispara cuando el USUARIO cambia el distrito
            $('#select-distrito-editar').on('select2:select select2:unselect', function () {
                buscarUnidadEditar(null, null, false);
            });

            // ── Cascada modal editar: unidad -> empleado (manual) ────
            $('#select-unidad-editar').on('select2:select select2:unselect', function () {
                buscarEmpleadoEditar(null, false);
            });

            // ── Limpiar modal al cerrar ───────────────────────────────
            $('#modalEditar').on('hidden.bs.modal', function () {
                limpiarCamposEditar();
            });
        });

        // ════════════════════════════════════════════════════════════
        // HELPERS MODAL EDITAR
        // ════════════════════════════════════════════════════════════
        function limpiarCamposEditar() {
            $('#id-editar').val('');
            $('#fecha-editar').val('');
            $('#descripcion-editar').val('');
            $('#jefe-firma-editar').val('');
            $('#cargo-firma-editar').val('');
            $('#material-linea-editar').val('');

            // Distrito: NO vaciar opciones, solo limpiar selección
            $('#select-distrito-editar').val('').trigger('change.select2');

            // Unidad y empleado: estos sí se vacían porque se cargan dinámicamente
            resetSelect('#select-unidad-editar',   '— Seleccionar distrito primero —', false, s2optsEditar());
            resetSelect('#select-empleado-editar', '— Seleccionar unidad primero —',   false, s2optsEditar());
        }

        // ════════════════════════════════════════════════════════════
        // CASCADA MODAL EDITAR
        // ════════════════════════════════════════════════════════════
        function buscarUnidadEditar(idUnidadPre, idEmpleadoPre, silencioso) {
            var idDistrito = $('#select-distrito-editar').val();

            if (!idDistrito) {
                resetSelect('#select-unidad-editar',   '— Seleccionar distrito primero —', false, s2optsEditar());
                resetSelect('#select-empleado-editar', '— Seleccionar unidad primero —',   false, s2optsEditar());
                if (silencioso) closeLoading();
                return;
            }

            if (!silencioso) openLoading();

            axios.post(urlAdmin + '/admin/empleados/buscarunidad', { id: idDistrito })
                .then(function (r) {
                    if (r.data.success === 1) {
                        var $s = $('#select-unidad-editar');
                        if ($s.hasClass('select2-hidden-accessible')) $s.select2('destroy');
                        $s.empty().append('<option value="">— Seleccionar unidad —</option>');
                        r.data.arrayUnidad.forEach(function (v) {
                            $s.append('<option value="' + v.id + '">' + v.nombre + '</option>');
                        });
                        if (idUnidadPre) $s.val(idUnidadPre);
                        $s.select2(s2optsEditar());

                        if (idUnidadPre) {
                            // Continúa la cascada → ella cierra el loading
                            buscarEmpleadoEditar(idEmpleadoPre, silencioso);
                        } else {
                            resetSelect('#select-empleado-editar', '— Seleccionar unidad primero —', false, s2optsEditar());
                            closeLoading();
                        }
                    } else {
                        toastr.error('No se encontraron unidades');
                        closeLoading();
                    }
                })
                .catch(function () { closeLoading(); toastr.error('Error al cargar unidades'); });
        }

        function buscarEmpleadoEditar(idEmpleadoPre, silencioso) {
            var idUnidad = $('#select-unidad-editar').val();

            if (!idUnidad) {
                resetSelect('#select-empleado-editar', '— Seleccionar unidad primero —', false, s2optsEditar());
                closeLoading();
                return;
            }

            if (!silencioso) openLoading();

            axios.post(urlAdmin + '/admin/empleados/buscarunidad-empleado/reporte', { id: idUnidad })
                .then(function (r) {
                    if (r.data.success === 1) {
                        var $s = $('#select-empleado-editar');
                        if ($s.hasClass('select2-hidden-accessible')) $s.select2('destroy');
                        $s.empty().append('<option value="">— Seleccionar empleado —</option>');
                        r.data.arrayEmpleados.forEach(function (v) {
                            $s.append('<option value="' + v.id + '">' + v.nombreCompleto + '</option>');
                        });
                        if (idEmpleadoPre) $s.val(idEmpleadoPre);
                        $s.select2(s2optsEditar());
                    } else {
                        toastr.error('No se encontraron empleados');
                    }
                    closeLoading(); // ← siempre, fin de la cadena
                })
                .catch(function () { closeLoading(); toastr.error('Error al cargar empleados'); });
        }

        // ════════════════════════════════════════════════════════════
        // MODAL EDITAR — abrir
        // ════════════════════════════════════════════════════════════
        function modalEditar(id) {
            limpiarCamposEditar();
            openLoading();

            axios.post(urlAdmin + '/admin/historial/salidas/informacion', { id: id })
                .then(function (response) {
                    if (response.data.success === 1) {
                        var s = response.data.salida;

                        $('#id-editar').val(s.id);
                        $('#fecha-editar').val(s.fecha ? s.fecha.substring(0, 10) : '');
                        $('#descripcion-editar').val(s.descripcion      ?? '');
                        $('#jefe-firma-editar').val(s.jefe_firma         ?? '');
                        $('#cargo-firma-editar').val(s.cargo_firma       ?? '');
                        $('#material-linea-editar').val(s.material_linea ?? '');

                        $('#modalEditar').modal('show');

                        if (s.id_distrito) {
                            // Las opciones de distrito ya están en el DOM (Blade las renderizó)
                            // Solo seteamos el valor y refrescamos select2
                            $('#select-distrito-editar').val(s.id_distrito).trigger('change.select2');
                            // Cascada con preselección
                            buscarUnidadEditar(s.id_unidad_empleado, s.id_empleado, true);
                        } else {
                            closeLoading();
                        }
                    } else {
                        closeLoading();
                        toastr.error('No se pudo cargar la información.');

                    }
                })
                .catch(function () { closeLoading(); toastr.error('Error al obtener información'); });
        }

        // ════════════════════════════════════════════════════════════
        // MODAL EDITAR — guardar
        // ════════════════════════════════════════════════════════════
        function editar() {
            var id            = $('#id-editar').val();
            var fecha         = $('#fecha-editar').val().trim();
            var idEmpleado    = $('#select-empleado-editar').val();
            var descripcion   = $('#descripcion-editar').val().trim();
            var jefeFirma     = $('#jefe-firma-editar').val().trim();
            var cargoFirma    = $('#cargo-firma-editar').val().trim();
            var materialLinea = $('#material-linea-editar').val().trim();

            if (!fecha)      { toastr.error('La fecha es requerida');        return; }
            if (!idEmpleado) { toastr.error('Debe seleccionar un empleado'); return; }

            openLoading();
            var fd = new FormData();
            fd.append('id',             id);
            fd.append('fecha',          fecha);
            fd.append('id_empleado',    idEmpleado);
            fd.append('descripcion',    descripcion);
            fd.append('jefe_firma',     jefeFirma);
            fd.append('cargo_firma',    cargoFirma);
            fd.append('material_linea', materialLinea);

            axios.post(urlAdmin + '/admin/historial/salidas/editar', fd)
                .then(function (response) {
                    closeLoading();
                    if (response.data.success === 1) {
                        toastr.success('Salida actualizada correctamente');
                        $('#modalEditar').modal('hide');
                        recargar();
                    } else if (response.data.success === 2) {
                        Swal.fire({
                            title: 'Fecha inválida',
                            html:  'El material <b>' + response.data.nombre_material + '</b> ' +
                                'tiene fecha de ingreso <b>' + response.data.fecha_ingreso + '</b>.<br><br>' +
                                'La fecha de salida (<b>' + response.data.fecha_salida + '</b>) ' +
                                'no puede ser anterior al ingreso.',
                            type:  'warning',
                            confirmButtonColor: '#d33',
                            confirmButtonText:  'Entendido'
                        });
                    } else {
                        toastr.error('Error al actualizar');
                    }
                })
                .catch(function () { closeLoading(); toastr.error('Error al actualizar'); });
        }

        // ════════════════════════════════════════════════════════════
        // DATATABLE
        // ════════════════════════════════════════════════════════════
        function initDataTable() {
            if ($.fn.DataTable.isDataTable('#tabla')) {
                $('#tabla').DataTable().destroy();
            }
            $('#tabla').DataTable({
                paging: true, lengthChange: true, searching: true,
                ordering: true, info: true, autoWidth: false, responsive: true,
                pagingType: 'full_numbers',
                order: [[0, 'desc']],
                lengthMenu: [[50, 100, -1], [50, 100, 'Todo']],
                language: {
                    sProcessing: 'Procesando...', sLengthMenu: 'Mostrar _MENU_ registros',
                    sZeroRecords: 'No se encontraron resultados',
                    sEmptyTable: 'Ningún dato disponible en esta tabla',
                    sInfo: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    sInfoEmpty: 'Mostrando 0 a 0 de 0 registros',
                    sInfoFiltered: '(filtrado de _MAX_ registros)',
                    sSearch: 'Buscar:',
                    oPaginate: { sFirst: 'Primero', sLast: 'Último', sNext: 'Siguiente', sPrevious: 'Anterior' }
                },
                columnDefs: [
                    { targets: 0,  orderData: 0 },
                    { targets: -1, orderable: false, searchable: false }
                ],
                dom: "<'row align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-md-right'f>>" +
                    "tr" +
                    "<'row align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
            });
            $('#tabla_length select').addClass('form-control form-control-sm');
            $('#tabla_filter input').addClass('form-control form-control-sm').css('display', 'inline-block');
        }

        // ════════════════════════════════════════════════════════════
        // FILTROS Y TABLA
        // ════════════════════════════════════════════════════════════
        function cargarTablaConFiltros(filtros) {
            openLoading();
            $.ajax({
                url:    "{{ url('/admin/historial/salidas/tabla') }}",
                method: 'POST',
                data:   { _token: "{{ csrf_token() }}", ...filtros },
                success: function (html) {
                    $('#tablaDatatable').html(html);
                    initDataTable();
                },
                error: function () {
                    closeLoading();
                    toastr.error('Error al cargar la tabla');
                }
            });
        }

        window.recargar = function () { buscarConFiltros(); };

        function buscarConFiltros() {
            cargarTablaConFiltros({
                id_distrito:  $('#select-distrito-filtro').val() || '',
                id_unidad:    $('#select-unidad-filtro').val()   || '',
                id_empleado:  $('#select-empleado-filtro').val() || '',
                fecha_desde:  $('#fecha-desde-filtro').val()     || '',
                fecha_hasta:  $('#fecha-hasta-filtro').val()     || '',
                buscar_todos: '1'
            });
        }

        function limpiarFiltros() {
            $('#select-distrito-filtro').val('').trigger('change');
            $('#fecha-desde-filtro').val('');
            $('#fecha-hasta-filtro').val('');
            $('#tablaDatatable').html(
                '<div class="text-center text-muted py-5">' +
                '<i class="fas fa-filter fa-2x mb-2 d-block"></i>' +
                '<p>Selecciona al menos un filtro y presiona <b>Buscar</b> para ver resultados.</p>' +
                '</div>'
            );
        }

        // ════════════════════════════════════════════════════════════
        // ELIMINAR SALIDA
        // ════════════════════════════════════════════════════════════
        function eliminar(id) {
            Swal.fire({
                title: '¿Eliminar salida?',
                text:  'Se eliminarán también todos los detalles relacionados. Esta acción no se puede deshacer.',
                type:  'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result.value) {
                    openLoading();
                    axios.post(urlAdmin + '/admin/historial/salidas/eliminar', { id: id })
                        .then(function (r) {
                            closeLoading();
                            if (r.data.success === 1) {
                                toastr.success('Salida eliminada correctamente');
                                recargar();
                            } else { toastr.error('Error al eliminar'); }
                        })
                        .catch(function () { closeLoading(); toastr.error('Error al eliminar'); });
                }
            });
        }

        // ════════════════════════════════════════════════════════════
        // MODAL DETALLE
        // ════════════════════════════════════════════════════════════
        function verDetalle(id, fecha) {
            detalleIdSalidaActual = id;
            $('#detalle-fecha').text(fecha);
            $('#detalle-tbody').html('');
            $('#detalle-contenido, #detalle-vacio').hide();
            $('#detalle-loading').show();
            $('#modalDetalle').modal('show');
            cargarDetalle(id);
        }

        function cargarDetalle(id) {
            $('#detalle-tbody').html('');
            $('#detalle-contenido, #detalle-vacio').hide();
            $('#detalle-loading').show();

            axios.post(urlAdmin + '/admin/historial/salidas/detalle', { id: id })
                .then(function (r) {
                    $('#detalle-loading').hide();
                    if (r.data.success === 1 && r.data.detalle.length > 0) {
                        var html = '';
                        r.data.detalle.forEach(function (fila, i) {
                            html += '<tr>' +
                                '<td>' + (i + 1) + '</td>' +
                                '<td>' + fila.material + '</td>' +
                                '<td class="text-center">' + fila.cantidad_salida + '</td>' +
                                '<td class="text-right">$' + fila.precio + '</td>' +
                                '<td class="text-center">' +
                                '<button type="button" class="btn btn-danger btn-xs" ' +
                                'onclick="confirmarEliminarItem(' + fila.id_detalle + ',' + r.data.detalle.length + ')">' +
                                '<i class="fas fa-trash"></i></button>' +
                                '</td></tr>';
                        });
                        $('#detalle-tbody').html(html);
                        $('#detalle-contenido').show();
                    } else {
                        $('#detalle-vacio').show();
                    }
                })
                .catch(function () {
                    $('#detalle-loading').hide();
                    $('#detalle-vacio').show();
                    toastr.error('Error al cargar el detalle');
                });
        }

        function confirmarEliminarItem(idDetalle, totalItems) {
            var esUltimo = (totalItems === 1);
            Swal.fire({
                title: esUltimo ? '¿Eliminar último ítem?' : '¿Eliminar este ítem?',
                text:  esUltimo
                    ? 'Es el único material registrado. Se eliminará también la salida completa.'
                    : 'Se eliminará este material del detalle de salida.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (result.value) { eliminarItemDetalle(idDetalle, esUltimo); }
            });
        }

        function eliminarItemDetalle(idDetalle, esUltimo) {
            openLoading();
            axios.post(urlAdmin + '/admin/historial/salidas/detalle/eliminar', {
                id_detalle: idDetalle,
                id_salida:  detalleIdSalidaActual
            })
                .then(function (r) {
                    closeLoading();
                    if (r.data.success === 1) {
                        if (esUltimo) {
                            toastr.success('Salida eliminada completamente');
                            $('#modalDetalle').modal('hide');
                            recargar();
                        } else {
                            toastr.success('Ítem eliminado correctamente');
                            cargarDetalle(detalleIdSalidaActual);
                            recargar();
                        }
                    } else { toastr.error('Error al eliminar el ítem'); }
                })
                .catch(function () { closeLoading(); toastr.error('Error al eliminar'); });
        }

    </script>
@endsection

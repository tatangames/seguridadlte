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

    {{-- Modal Editar Salida --}}
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
                    <form id="formulario-editar">
                        <input type="hidden" id="id-editar">


                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fecha <span class="text-danger">*</span></label>
                                <input type="date" id="fecha-editar" class="form-control">
                            </div>
                        </div>

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
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Unidad <span class="text-danger">*</span></label>
                                <select id="select-unidad-editar" class="form-control" style="width:100%">
                                    <option value="">— Seleccionar distrito primero —</option>
                                </select>
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
                                    <input type="text" id="jefe-firma-editar" class="form-control" maxlength="100" placeholder="Nombre en firma">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cargo Firma</label>
                                    <input type="text" id="cargo-firma-editar" class="form-control" maxlength="100" placeholder="Cargo en firma">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Material Línea</label>
                                    <input type="text" id="material-linea-editar" class="form-control" maxlength="400" placeholder="Material línea">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea id="descripcion-editar" class="form-control"
                                      rows="3" maxlength="800"
                                      placeholder="Descripción opcional"></textarea>
                        </div>
                    </form>
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

    {{-- Modal Detalle Salida --}}
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

        // ID de la salida actualmente abierta en el modal detalle
        var detalleIdSalidaActual = null;

        // ════════════════════════════════════════════════════════════
        // SELECT2 — MODAL EDITAR
        // ════════════════════════════════════════════════════════════
        function s2optsEditar() {
            return {
                theme: 'bootstrap-5',
                dropdownParent: $('#modalEditar'),
                width: '100%',
                language: { noResults: function () { return 'Búsqueda no encontrada'; } }
            };
        }

        function initSelect2Editar(selector) {
            if ($(selector).hasClass('select2-hidden-accessible')) {
                $(selector).select2('destroy');
            }
            $(selector).select2(s2optsEditar());
        }

        // ════════════════════════════════════════════════════════════
        // SELECT2 — PANEL DE FILTROS
        // ════════════════════════════════════════════════════════════
        function s2optsFiltro() {
            return {
                theme: 'bootstrap-5',
                width: '100%',
                language: { noResults: function () { return 'Búsqueda no encontrada'; } }
            };
        }

        function initSelect2Filtro(selector) {
            if ($(selector).hasClass('select2-hidden-accessible')) {
                $(selector).select2('destroy');
            }
            $(selector).select2(s2optsFiltro());
        }

        $(function () {

            // Inicializar selects del panel de filtros
            initSelect2Filtro('#select-distrito-filtro');
            initSelect2Filtro('#select-unidad-filtro');
            initSelect2Filtro('#select-empleado-filtro');

            // ── Cascada panel filtros: distrito -> unidad ──
            $('#select-distrito-filtro').on('change', function () {
                const idDistrito = $(this).val();

                // Reset unidad y empleado
                if ($('#select-unidad-filtro').hasClass('select2-hidden-accessible')) {
                    $('#select-unidad-filtro').select2('destroy');
                }
                if ($('#select-empleado-filtro').hasClass('select2-hidden-accessible')) {
                    $('#select-empleado-filtro').select2('destroy');
                }

                if (!idDistrito) {
                    $('#select-unidad-filtro').empty()
                        .append('<option value="">— Seleccionar distrito primero —</option>')
                        .prop('disabled', true);
                    $('#select-empleado-filtro').empty()
                        .append('<option value="">— Seleccionar unidad primero —</option>')
                        .prop('disabled', true);
                    initSelect2Filtro('#select-unidad-filtro');
                    initSelect2Filtro('#select-empleado-filtro');
                    return;
                }

                $('#select-empleado-filtro').empty()
                    .append('<option value="">— Seleccionar unidad primero —</option>')
                    .prop('disabled', true);
                initSelect2Filtro('#select-empleado-filtro');

                openLoading();
                axios.post(urlAdmin + '/admin/empleados/buscarunidad', { id: idDistrito })
                    .then((response) => {
                        closeLoading();
                        if (response.data.success === 1) {
                            $('#select-unidad-filtro').empty()
                                .append('<option value="">— Todas —</option>');

                            response.data.arrayUnidad.forEach(v => {
                                $('#select-unidad-filtro').append(
                                    `<option value="${v.id}">${v.nombre}</option>`
                                );
                            });
                            $('#select-unidad-filtro').prop('disabled', false);
                            initSelect2Filtro('#select-unidad-filtro');
                        } else {
                            toastr.error('No se encontraron unidades');
                        }
                    })
                    .catch(() => { closeLoading(); toastr.error('Error al cargar unidades'); });
            });

            // ── Cascada panel filtros: unidad -> empleado ──
            $('#select-unidad-filtro').on('change', function () {
                const idUnidad = $(this).val();

                if ($('#select-empleado-filtro').hasClass('select2-hidden-accessible')) {
                    $('#select-empleado-filtro').select2('destroy');
                }

                if (!idUnidad) {
                    $('#select-empleado-filtro').empty()
                        .append('<option value="">— Seleccionar unidad primero —</option>')
                        .prop('disabled', true);
                    initSelect2Filtro('#select-empleado-filtro');
                    return;
                }

                openLoading();
                axios.post(urlAdmin + '/admin/empleados/buscarunidad-empleado/reporte', { id: idUnidad })
                    .then((response) => {
                        closeLoading();
                        if (response.data.success === 1) {
                            $('#select-empleado-filtro').empty()
                                .append('<option value="">— Todos —</option>');

                            response.data.arrayEmpleados.forEach(v => {
                                $('#select-empleado-filtro').append(
                                    `<option value="${v.id}">${v.nombreCompleto}</option>`
                                );
                            });
                            $('#select-empleado-filtro').prop('disabled', false);
                            initSelect2Filtro('#select-empleado-filtro');
                        } else {
                            toastr.error('No se encontraron empleados');
                        }
                    })
                    .catch(() => { closeLoading(); toastr.error('Error al cargar empleados'); });
            });

            // ── Bindings de cascada dentro del modal editar ──
            $('#select-distrito-editar').on('change', function () {
                buscarUnidadEditar();
            });

            $('#select-unidad-editar').on('change', function () {
                buscarEmpleadoEditar();
            });
        });

        // ════════════════════════════════════════════════════════════
        // DATATABLE
        // ════════════════════════════════════════════════════════════
        function initDataTable() {
            if ($.fn.DataTable.isDataTable('#tabla')) {
                $('#tabla').DataTable().destroy();
            }
            $('#tabla').DataTable({
                paging: true,
                lengthChange: true,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                responsive: true,
                pagingType: "full_numbers",
                order: [[0, 'desc']],
                lengthMenu: [[50, 100, -1], [50, 100, "Todo"]],
                language: {
                    sProcessing:   "Procesando...",
                    sLengthMenu:   "Mostrar _MENU_ registros",
                    sZeroRecords:  "No se encontraron resultados",
                    sEmptyTable:   "Ningún dato disponible en esta tabla",
                    sInfo:         "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    sInfoEmpty:    "Mostrando 0 a 0 de 0 registros",
                    sInfoFiltered: "(filtrado de _MAX_ registros)",
                    sSearch:       "Buscar:",
                    oPaginate: {
                        sFirst: "Primero", sLast: "Último",
                        sNext: "Siguiente", sPrevious: "Anterior"
                    }
                },
                columnDefs: [
                    { targets: 0, orderData: 0 },
                    { targets: -1, orderable: false, searchable: false }
                ],
                dom:
                    "<'row align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-md-right'f>>" +
                    "tr" +
                    "<'row align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
            });
            $('#tabla_length select').addClass('form-control form-control-sm');
            $('#tabla_filter input').addClass('form-control form-control-sm').css('display', 'inline-block');
        }

        // ── Construye los filtros actuales y carga la tabla vía POST ──
        function cargarTablaConFiltros(filtros) {
            const ruta = "{{ url('/admin/historial/salidas/tabla') }}";
            openLoading();

            $.ajax({
                url: ruta,
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    ...filtros
                },
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

        window.recargar = function () {
            // Recarga manteniendo los filtros que estén actualmente seleccionados
            buscarConFiltros();
        };

        // ── Click en "Buscar" ──
        function buscarConFiltros() {
            const filtros = {
                id_distrito:  $('#select-distrito-filtro').val()  || '',
                id_unidad:    $('#select-unidad-filtro').val()    || '',
                id_empleado:  $('#select-empleado-filtro').val()  || '',
                fecha_desde:  $('#fecha-desde-filtro').val()      || '',
                fecha_hasta:  $('#fecha-hasta-filtro').val()      || ''
            };

            const hayFiltro = Object.values(filtros).some(v => v !== '');

            if (!hayFiltro) {
                toastr.warning('Selecciona al menos un filtro antes de buscar');
                return;
            }

            cargarTablaConFiltros(filtros);
        }

        // ── Click en "Limpiar" ──
        function limpiarFiltros() {
            if ($('#select-distrito-filtro').hasClass('select2-hidden-accessible')) {
                $('#select-distrito-filtro').val('').trigger('change');
            }
            $('#fecha-desde-filtro').val('');
            $('#fecha-hasta-filtro').val('');

            $('#tablaDatatable').html(`
                <div class="text-center text-muted py-5">
                    <i class="fas fa-filter fa-2x mb-2 d-block"></i>
                    <p>Selecciona al menos un filtro y presiona <b>Buscar</b> para ver resultados.</p>
                </div>
            `);
        }

        // ════════════════════════════════════════════════════════════
        // CASCADA — MODAL EDITAR (sin cambios respecto al original)
        // ════════════════════════════════════════════════════════════
        function buscarUnidadEditar(idUnidadPreseleccionar, idEmpleadoPreseleccionar, silencioso) {
            const idDistrito = $('#select-distrito-editar').val();

            if (!idDistrito) {
                if ($('#select-unidad-editar').hasClass('select2-hidden-accessible')) {
                    $('#select-unidad-editar').select2('destroy');
                }
                $('#select-unidad-editar').empty()
                    .append('<option value="">— Seleccionar distrito primero —</option>');
                initSelect2Editar('#select-unidad-editar');

                if ($('#select-empleado-editar').hasClass('select2-hidden-accessible')) {
                    $('#select-empleado-editar').select2('destroy');
                }
                $('#select-empleado-editar').empty()
                    .append('<option value="">— Seleccionar unidad primero —</option>');
                initSelect2Editar('#select-empleado-editar');
                return;
            }

            if (!silencioso) openLoading();
            axios.post(urlAdmin + '/admin/empleados/buscarunidad', { id: idDistrito })
                .then((response) => {
                    if (!silencioso) closeLoading();
                    if (response.data.success === 1) {
                        if ($('#select-unidad-editar').hasClass('select2-hidden-accessible')) {
                            $('#select-unidad-editar').select2('destroy');
                        }
                        $('#select-unidad-editar').empty();
                        $('#select-unidad-editar').append('<option value="">— Seleccionar unidad —</option>');

                        response.data.arrayUnidad.forEach(v => {
                            $('#select-unidad-editar').append(
                                `<option value="${v.id}">${v.nombre}</option>`
                            );
                        });

                        if (idUnidadPreseleccionar) {
                            $('#select-unidad-editar').val(idUnidadPreseleccionar);
                        }
                        initSelect2Editar('#select-unidad-editar');

                        if (idUnidadPreseleccionar) {
                            buscarEmpleadoEditar(idEmpleadoPreseleccionar, silencioso);
                        } else {
                            if ($('#select-empleado-editar').hasClass('select2-hidden-accessible')) {
                                $('#select-empleado-editar').select2('destroy');
                            }
                            $('#select-empleado-editar').empty()
                                .append('<option value="">— Seleccionar unidad primero —</option>');
                            initSelect2Editar('#select-empleado-editar');
                            if (silencioso) closeLoading();
                        }
                    } else {
                        toastr.error('No se encontraron unidades');
                        if (silencioso) closeLoading();
                    }
                })
                .catch(() => { closeLoading(); toastr.error('Error al cargar unidades'); });
        }

        function buscarEmpleadoEditar(idEmpleadoPreseleccionar, silencioso) {
            const idUnidad = $('#select-unidad-editar').val();

            if (!idUnidad) {
                if ($('#select-empleado-editar').hasClass('select2-hidden-accessible')) {
                    $('#select-empleado-editar').select2('destroy');
                }
                $('#select-empleado-editar').empty()
                    .append('<option value="">— Seleccionar unidad primero —</option>');
                initSelect2Editar('#select-empleado-editar');
                return;
            }

            if (!silencioso) openLoading();
            axios.post(urlAdmin + '/admin/empleados/buscarunidad-empleado/reporte', { id: idUnidad })
                .then((response) => {
                    if (!silencioso) closeLoading();
                    if (response.data.success === 1) {
                        if ($('#select-empleado-editar').hasClass('select2-hidden-accessible')) {
                            $('#select-empleado-editar').select2('destroy');
                        }
                        $('#select-empleado-editar').empty();
                        $('#select-empleado-editar').append('<option value="">— Seleccionar empleado —</option>');

                        response.data.arrayEmpleados.forEach(v => {
                            $('#select-empleado-editar').append(
                                `<option value="${v.id}">${v.nombreCompleto}</option>`
                            );
                        });

                        if (idEmpleadoPreseleccionar) {
                            $('#select-empleado-editar').val(idEmpleadoPreseleccionar);
                        }
                        initSelect2Editar('#select-empleado-editar');

                        if (silencioso) closeLoading();
                    } else {
                        toastr.error('No se encontraron empleados');
                        if (silencioso) closeLoading();
                    }
                })
                .catch(() => { closeLoading(); toastr.error('Error al cargar empleados'); });
        }

        function modalEditar(id) {
            openLoading();
            document.getElementById('formulario-editar').reset();

            axios.post(urlAdmin + '/admin/historial/salidas/informacion', { id: id })
                .then((response) => {
                    if (response.data.success === 1) {
                        const s = response.data.salida;
                        $('#id-editar').val(s.id);
                        $('#fecha-editar').val(s.fecha ? s.fecha.substring(0, 10) : '');
                        $('#descripcion-editar').val(s.descripcion ?? '');
                        $('#jefe-firma-editar').val(s.jefe_firma ?? '');
                        $('#cargo-firma-editar').val(s.cargo_firma ?? '');
                        $('#material-linea-editar').val(s.material_linea ?? '');

                        if ($('#select-distrito-editar').hasClass('select2-hidden-accessible')) {
                            $('#select-distrito-editar').select2('destroy');
                        }
                        initSelect2Editar('#select-distrito-editar');

                        if (s.id_distrito) {
                            $('#select-distrito-editar').val(s.id_distrito).trigger('change.select2');
                            buscarUnidadEditar(s.id_unidad_empleado, s.id_empleado, true);
                        } else {
                            initSelect2Editar('#select-unidad-editar');
                            initSelect2Editar('#select-empleado-editar');
                            closeLoading();
                        }

                        $('#modalEditar').modal('show');
                    } else {
                        closeLoading();
                        toastr.error('No se pudo cargar la información');
                    }
                })
                .catch(() => { closeLoading(); toastr.error('Error al obtener información'); });
        }

        function editar() {
            const id             = $('#id-editar').val();
            const fecha          = $('#fecha-editar').val().trim();
            const idEmpleado     = $('#select-empleado-editar').val();
            const descripcion    = $('#descripcion-editar').val().trim();
            const jefeFirma      = $('#jefe-firma-editar').val().trim();
            const cargoFirma     = $('#cargo-firma-editar').val().trim();
            const materialLinea  = $('#material-linea-editar').val().trim();

            if (!fecha) { toastr.error('La fecha es requerida'); return; }
            if (!idEmpleado) { toastr.error('Debe seleccionar un empleado'); return; }

            openLoading();
            const fd = new FormData();
            fd.append('id',             id);
            fd.append('fecha',          fecha);
            fd.append('id_empleado',    idEmpleado);
            fd.append('descripcion',    descripcion);
            fd.append('jefe_firma',     jefeFirma);
            fd.append('cargo_firma',    cargoFirma);
            fd.append('material_linea', materialLinea);

            axios.post(urlAdmin + '/admin/historial/salidas/editar', fd)
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
                        toastr.success('Salida actualizada correctamente');
                        $('#modalEditar').modal('hide');
                        recargar();
                    } else if (response.data.success === 2) {
                        Swal.fire({
                            title: 'Fecha inválida',
                            html: 'El material <b>' + response.data.nombre_material + '</b> ' +
                                'tiene fecha de ingreso <b>' + response.data.fecha_ingreso + '</b>.<br><br>' +
                                'La fecha de salida (<b>' + response.data.fecha_salida + '</b>) ' +
                                'no puede ser anterior al ingreso.',
                            type: 'warning',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'Entendido'
                        });
                    } else {
                        toastr.error('Error al actualizar');
                    }
                })
                .catch(() => { closeLoading(); toastr.error('Error al actualizar'); });
        }

        function eliminar(id) {
            Swal.fire({
                title: '¿Eliminar salida?',
                text: 'Se eliminarán también todos los detalles relacionados. Esta acción no se puede deshacer.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    openLoading();
                    axios.post(urlAdmin + '/admin/historial/salidas/eliminar', { id: id })
                        .then((response) => {
                            closeLoading();
                            if (response.data.success === 1) {
                                toastr.success('Salida eliminada correctamente');
                                recargar();
                            } else {
                                toastr.error('Error al eliminar');
                            }
                        })
                        .catch(() => { closeLoading(); toastr.error('Error al eliminar'); });
                }
            });
        }

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
                .then((response) => {
                    $('#detalle-loading').hide();
                    if (response.data.success === 1 && response.data.detalle.length > 0) {
                        let html = '';
                        response.data.detalle.forEach((fila, i) => {
                            html += `<tr>
                                <td>${i + 1}</td>
                                <td>${fila.material}</td>
                                <td class="text-center">${fila.cantidad_salida}</td>
                                <td class="text-right">$${fila.precio}</td>
                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-danger btn-xs"
                                            onclick="confirmarEliminarItem(${fila.id_detalle}, ${response.data.detalle.length})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>`;
                        });
                        $('#detalle-tbody').html(html);
                        $('#detalle-contenido').show();
                    } else {
                        $('#detalle-vacio').show();
                    }
                })
                .catch(() => {
                    $('#detalle-loading').hide();
                    $('#detalle-vacio').show();
                    toastr.error('Error al cargar el detalle');
                });
        }

        function confirmarEliminarItem(idDetalle, totalItems) {
            var esUltimo  = (totalItems === 1);
            var titulo    = esUltimo ? '¿Eliminar último ítem?' : '¿Eliminar este ítem?';
            var texto     = esUltimo
                ? 'Es el único material registrado. Se eliminará también la salida completa.'
                : 'Se eliminará este material del detalle de salida.';

            Swal.fire({
                title: titulo,
                text: texto,
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    eliminarItemDetalle(idDetalle, esUltimo);
                }
            });
        }

        function eliminarItemDetalle(idDetalle, esUltimo) {
            openLoading();
            axios.post(urlAdmin + '/admin/historial/salidas/detalle/eliminar', {
                id_detalle: idDetalle,
                id_salida:  detalleIdSalidaActual
            })
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
                        if (esUltimo) {
                            toastr.success('Salida eliminada completamente');
                            $('#modalDetalle').modal('hide');
                            recargar();
                        } else {
                            toastr.success('Ítem eliminado correctamente');
                            cargarDetalle(detalleIdSalidaActual);
                            recargar();
                        }
                    } else {
                        toastr.error('Error al eliminar el ítem');
                    }
                })
                .catch(() => { closeLoading(); toastr.error('Error al eliminar'); });
        }

    </script>
@endsection

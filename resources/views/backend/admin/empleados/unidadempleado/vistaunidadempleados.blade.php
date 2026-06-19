@extends('adminlte::page')

@section('title', 'Listado de Unidades')

@section('content_header')
    <h1>Listado de Unidades</h1>
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
        <a href="#" class="nav-link" data-toggle="dropdown" role="button">
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
        .badge-jefe-asig {
            background: #d4edda; color: #155724;
            border: 1px solid #c3e6cb; border-radius: 12px;
            padding: 2px 8px; font-size: 11px; font-weight: 600;
            display: inline-block; margin: 1px 2px;
        }
        .badge-sin-jefe {
            background: #f8d7da; color: #721c24;
            border: 1px solid #f5c6cb; border-radius: 12px;
            padding: 2px 9px; font-size: 11px; font-weight: 700;
            display: inline-block;
        }
        .nombre-uni { font-weight: 600; color: #1a2d55; }
        #tablaUnidad thead tr th {
            background: #2156af; color: #fff;
            font-size: 12px; font-weight: 700;
            text-transform: uppercase; border: none !important;
            white-space: nowrap; padding: 10px 12px;
        }
        #tablaUnidad tbody tr:hover { background: #eef3ff !important; }
        #tablaUnidad tbody td { vertical-align: middle; font-size: 13px; }

        /* Flechas de ordenamiento visibles sobre fondo azul */
        #tablaUnidad thead th.sorting:after,
        #tablaUnidad thead th.sorting_asc:after,
        #tablaUnidad thead th.sorting_desc:after {
            color: rgba(255,255,255,0.7) !important;
        }
        #tablaUnidad thead th.sorting_asc:after  { color: #fff !important; }
        #tablaUnidad thead th.sorting_desc:after { color: #fff !important; }
    </style>

    <div id="divcontenedor">

        <section class="content-header">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <button type="button"
                            style="font-weight:normal; background-color:#2156af; color:white !important;"
                            id="btn-nueva-unidad"
                            class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nueva Unidad
                    </button>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Unidad</li>
                        <li class="breadcrumb-item active">Listado de Unidades</li>
                    </ol>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div id="tablaDatatable"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══ MODAL AGREGAR UNIDAD ══ --}}
        <div class="modal fade" id="modalAgregar">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Nueva Unidad</h4>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form id="formulario-nuevo">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Distrito:</label>
                                    <select class="form-control" id="select-distrito">
                                        @foreach($arrayDistritos as $sel)
                                            <option value="{{ $sel->id }}">{{ $sel->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Nombre de Unidad</label>
                                    <input type="text" maxlength="100" class="form-control" id="unidad-nuevo" autocomplete="off">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button"
                                style="font-weight:bold; background-color:#2156af; color:white !important;"
                                class="btn btn-sm"
                                id="btn-guardar-nuevo">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ MODAL EDITAR UNIDAD ══ --}}
        <div class="modal fade" id="modalEditar">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Editar Unidad</h4>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form id="formulario-editar">
                            <div class="card-body">
                                <input type="hidden" id="id-editar">
                                <div class="form-group">
                                    <label>Distrito:</label>
                                    <select class="form-control" id="select-distrito-editar"></select>
                                </div>
                                <div class="form-group">
                                    <label>Nombre de Unidad</label>
                                    <input type="text" maxlength="100" class="form-control" id="unidad-editar" autocomplete="off">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button"
                                class="btn btn-success btn-sm"
                                id="btn-guardar-editar">
                            Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ MODAL GESTIONAR JEFES ══ --}}
        <div class="modal fade" id="modalJefes">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            Jefes de: <span id="titulo-unidad" class="text-primary"></span>
                        </h4>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="id-unidad-jefes">
                        <p class="text-muted" style="font-size:12px">
                            Selecciona uno o varios empleados que serán jefes responsables de esta unidad.
                        </p>
                        <div class="form-group">
                            <label>Agregar jefe a esta unidad:</label>
                            <div class="input-group">
                                <select class="form-control" id="select-agregar-jefe">
                                    <option value="">Seleccionar empleado…</option>
                                </select>
                                <div class="input-group-append">
                                    <button class="btn btn-primary btn-sm" id="btn-agregar-jefe">
                                        <i class="fas fa-plus"></i> Agregar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <label>Jefes actualmente asignados:</label>
                        <div id="lista-jefes-asignados" style="min-height:40px">
                            <small class="text-muted">Cargando…</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

@stop

@section('js')

    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}" type="text/javascript"></script>

    <script>
        var rutaTabla = "{{ url('/admin/unidadempleado/tabla') }}";

        // ── Inicializar DataTable ─────────────────────────────────────
        function initDataTable() {
            if ($.fn.DataTable.isDataTable('#tablaUnidad')) {
                $('#tablaUnidad').DataTable().destroy();
            }

            $('#tablaUnidad').DataTable({
                paging      : true,
                searching   : true,
                ordering    : true,
                order       : [[0, 'asc']],
                info        : true,
                autoWidth   : false,
                responsive  : true,
                pagingType  : "full_numbers",
                lengthMenu  : [[25, 50, 100, -1], [25, 50, 100, "Todo"]],
                pageLength  : 25,
                language: {
                    sLengthMenu   : "Mostrar _MENU_ registros",
                    sZeroRecords  : "No se encontraron resultados",
                    sEmptyTable   : "Ningún dato disponible",
                    sInfo         : "Mostrando registros del _START_ al _END_ de _TOTAL_",
                    sInfoEmpty    : "Mostrando registros del 0 al 0 de 0",
                    sInfoFiltered : "(filtrado de _MAX_ registros)",
                    sSearch       : "Buscar:",
                    oPaginate     : { sFirst: "Primero", sLast: "Último", sNext: "Siguiente", sPrevious: "Anterior" }
                }
            });
        }

        // ── Cargar parcial ────────────────────────────────────────────
        function cargarTabla() {
            $('#tablaDatatable').load(rutaTabla, function () {
                initDataTable();
            });
        }

        $(document).ready(function () {

            cargarTabla();

            $('#modalAgregar').on('shown.bs.modal', function () {
                reiniciarSelect2('#select-distrito', '#modalAgregar');
            });
            $('#modalEditar').on('shown.bs.modal', function () {
                reiniciarSelect2('#select-distrito-editar', '#modalEditar');
            });
            $('#modalJefes').on('shown.bs.modal', function () {
                reiniciarSelect2('#select-agregar-jefe', '#modalJefes');
            });

            $(document).on('click', '#btn-nueva-unidad',   function () { modalAgregar(); });
            $(document).on('click', '#btn-guardar-nuevo',  function () { nuevo(); });
            $(document).on('click', '#btn-guardar-editar', function () { editar(); });
            $(document).on('click', '#btn-agregar-jefe',   function () { agregarJefeUnidad(); });

            $(document).on('click', '.btn-editar-unidad', function () {
                informacion($(this).data('id'));
            });
            $(document).on('click', '.btn-gestionar-jefes', function () {
                modalAsignarJefes($(this).data('id'), $(this).data('nombre'));
            });
            $(document).on('click', '.btn-quitar-jefe', function () {
                quitarJefeUnidad($(this).data('pivot-id'));
            });
        });

        // ── Select2 ───────────────────────────────────────────────────
        function reiniciarSelect2(selector, parent) {
            if ($(selector).hasClass('select2-hidden-accessible')) {
                $(selector).select2('destroy');
            }
            $(selector).select2({
                theme: "bootstrap-5",
                dropdownParent: $(parent),
                language: { noResults: function () { return "Búsqueda no encontrada"; } }
            });
        }

        // ── CRUD ──────────────────────────────────────────────────────
        function modalAgregar() {
            document.getElementById("formulario-nuevo").reset();
            $('#modalAgregar').modal('show');
        }

        function nuevo() {
            var nombre   = document.getElementById('unidad-nuevo').value.trim();
            var distrito = document.getElementById('select-distrito').value;
            if (!nombre) { toastr.error('Nombre es requerido'); return; }
            openLoading();
            var fd = new FormData();
            fd.append('nombre', nombre);
            fd.append('unidad', distrito);
            axios.post(urlAdmin + '/admin/unidadempleado/nuevo', fd)
                .then(function (r) {
                    closeLoading();
                    if (r.data.success === 1) {
                        toastr.success('Registrado correctamente');
                        $('#modalAgregar').modal('hide');
                        cargarTabla();
                    } else { toastr.error('Error al registrar'); }
                })
                .catch(function () { toastr.error('Error al registrar'); closeLoading(); });
        }

        function informacion(id) {
            openLoading();
            document.getElementById("formulario-editar").reset();
            axios.post(urlAdmin + '/admin/unidadempleado/informacion', { 'id': id })
                .then(function (r) {
                    closeLoading();
                    if (r.data.success === 1) {
                        $('#id-editar').val(id);
                        $('#unidad-editar').val(r.data.info.nombre);
                        document.getElementById("select-distrito-editar").options.length = 0;
                        $.each(r.data.arrayDistrito, function (k, v) {
                            var sel = r.data.info.id_distrito == v.id ? ' selected' : '';
                            $('#select-distrito-editar').append(
                                '<option value="' + v.id + '"' + sel + '>' + v.nombre + '</option>'
                            );
                        });
                        $('#modalEditar').modal('show');
                    } else { toastr.error('Información no encontrada'); }
                })
                .catch(function () { closeLoading(); toastr.error('Información no encontrada'); });
        }

        function editar() {
            var id       = document.getElementById('id-editar').value;
            var nombre   = document.getElementById('unidad-editar').value.trim();
            var distrito = document.getElementById('select-distrito-editar').value;
            if (!nombre) { toastr.error('Nombre es requerido'); return; }
            openLoading();
            var fd = new FormData();
            fd.append('id',       id);
            fd.append('nombre',   nombre);
            fd.append('distrito', distrito);
            axios.post(urlAdmin + '/admin/unidadempleado/editar', fd)
                .then(function (r) {
                    closeLoading();
                    if (r.data.success === 1) {
                        toastr.success('Actualizado correctamente');
                        $('#modalEditar').modal('hide');
                        cargarTabla();
                    } else { toastr.error('Error al actualizar'); }
                })
                .catch(function () { toastr.error('Error al actualizar'); closeLoading(); });
        }

        // ── Gestionar Jefes ───────────────────────────────────────────
        function modalAsignarJefes(idUnidad, nombreUnidad) {
            $('#id-unidad-jefes').val(idUnidad);
            $('#titulo-unidad').text(nombreUnidad);
            $('#lista-jefes-asignados').html('<small class="text-muted">Cargando…</small>');
            document.getElementById("select-agregar-jefe").options.length = 0;
            $('#select-agregar-jefe').append('<option value="">Seleccionar empleado…</option>');
            $('#modalJefes').modal('show');

            openLoading();
            axios.post(urlAdmin + '/admin/unidadempleado/jefes/informacion', { 'id': idUnidad })
                .then(function (r) {
                    closeLoading();
                    if (r.data.success === 1) {
                        $.each(r.data.arrayJefes, function (k, v) {
                            $('#select-agregar-jefe').append(
                                '<option value="' + v.id + '">' + v.nombre_completo + '</option>'
                            );
                        });
                        $('#select-agregar-jefe').trigger('change');
                        renderJefesAsignados(r.data.asignados);
                    } else { toastr.error('Información no encontrada'); }
                })
                .catch(function () { closeLoading(); toastr.error('Error al cargar'); });
        }

        function renderJefesAsignados(asignados) {
            var html = '';
            if (asignados.length === 0) {
                html = '<p class="text-muted mb-0"><small>Ningún jefe asignado aún.</small></p>';
            } else {
                asignados.forEach(function (j) {
                    html += '<div class="d-flex align-items-center justify-content-between mb-1 p-2" '
                        + 'style="background:#f8f9fa; border-radius:6px; border:1px solid #dee2e6">'
                        + '<span><i class="fas fa-user-tie mr-2 text-success"></i>'
                        + '<strong>' + j.nombre + '</strong>'
                        + ' <small class="text-muted">(' + j.cargo + ')</small></span>'
                        + '<button class="btn btn-danger btn-xs btn-quitar-jefe" data-pivot-id="' + j.pivot_id + '">'
                        + '<i class="fas fa-times"></i> Quitar</button>'
                        + '</div>';
                });
            }
            $('#lista-jefes-asignados').html(html);

            var idUnidad   = $('#id-unidad-jefes').val();
            var badgesHtml = '';
            if (asignados.length === 0) {
                badgesHtml = '<span class="badge-sin-jefe">Sin asignar</span>';
            } else {
                asignados.forEach(function (j) {
                    badgesHtml += '<span class="badge-jefe-asig">'
                        + '<i class="fas fa-user-tie" style="font-size:9px"></i> '
                        + j.nombre + '</span>';
                });
            }

            $('#tablaUnidad tbody tr').each(function () {
                var $btn = $(this).find('.btn-gestionar-jefes[data-id="' + idUnidad + '"]');
                if ($btn.length) {
                    $(this).find('td').eq(2).html(badgesHtml);
                }
            });
        }

        function agregarJefeUnidad() {
            var idUnidad   = document.getElementById('id-unidad-jefes').value;
            var idEmpleado = document.getElementById('select-agregar-jefe').value;
            if (!idEmpleado) { toastr.error('Selecciona un empleado'); return; }
            openLoading();
            var fd = new FormData();
            fd.append('id_unidad',   idUnidad);
            fd.append('id_empleado', idEmpleado);
            axios.post(urlAdmin + '/admin/unidadempleado/jefes/agregar', fd)
                .then(function (r) {
                    closeLoading();
                    if (r.data.success === 1) {
                        toastr.success('Jefe asignado');
                        renderJefesAsignados(r.data.asignados);
                    } else if (r.data.success === 2) {
                        toastr.warning('Este jefe ya está asignado a esta unidad');
                    } else { toastr.error('Error al asignar'); }
                })
                .catch(function () { toastr.error('Error al asignar'); closeLoading(); });
        }

        function quitarJefeUnidad(pivotId) {
            openLoading();
            var fd = new FormData();
            fd.append('pivot_id', pivotId);
            axios.post(urlAdmin + '/admin/unidadempleado/jefes/quitar', fd)
                .then(function (r) {
                    closeLoading();
                    if (r.data.success === 1) {
                        toastr.success('Jefe removido');
                        renderJefesAsignados(r.data.asignados);
                    } else { toastr.error('Error al remover'); }
                })
                .catch(function () { toastr.error('Error al remover'); closeLoading(); });
        }

    </script>

@endsection

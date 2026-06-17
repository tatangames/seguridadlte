@extends('adminlte::page')

@section('title', 'Empleados')

@section('content_header')
    <h1>Listado de Empleados</h1>
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
        .badge-jefe {
            background: #d4edda; color: #155724;
            border: 1px solid #c3e6cb; border-radius: 12px;
            padding: 2px 10px; font-size: 11px; font-weight: 700;
            display: inline-block;
        }
        .badge-empleado {
            background: #e8eeff; color: #2156af;
            border: 1px solid #c5d3f7; border-radius: 12px;
            padding: 2px 10px; font-size: 11px; font-weight: 700;
            display: inline-block;
        }
        .badge-activo {
            background: #d4edda; color: #155724;
            border: 1px solid #c3e6cb; border-radius: 12px;
            padding: 2px 9px; font-size: 11px; font-weight: 700;
            display: inline-block;
        }
        .badge-inactivo {
            background: #f8d7da; color: #721c24;
            border: 1px solid #f5c6cb; border-radius: 12px;
            padding: 2px 9px; font-size: 11px; font-weight: 700;
            display: inline-block;
        }
        .nombre-emp { font-weight: 600; color: #1a2d55; }
        .dui-txt    { font-family: 'Courier New', monospace; color: #555; font-size: 12px; }
        .jefe-txt   { font-size: 12px; color: #555; }

        #tablaEmpleados thead tr th {
            background: #2156af; color: #fff;
            font-size: 12px; font-weight: 700;
            text-transform: uppercase; border: none !important;
            white-space: nowrap; padding: 10px 12px;
        }
        #tablaEmpleados thead th.sorting:after,
        #tablaEmpleados thead th.sorting_asc:after,
        #tablaEmpleados thead th.sorting_desc:after { color: rgba(255,255,255,0.7) !important; }
        #tablaEmpleados thead th.sorting_asc:after,
        #tablaEmpleados thead th.sorting_desc:after  { color: #fff !important; }
        #tablaEmpleados tbody tr:hover { background: #eef3ff !important; }
        #tablaEmpleados tbody td { vertical-align: middle; font-size: 13px; }

        .grupo-jefe-directo.oculto { display: none; }
        .select2-container--open   { z-index: 99999 !important; }
        .select2-dropdown          { z-index: 99999 !important; }

        .filtros-panel {
            background: linear-gradient(135deg, #1a3a6b 0%, #2156af 100%);
            border-radius: 10px; padding: 16px 20px 8px;
            margin-bottom: 16px;
            box-shadow: 0 4px 18px rgba(33,86,175,0.18);
        }
        .filtros-panel label {
            color: #c8d8f8; font-size: 11px; font-weight: 700;
            letter-spacing: .07em; text-transform: uppercase;
            margin-bottom: 4px; display: block;
        }
        .filtros-panel .form-control {
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.22);
            color: #fff; border-radius: 6px; font-size: 13px; height: 34px;
        }
        .filtros-panel .form-control::placeholder { color: rgba(255,255,255,.5); }
        .filtros-panel .form-control:focus {
            background: rgba(255,255,255,0.18); border-color: #82aaff;
            color: #fff; box-shadow: none;
        }
        .filtros-panel select option { color: #222; background: #fff; }
        .filtros-panel .btn-limpiar {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            color: #fff; border-radius: 6px; font-size: 12px;
            height: 34px; padding: 0 16px; cursor: pointer;
            transition: background .2s; width: 100%;
        }
        .filtros-panel .btn-limpiar:hover { background: rgba(255,255,255,0.28); }

        .resumen-badges .badge-stat {
            display: inline-flex; align-items: center; gap: 6px;
            background: #f0f4ff; border: 1px solid #d0dcf7; color: #2156af;
            border-radius: 20px; padding: 4px 13px;
            font-size: 12px; font-weight: 600;
            margin-right: 8px; margin-bottom: 6px;
        }
        .resumen-badges .badge-stat .dot { width: 8px; height: 8px; border-radius: 50%; }
        .resumen-badges .badge-stat .dot.total   { background: #6c757d; }
        .resumen-badges .badge-stat .dot.jefe    { background: #28a745; }
        .resumen-badges .badge-stat .dot.emp     { background: #2156af; }
        .resumen-badges .badge-stat .dot.activo  { background: #28a745; }
        .resumen-badges .badge-stat .dot.inactivo{ background: #dc3545; }

        /* ── Toggle ── */
        .jefe-checkbox-hidden { display: none; }
        .jefe-toggle-btn {
            display: inline-flex; align-items: center; gap: 10px;
            cursor: pointer; background: #f1f3f5;
            border: 2px solid #dee2e6; border-radius: 50px;
            padding: 8px 20px; font-size: 13px; font-weight: 600;
            color: #6c757d; transition: all .25s ease;
            user-select: none; min-width: 150px;
        }
        .jefe-toggle-btn .jefe-toggle-icon {
            width: 26px; height: 26px; border-radius: 50%;
            background: #dee2e6;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; color: #6c757d;
            transition: all .25s ease; flex-shrink: 0;
        }
        .jefe-checkbox-hidden:checked + .jefe-toggle-btn {
            background: #d4edda; border-color: #28a745; color: #155724;
        }
        .jefe-checkbox-hidden:checked + .jefe-toggle-btn .jefe-toggle-icon {
            background: #28a745; color: #fff;
        }

        .field-label {
            font-size: 11px; font-weight: 700; color: #6b7a99;
            text-transform: uppercase; letter-spacing: .06em;
            margin-bottom: 5px; display: block;
        }
    </style>

    <div id="divcontenedor">

        <section class="content-header">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <button type="button" id="btn-nuevo-empleado" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nuevo Empleado
                    </button>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">Empleados</li>
                        <li class="breadcrumb-item active">Listado</li>
                    </ol>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">

                {{-- Filtros --}}
                <div class="filtros-panel">
                    <div class="row align-items-end">
                        <div class="col-md-3 col-sm-6 mb-2">
                            <label><i class="fas fa-search mr-1"></i>Buscar</label>
                            <input type="text" id="filtro-buscar" class="form-control" placeholder="Nombre, DUI…" autocomplete="off">
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label><i class="fas fa-map-marker-alt mr-1"></i>Distrito</label>
                            <select id="filtro-distrito" class="form-control">
                                <option value="">Todos</option>
                                @foreach($arrayDistrito as $dist)
                                    <option value="{{ $dist->nombre }}">{{ $dist->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label><i class="fas fa-user-tie mr-1"></i>Rol</label>
                            <select id="filtro-rol" class="form-control">
                                <option value="">Todos</option>
                                <option value="1">Solo Jefes</option>
                                <option value="0">Solo Empleados</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label><i class="fas fa-power-off mr-1"></i>Estado</label>
                            <select id="filtro-activo" class="form-control">
                                <option value="">Todos</option>
                                <option value="1">Activos</option>
                                <option value="0">Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label>&nbsp;</label>
                            <button class="btn-limpiar" id="btn-limpiar-filtros">
                                <i class="fas fa-times mr-1"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Badges --}}
                <div class="resumen-badges mb-3">
                    <span class="badge-stat"><span class="dot total"></span> Total: <strong id="cnt-total">0</strong></span>
                    <span class="badge-stat"><span class="dot jefe"></span> Jefes: <strong id="cnt-jefes">0</strong></span>
                    <span class="badge-stat"><span class="dot emp"></span> Empleados: <strong id="cnt-empleados">0</strong></span>
                    <span class="badge-stat"><span class="dot activo"></span> Activos: <strong id="cnt-activos">0</strong></span>
                    <span class="badge-stat"><span class="dot inactivo"></span> Inactivos: <strong id="cnt-inactivos">0</strong></span>
                </div>

                {{-- Tabla --}}
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


        {{-- ══ MODAL AGREGAR EMPLEADO ══ --}}
        <div class="modal fade" id="modalAgregar" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg,#1a3a6b,#2156af)">
                        <h4 class="modal-title" style="color:#fff">
                            <i class="fas fa-user-plus mr-2"></i>Nuevo Empleado
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff"><span>&times;</span></button>
                    </div>
                    <div class="modal-body" style="padding: 28px 32px;">
                        <form id="formulario-nuevo">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="field-label"><i class="fas fa-map-marker-alt mr-1"></i>Distrito <span class="text-danger">*</span></label>
                                        <select class="form-control" id="nuevo-distrito">
                                            <option value="0">Seleccionar…</option>
                                            @foreach($arrayDistrito as $sel)
                                                <option value="{{ $sel->id }}">{{ $sel->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="field-label"><i class="fas fa-building mr-1"></i>Unidad <span class="text-danger">*</span></label>
                                        <select class="form-control" id="nuevo-unidad">
                                            <option value="">— Seleccione distrito primero —</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="field-label"><i class="fas fa-user mr-1"></i>Nombre <span class="text-danger">*</span></label>
                                        <input type="text" maxlength="100" class="form-control"
                                               id="nuevo-nombre" placeholder="Nombre completo" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="field-label"><i class="fas fa-id-card mr-1"></i>DUI</label>
                                        <input type="text" maxlength="50" class="form-control"
                                               id="nuevo-dui" placeholder="00000000-0" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="field-label"><i class="fas fa-briefcase mr-1"></i>Cargo <span class="text-danger">*</span></label>
                                        <select class="form-control" id="nuevo-cargo">
                                            @foreach($arrayCargo as $sel)
                                                <option value="{{ $sel->id }}">{{ $sel->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="field-label"><i class="fas fa-user-tie mr-1"></i>¿Es Jefe?</label>
                                        <div class="mt-2">
                                            <input type="checkbox" id="nuevo-jefe" class="jefe-checkbox-hidden">
                                            <label for="nuevo-jefe" class="jefe-toggle-btn" id="nuevo-jefe-label">
                                                <span class="jefe-toggle-icon"><i class="fas fa-times"></i></span>
                                                <span class="jefe-toggle-text">No es Jefe</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group grupo-jefe-directo oculto" id="nuevo-grupo-jefe">
                                <label class="field-label"><i class="fas fa-sitemap mr-1"></i>Jefe Directo <small class="text-muted font-weight-normal" style="text-transform:none">(opcional)</small></label>
                                <select class="form-control" id="nuevo-select-jefe">
                                    <option value="">Sin jefe directo</option>
                                    @foreach($arrayEmpleados as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </form>
                    </div>
                    <div class="modal-footer justify-content-between" style="background:#f8faff;">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cerrar
                        </button>
                        <button type="button" class="btn btn-primary px-4" id="btn-guardar-nuevo">
                            <i class="fas fa-save mr-1"></i> Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>



        {{-- ══ MODAL EDITAR EMPLEADO ══ --}}
        <div class="modal fade" id="modalEditar" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg,#1a5c2e,#28a745)">
                        <h4 class="modal-title" style="color:#fff">
                            <i class="fas fa-user-edit mr-2"></i>Editar Empleado
                        </h4>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff"><span>&times;</span></button>
                    </div>
                    <div class="modal-body" style="padding: 28px 32px;">
                        <form id="formulario-editar">

                            <input type="hidden" id="editar-id">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="field-label"><i class="fas fa-map-marker-alt mr-1"></i>Distrito <span class="text-danger">*</span></label>
                                        <select class="form-control" id="editar-distrito">
                                            @foreach($arrayDistrito as $sel)
                                                <option value="{{ $sel->id }}">{{ $sel->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="field-label"><i class="fas fa-building mr-1"></i>Unidad <span class="text-danger">*</span></label>
                                        <select class="form-control" id="editar-unidad">
                                            <option value="">Cargando…</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="field-label"><i class="fas fa-user mr-1"></i>Nombre <span class="text-danger">*</span></label>
                                        <input type="text" maxlength="100" class="form-control"
                                               id="editar-nombre" autocomplete="off" placeholder="Nombre completo">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="field-label"><i class="fas fa-id-card mr-1"></i>DUI</label>
                                        <input type="text" maxlength="50" class="form-control"
                                               id="editar-dui" autocomplete="off" placeholder="00000000-0">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="field-label"><i class="fas fa-briefcase mr-1"></i>Cargo <span class="text-danger">*</span></label>
                                        <select class="form-control" id="editar-cargo">
                                            @foreach($arrayCargo as $sel)
                                                <option value="{{ $sel->id }}">{{ $sel->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="field-label"><i class="fas fa-user-tie mr-1"></i>¿Es Jefe?</label>
                                        <div class="mt-2">
                                            <input type="checkbox" id="editar-jefe" class="jefe-checkbox-hidden">
                                            <label for="editar-jefe" class="jefe-toggle-btn" id="editar-jefe-label">
                                                <span class="jefe-toggle-icon"><i class="fas fa-times"></i></span>
                                                <span class="jefe-toggle-text">No es Jefe</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="field-label"><i class="fas fa-power-off mr-1"></i>Estado</label>
                                        <div class="mt-2">
                                            <input type="checkbox" id="editar-activo" class="jefe-checkbox-hidden">
                                            <label for="editar-activo" class="jefe-toggle-btn" id="editar-activo-label">
                                                <span class="jefe-toggle-icon"><i class="fas fa-times"></i></span>
                                                <span class="jefe-toggle-text">Inactivo</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group grupo-jefe-directo oculto" id="editar-grupo-jefe">
                                <label class="field-label"><i class="fas fa-sitemap mr-1"></i>Jefe Directo <small class="text-muted font-weight-normal" style="text-transform:none">(opcional)</small></label>
                                <select class="form-control" id="editar-select-jefe">
                                    <option value="">Sin jefe directo</option>
                                </select>
                            </div>

                        </form>
                    </div>
                    <div class="modal-footer justify-content-between" style="background:#f8faff;">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cerrar
                        </button>
                        <button type="button" class="btn btn-success px-4" id="btn-guardar-editar">
                            <i class="fas fa-save mr-1"></i> Actualizar
                        </button>
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

        var rutaTabla = "{{ url('/admin/empleados/tabla') }}";

        // ── DataTable ─────────────────────────────────────────────────
        function initDataTable() {
            if ($.fn.DataTable.isDataTable('#tablaEmpleados')) {
                $('#tablaEmpleados').DataTable().destroy();
            }

            var dt = $('#tablaEmpleados').DataTable({
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
                    oPaginate     : { sFirst:"Primero", sLast:"Último", sNext:"Siguiente", sPrevious:"Anterior" }
                },
                drawCallback: actualizarContadores
            });

            // ── Filtro personalizado ──────────────────────────────────
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (settings.nTable.id !== 'tablaEmpleados') return true;
                var buscar   = $('#filtro-buscar').val().toLowerCase().trim();
                var distrito = $('#filtro-distrito').val();
                var rol      = $('#filtro-rol').val();
                var activo   = $('#filtro-activo').val();
                var $row     = $(dt.row(dataIndex).node());
                var rowDist  = String($row.data('distrito') ?? '');
                var rowJefe  = String($row.data('jefe')     ?? '');
                var rowActivo= String($row.data('activo')   ?? '');

                if (buscar) {
                    var txt = [data[0],data[1],data[2],data[3],data[4]].join(' ').toLowerCase();
                    if (txt.indexOf(buscar) === -1) return false;
                }
                if (distrito !== '' && rowDist   !== distrito) return false;
                if (rol      !== '' && rowJefe   !== rol)      return false;
                if (activo   !== '' && rowActivo !== activo)   return false;
                return true;
            });

            $('#filtro-buscar').off('input').on('input', function () { dt.draw(); });
            $('#filtro-distrito, #filtro-rol, #filtro-activo').off('change').on('change', function () { dt.draw(); });
            $('#btn-limpiar-filtros').off('click').on('click', function () {
                $('#filtro-buscar').val('');
                $('#filtro-distrito, #filtro-rol, #filtro-activo').val('');
                dt.draw();
            });

            actualizarContadores();
        }

        function cargarTabla() {
            $('#tablaDatatable').load(rutaTabla, function () {
                initDataTable();
            });
        }

        function actualizarContadores() {
            var dt       = $('#tablaEmpleados').DataTable();
            var total    = dt.rows({ filter: 'applied' }).count();
            var jefes    = 0;
            var activos  = 0;
            dt.rows({ filter: 'applied' }).every(function () {
                var $n = $(this.node());
                if ($n.data('jefe')   == 1) jefes++;
                if ($n.data('activo') == 1) activos++;
            });
            $('#cnt-total').text(total);
            $('#cnt-jefes').text(jefes);
            $('#cnt-empleados').text(total - jefes);
            $('#cnt-activos').text(activos);
            $('#cnt-inactivos').text(total - activos);
        }

        // ── Select2 ───────────────────────────────────────────────────
        function s2(selector, parent) {
            if ($(selector).hasClass('select2-hidden-accessible')) {
                $(selector).select2('destroy');
            }
            $(selector).select2({
                theme: "bootstrap-5",
                dropdownParent: parent ? $(parent) : $('body'),
                width: '100%',
                language: { noResults: function () { return "No encontrado"; } }
            });
        }

        // ── Cargar unidades por distrito ──────────────────────────────
        function cargarUnidades(idDistrito, selectTarget, valorSeleccionado) {
            openLoading();
            axios.post(urlAdmin + '/admin/empleados/buscarunidad', { id: idDistrito })
                .then(function (r) {
                    closeLoading();
                    if (r.data.success === 1) {
                        if ($(selectTarget).hasClass('select2-hidden-accessible')) {
                            $(selectTarget).select2('destroy');
                        }
                        $(selectTarget).empty();
                        $.each(r.data.arrayUnidad, function (k, v) {
                            var sel = valorSeleccionado && v.id == valorSeleccionado ? 'selected' : '';
                            $(selectTarget).append('<option value="' + v.id + '" ' + sel + '>' + v.nombre + '</option>');
                        });
                        var parent = selectTarget === '#nuevo-unidad' ? '#modalAgregar' : '#modalEditar';
                        s2(selectTarget, parent);
                    } else {
                        toastr.error('No se encontraron unidades');
                    }
                })
                .catch(function () { closeLoading(); toastr.error('Error al cargar unidades'); });
        }

        // ── Helpers toggle ────────────────────────────────────────────
        function setJefeToggle(activo) {
            $('#editar-jefe').prop('checked', activo);
            if (activo) {
                $('#editar-jefe-label .jefe-toggle-icon').html('<i class="fas fa-check"></i>');
                $('#editar-jefe-label .jefe-toggle-text').text('Es Jefe');
            } else {
                $('#editar-jefe-label .jefe-toggle-icon').html('<i class="fas fa-times"></i>');
                $('#editar-jefe-label .jefe-toggle-text').text('No es Jefe');
            }
        }

        function setActivoToggle(activo) {
            $('#editar-activo').prop('checked', activo);
            if (activo) {
                $('#editar-activo-label .jefe-toggle-icon').html('<i class="fas fa-check"></i>');
                $('#editar-activo-label .jefe-toggle-text').text('Activo');
                $('#editar-activo-label').css({ background: '#d4edda', borderColor: '#28a745', color: '#155724' });
                $('#editar-activo-label .jefe-toggle-icon').css({ background: '#28a745', color: '#fff' });
            } else {
                $('#editar-activo-label .jefe-toggle-icon').html('<i class="fas fa-times"></i>');
                $('#editar-activo-label .jefe-toggle-text').text('Inactivo');
                $('#editar-activo-label').css({ background: '#f8d7da', borderColor: '#dc3545', color: '#721c24' });
                $('#editar-activo-label .jefe-toggle-icon').css({ background: '#dc3545', color: '#fff' });
            }
        }

        /* ════════════════════════════════════════════════════════════════
         *  DOCUMENT READY
         * ════════════════════════════════════════════════════════════════ */
        $(document).ready(function () {

            cargarTabla();

            // ── Select2 al abrir modales ───────────────────────────────
            $('#modalAgregar').on('shown.bs.modal', function () {
                s2('#nuevo-distrito',    '#modalAgregar');
                s2('#nuevo-unidad',      '#modalAgregar');
                s2('#nuevo-cargo',       '#modalAgregar');
                s2('#nuevo-select-jefe', '#modalAgregar');
            });
            $('#modalEditar').on('shown.bs.modal', function () {
                s2('#editar-distrito',    '#modalEditar');
                s2('#editar-unidad',      '#modalEditar');
                s2('#editar-cargo',       '#modalEditar');
                s2('#editar-select-jefe', '#modalEditar');
            });

            // ── Toggle jefe — Agregar ──────────────────────────────────
            $('#nuevo-jefe').on('change', function () {
                if (this.checked) $('#nuevo-grupo-jefe').removeClass('oculto');
                else              $('#nuevo-grupo-jefe').addClass('oculto');
            });


            // ── Toggle jefe — Agregar ──────────────────────────────────
            $(document).on('change', '#nuevo-jefe', function () {
                if (this.checked) {
                    $('#nuevo-grupo-jefe').removeClass('oculto');
                    $('#nuevo-jefe-label .jefe-toggle-icon').html('<i class="fas fa-check"></i>');
                    $('#nuevo-jefe-label .jefe-toggle-text').text('Es Jefe');
                } else {
                    $('#nuevo-grupo-jefe').addClass('oculto');
                    $('#nuevo-jefe-label .jefe-toggle-icon').html('<i class="fas fa-times"></i>');
                    $('#nuevo-jefe-label .jefe-toggle-text').text('No es Jefe');
                }
            });

            // ── Abrir modal nuevo — reset completo ────────────────────
            $(document).on('click', '#btn-nuevo-empleado', function () {
                document.getElementById('formulario-nuevo').reset();
                $('#nuevo-jefe').prop('checked', false);
                $('#nuevo-jefe-label .jefe-toggle-icon').html('<i class="fas fa-times"></i>');
                $('#nuevo-jefe-label .jefe-toggle-text').text('No es Jefe');
                $('#nuevo-grupo-jefe').addClass('oculto');
                $('#nuevo-unidad').empty().append('<option value="">— Seleccione distrito primero —</option>');
                $('#modalAgregar').modal('show');
            });

            // ── Toggle jefe — Editar ───────────────────────────────────
            $(document).on('change', '#editar-jefe', function () {
                if (this.checked) {
                    $('#editar-grupo-jefe').removeClass('oculto');
                    $('#editar-jefe-label .jefe-toggle-icon').html('<i class="fas fa-check"></i>');
                    $('#editar-jefe-label .jefe-toggle-text').text('Es Jefe');
                } else {
                    $('#editar-grupo-jefe').addClass('oculto');
                    $('#editar-jefe-label .jefe-toggle-icon').html('<i class="fas fa-times"></i>');
                    $('#editar-jefe-label .jefe-toggle-text').text('No es Jefe');
                    $('#editar-select-jefe').val('');
                }
            });

            // ── Toggle activo — Editar ─────────────────────────────────
            $(document).on('change', '#editar-activo', function () {
                setActivoToggle(this.checked);
            });

            // ── Cambio distrito — Agregar ──────────────────────────────
            $(document).on('change', '#nuevo-distrito', function () {
                var id = $(this).val();
                if (!id || id == '0') {
                    $('#nuevo-unidad').empty().append('<option value="">— Seleccione distrito —</option>');
                    return;
                }
                cargarUnidades(id, '#nuevo-unidad', null);
            });

            // ── Cambio distrito — Editar ───────────────────────────────
            $(document).on('change', '#editar-distrito', function () {
                cargarUnidades($(this).val(), '#editar-unidad', null);
            });

            // ── Delegación ─────────────────────────────────────────────
            $(document).on('click', '#btn-nuevo-empleado', function () {
                document.getElementById('formulario-nuevo').reset();
                $('#nuevo-jefe').prop('checked', false);
                $('#nuevo-grupo-jefe').addClass('oculto');
                $('#nuevo-unidad').empty().append('<option value="">— Seleccione distrito primero —</option>');
                $('#modalAgregar').modal('show');
            });

            $(document).on('click', '#btn-guardar-nuevo',  function () { guardarNuevo(); });
            $(document).on('click', '#btn-guardar-editar', function () { guardarEditar(); });

            $(document).on('click', '.btn-editar-empleado', function () {
                cargarInformacion($(this).data('id'));
            });
        });

        // ── Guardar nuevo ─────────────────────────────────────────────
        function guardarNuevo() {
            var nombre   = $('#nuevo-nombre').val().trim();
            var unidad   = $('#nuevo-unidad').val();
            var cargo    = $('#nuevo-cargo').val();
            var dui      = $('#nuevo-dui').val().trim();
            var jefe     = $('#nuevo-jefe').is(':checked') ? 1 : 0;
            var idJefe   = jefe === 1 ? $('#nuevo-select-jefe').val() : '';
            var distrito = $('#nuevo-distrito').val();

            if (!distrito || distrito == '0') { toastr.error('Seleccione un distrito');  return; }
            if (!unidad)                       { toastr.error('Seleccione una unidad');   return; }
            if (!cargo)                        { toastr.error('Seleccione un cargo');     return; }
            if (!nombre)                       { toastr.error('El nombre es requerido');  return; }

            openLoading();
            var fd = new FormData();
            fd.append('nombre',  nombre);
            fd.append('unidad',  unidad);
            fd.append('cargo',   cargo);
            fd.append('dui',     dui);
            fd.append('jefe',    jefe);
            fd.append('id_jefe', idJefe);

            axios.post(urlAdmin + '/admin/empleados/nuevo', fd)
                .then(function (r) {
                    closeLoading();
                    if (r.data.success === 1) {
                        toastr.success('Empleado registrado correctamente');
                        $('#modalAgregar').modal('hide');
                        cargarTabla();
                    } else { toastr.error('Error al registrar'); }
                })
                .catch(function () { toastr.error('Error al registrar'); closeLoading(); });
        }

        // ── Cargar información para editar ────────────────────────────
        function cargarInformacion(id) {
            openLoading();
            axios.post(urlAdmin + '/admin/empleados/informacion', { id: id })
                .then(function (r) {
                    closeLoading();
                    if (r.data.success === 1) {
                        var info = r.data.info;

                        $('#editar-id').val(info.id);
                        $('#editar-nombre').val(info.nombre);
                        $('#editar-dui').val(info.dui ?? '');

                        // Toggle jefe
                        setJefeToggle(info.jefe == 1);
                        if (info.jefe == 1) $('#editar-grupo-jefe').removeClass('oculto');
                        else                $('#editar-grupo-jefe').addClass('oculto');

                        // Toggle activo
                        setActivoToggle(info.activo == 1);

                        // Distrito
                        if ($('#editar-distrito').hasClass('select2-hidden-accessible')) {
                            $('#editar-distrito').select2('destroy');
                        }
                        $('#editar-distrito').val(r.data.idDistrito).trigger('change.select2');
                        s2('#editar-distrito', '#modalEditar');

                        // Unidades
                        if ($('#editar-unidad').hasClass('select2-hidden-accessible')) {
                            $('#editar-unidad').select2('destroy');
                        }
                        $('#editar-unidad').empty();
                        $.each(r.data.arrayUnidad, function (k, v) {
                            var sel = v.id == info.id_unidad_empleado ? 'selected' : '';
                            $('#editar-unidad').append('<option value="' + v.id + '" ' + sel + '>' + v.nombre + '</option>');
                        });
                        s2('#editar-unidad', '#modalEditar');

                        // Cargo
                        $('#editar-cargo').val(info.id_cargo).trigger('change.select2');

                        // Select jefe directo
                        if ($('#editar-select-jefe').hasClass('select2-hidden-accessible')) {
                            $('#editar-select-jefe').select2('destroy');
                        }
                        $('#editar-select-jefe').empty().append('<option value="">Sin jefe directo</option>');
                        $.each(r.data.arrayEmpleados, function (k, v) {
                            var sel = v.id == info.id_jefe ? 'selected' : '';
                            $('#editar-select-jefe').append('<option value="' + v.id + '" ' + sel + '>' + v.nombre_completo + '</option>');
                        });
                        s2('#editar-select-jefe', '#modalEditar');

                        $('#modalEditar').modal('show');
                    } else { toastr.error('Información no encontrada'); }
                })
                .catch(function () { closeLoading(); toastr.error('Error al cargar información'); });
        }

        // ── Guardar editar ────────────────────────────────────────────
        function guardarEditar() {
            var id     = $('#editar-id').val();
            var nombre = $('#editar-nombre').val().trim();
            var unidad = $('#editar-unidad').val();
            var cargo  = $('#editar-cargo').val();
            var dui    = $('#editar-dui').val().trim();
            var jefe   = $('#editar-jefe').is(':checked') ? 1 : 0;
            var activo = $('#editar-activo').is(':checked') ? 1 : 0;
            var idJefe = jefe === 1 ? $('#editar-select-jefe').val() : '';

            if (!nombre) { toastr.error('El nombre es requerido'); return; }
            if (!unidad) { toastr.error('Seleccione una unidad');  return; }

            openLoading();
            var fd = new FormData();
            fd.append('id',      id);
            fd.append('nombre',  nombre);
            fd.append('unidad',  unidad);
            fd.append('cargo',   cargo);
            fd.append('dui',     dui);
            fd.append('jefe',    jefe);
            fd.append('activo',  activo);
            fd.append('id_jefe', idJefe);

            axios.post(urlAdmin + '/admin/empleados/actualizar', fd)
                .then(function (r) {
                    closeLoading();
                    if (r.data.success === 1) {
                        toastr.success('Empleado actualizado correctamente');
                        $('#modalEditar').modal('hide');
                        cargarTabla();
                    } else { toastr.error('Error al actualizar'); }
                })
                .catch(function () { toastr.error('Error al actualizar'); closeLoading(); });
        }

    </script>

@endsection

@extends('adminlte::page')

@section('title', 'Historial / Salidas')

@section('content_header')
    <h1>Historial / Salidas</h1>
@stop

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
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

    <div id="divcontenedor">

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
                                <div id="tablaDatatable"></div>
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

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha <span class="text-danger">*</span></label>
                                    <input type="date" id="fecha-editar" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Área</label>
                                    <input type="text" id="area-editar" class="form-control" maxlength="300" placeholder="Área">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Cargo</label>
                                    <input type="text" id="cargo-editar" class="form-control" maxlength="300" placeholder="Cargo">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Colaborador</label>
                                    <input type="text" id="colaborador-editar" class="form-control" maxlength="300" placeholder="Colaborador">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jefe Inmediato</label>
                                    <input type="text" id="jefe-inmediato-editar" class="form-control" maxlength="300" placeholder="Jefe inmediato">
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

    <script>

        // ID de la salida actualmente abierta en el modal detalle
        var detalleIdSalidaActual = null;

        $(function () {

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

            function cargarTabla() {
                const ruta = "{{ url('/admin/historial/salidas/tabla') }}";
                openLoading();
                $('#tablaDatatable').load(ruta, function () {
                    initDataTable();
                });
            }

            window.recargar = function () { cargarTabla(); };

            cargarTabla();
        });


        function modalEditar(id) {
            openLoading();
            document.getElementById('formulario-editar').reset();
            axios.post(urlAdmin + '/admin/historial/salidas/informacion', { id: id })
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
                        const s = response.data.salida;
                        $('#id-editar').val(s.id);
                        $('#fecha-editar').val(s.fecha ? s.fecha.substring(0, 10) : '');
                        $('#descripcion-editar').val(s.descripcion ?? '');
                        $('#area-editar').val(s.area ?? '');
                        $('#cargo-editar').val(s.cargo ?? '');
                        $('#colaborador-editar').val(s.colaborador ?? '');
                        $('#jefe-inmediato-editar').val(s.jefe_inmediato ?? '');
                        $('#jefe-firma-editar').val(s.jefe_firma ?? '');
                        $('#cargo-firma-editar').val(s.cargo_firma ?? '');
                        $('#material-linea-editar').val(s.material_linea ?? '');
                        $('#modalEditar').modal('show');
                    } else {
                        toastr.error('No se pudo cargar la información');
                    }
                })
                .catch(() => { closeLoading(); toastr.error('Error al obtener información'); });
        }

        function editar() {
            const id             = $('#id-editar').val();
            const fecha          = $('#fecha-editar').val().trim();
            const descripcion    = $('#descripcion-editar').val().trim();
            const area           = $('#area-editar').val().trim();
            const cargo          = $('#cargo-editar').val().trim();
            const colaborador    = $('#colaborador-editar').val().trim();
            const jefeInmediato  = $('#jefe-inmediato-editar').val().trim();
            const jefeFirma      = $('#jefe-firma-editar').val().trim();
            const cargoFirma     = $('#cargo-firma-editar').val().trim();
            const materialLinea  = $('#material-linea-editar').val().trim();

            if (!fecha) { toastr.error('La fecha es requerida'); return; }

            openLoading();
            const fd = new FormData();
            fd.append('id',             id);
            fd.append('fecha',          fecha);
            fd.append('descripcion',    descripcion);
            fd.append('area',           area);
            fd.append('cargo',          cargo);
            fd.append('colaborador',    colaborador);
            fd.append('jefe_inmediato', jefeInmediato);
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

        // ── Eliminar salida completa ────────────────────────────────
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

        // ── Ver detalle ─────────────────────────────────────────────
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

        // ── Confirmar eliminar ítem de detalle ──────────────────────
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

        // ── Ejecutar eliminación del ítem ───────────────────────────
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
                            // La salida completa fue eliminada → cerrar modal y recargar tabla
                            toastr.success('Salida eliminada completamente');
                            $('#modalDetalle').modal('hide');
                            recargar();
                        } else {
                            // Solo se eliminó el ítem → recargar el detalle en el modal
                            toastr.success('Ítem eliminado correctamente');
                            cargarDetalle(detalleIdSalidaActual);
                            recargar(); // refresca la tabla de fondo también
                        }
                    } else {
                        toastr.error('Error al eliminar el ítem');
                    }
                })
                .catch(() => { closeLoading(); toastr.error('Error al eliminar'); });
        }

    </script>
@endsection

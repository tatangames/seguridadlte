@extends('adminlte::page')

@section('title', 'Historial / Entradas')

@section('content_header')
    <h1>Historial / Entradas</h1>
@stop

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Sweetalert2', true)

@include('backend.urlglobal')

@section('content_top_nav_right')
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet"/>
    <link href="{{ asset('css/select2.min.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" type="text/css" rel="stylesheet">

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
                        <h3 class="card-title">Listado de Entradas</h3>
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

    {{-- Modal Editar Entrada --}}
    <div class="modal fade" id="modalEditar" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-edit mr-2"></i>Editar Entrada
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-editar">
                        <input type="hidden" id="id-editar">
                        <div class="form-group">
                            <label>Fecha <span class="text-danger">*</span></label>
                            <input type="date" id="fecha-editar" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Lote / Factura</label>
                            <input type="text" id="lote-editar" class="form-control"
                                   placeholder="Ej: FAC-0001 (opcional)" maxlength="100">
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea id="descripcion-editar" class="form-control"
                                      rows="3" maxlength="800" placeholder="Descripción opcional"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Proveedor</label>
                            <select id="select-proveedor-editar" class="form-control" style="width:100%"></select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" onclick="guardarEdicion()">
                        <i class="fas fa-save mr-1"></i>Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Detalle Entrada --}}
    <div class="modal fade" id="modalDetalle" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-list mr-2"></i>
                        Detalle —
                        <span id="detalle-lote"></span>
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
                                <th>#</th>
                                <th>Material</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-right">Precio Unit.</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                            </thead>
                            <tbody id="detalle-tbody"></tbody>
                        </table>
                    </div>
                    <div id="detalle-vacio" class="text-center text-muted py-4" style="display:none;">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        <p>Esta entrada no tiene materiales registrados.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Editar Detalle --}}
    <div class="modal fade" id="modalEditarDetalle" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-edit mr-2"></i>Editar Material
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-editar-detalle">
                        <input type="hidden" id="detalle-id-editar">
                        <div class="form-group">
                            <label>Material</label>
                            <input type="text" id="detalle-material-editar" class="form-control" disabled>
                        </div>
                        <div class="form-group">
                            <label>Cantidad <span class="text-danger">*</span></label>
                            <input type="number" id="detalle-cantidad-editar" class="form-control"
                                   min="1" placeholder="0">
                        </div>
                        <div class="form-group">
                            <label>Precio unitario <span class="text-danger">*</span></label>
                            <input type="number" id="detalle-precio-editar" class="form-control"
                                   step="0.0001" min="0" placeholder="0.0000">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" onclick="editarDetalle()">
                        <i class="fas fa-save mr-1"></i>Guardar
                    </button>
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
        $(function () {

            // ── Select2 proveedor en modal editar ───────────────────
            $('#select-proveedor-editar').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalEditar'),
                language: { noResults: function () { return 'No encontrado'; } },
            });

            // ── DataTable ───────────────────────────────────────────
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
                const ruta = "{{ url('/admin/historial/entradas/tabla') }}";
                openLoading();
                $('#tablaDatatable').load(ruta, function () {
                    initDataTable();
                });
            }

            window.recargar = function () { cargarTabla(); };

            // Delegación botones detalle
            $(document).on('click', '.btn-editar-detalle', function () {
                const b = $(this);
                modalEditarDetalle(b.data('id'), b.data('material'), b.data('cantidad'), b.data('precio'));
            });
            $(document).on('click', '.btn-eliminar-detalle', function () {
                const b = $(this);
                eliminarDetalle(b.data('id'), b.data('material'));
            });

            cargarTabla();
        });

        // ── Editar cabecera ─────────────────────────────────────────
        function modalEditar(id) {
            openLoading();
            document.getElementById('formulario-editar').reset();
            axios.post(urlAdmin + '/admin/historial/entradas/informacion', { id: id })
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
                        const e = response.data.info;
                        $('#id-editar').val(e.id);
                        $('#fecha-editar').val(e.fecha);
                        $('#lote-editar').val(e.lote ?? '');
                        $('#descripcion-editar').val(e.descripcion ?? '');

                        $('#select-proveedor-editar').empty();
                        $.each(response.data.arrayProveedor, function (key, val) {
                            var selected = (e.id_proveedor == val.id) ? ' selected="selected"' : '';
                            $('#select-proveedor-editar').append(
                                '<option value="' + val.id + '"' + selected + '>' + val.nombre + '</option>'
                            );
                        });
                        $('#select-proveedor-editar').trigger('change');
                        $('#modalEditar').modal('show');
                    } else {
                        toastr.error('No se pudo cargar la información');
                    }
                })
                .catch(() => { closeLoading(); toastr.error('Error al obtener información'); });
        }

        function guardarEdicion() {
            const id          = $('#id-editar').val();
            const fecha       = $('#fecha-editar').val().trim();
            const lote        = $('#lote-editar').val().trim();
            const descripcion = $('#descripcion-editar').val().trim();
            const proveedor   = $('#select-proveedor-editar').val();

            if (!fecha)                   { toastr.error('La fecha es requerida'); return; }
            if (lote.length > 100)        { toastr.error('Lote máximo 100 caracteres'); return; }
            if (descripcion.length > 800) { toastr.error('Descripción máximo 800 caracteres'); return; }

            openLoading();
            const fd = new FormData();
            fd.append('id', id); fd.append('fecha', fecha);
            fd.append('lote', lote); fd.append('descripcion', descripcion);
            fd.append('proveedor', proveedor);

            axios.post(urlAdmin + '/admin/historial/entradas/editar', fd)
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
                        toastr.success('Entrada actualizada correctamente');
                        $('#modalEditar').modal('hide');
                        recargar();
                    } else { toastr.error('Error al actualizar'); }
                })
                .catch(() => { closeLoading(); toastr.error('Error al actualizar'); });
        }

        // ── Eliminar entrada ────────────────────────────────────────
        function eliminar(id) {
            Swal.fire({
                title: '¿Eliminar entrada?',
                text: 'Se eliminarán también todos los materiales relacionados. Esta acción no se puede deshacer.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    openLoading();
                    axios.post(urlAdmin + '/admin/historial/entradas/eliminar', { id: id })
                        .then((response) => {
                            closeLoading();
                            switch (response.data.success) {
                                case 1:  toastr.success('Entrada eliminada correctamente'); recargar(); break;
                                case 0:  toastr.error('La entrada no existe o ya fue eliminada'); recargar(); break;
                                case 99: toastr.error(response.data.msg || 'Ocurrió un error al eliminar'); break;
                                default: toastr.error('Error al eliminar');
                            }
                        })
                        .catch(() => { closeLoading(); toastr.error('Error al eliminar'); });
                }
            });
        }

        // ── Ver detalle ─────────────────────────────────────────────
        function verDetalle(id, lote, fecha) {
            $('#detalle-id-editar').data('entrada-id', id);
            $('#detalle-lote').text(lote || '—');
            $('#detalle-fecha').text(fecha);
            $('#detalle-tbody').html('');
            $('#detalle-contenido, #detalle-vacio').hide();
            $('#detalle-loading').show();
            $('#modalDetalle').modal('show');

            axios.post(urlAdmin + '/admin/historial/entradas/detalle', { id: id })
                .then((response) => {
                    $('#detalle-loading').hide();
                    if (response.data.success === 1 && response.data.detalle.length > 0) {
                        let html = '';
                        response.data.detalle.forEach((fila, i) => {
                            const botones = `
                            <button type="button"
                                    class="btn btn-warning btn-xs btn-editar-detalle mr-1"
                                    data-id="${fila.id}"
                                    data-material="${fila.material}"
                                    data-cantidad="${fila.cantidad_inicial}"
                                    data-precio="${fila.precio_raw}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button"
                                    class="btn btn-danger btn-xs btn-eliminar-detalle"
                                    data-id="${fila.id}"
                                    data-material="${fila.material}">
                                <i class="fas fa-trash"></i>
                            </button>`;
                            html += `<tr>
                            <td>${i+1}</td>
                            <td>${fila.material}</td>
                            <td class="text-center">${fila.cantidad_inicial}</td>
                            <td class="text-right">$${fila.precio}</td>
                            <td class="text-center text-nowrap">${botones}</td>
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

        // ── Editar fila detalle ─────────────────────────────────────
        function modalEditarDetalle(id, material, cantidad, precio) {
            document.getElementById('formulario-editar-detalle').reset();
            $('#detalle-id-editar').val(id);
            $('#detalle-material-editar').val(material);
            $('#detalle-cantidad-editar').val(cantidad);
            $('#detalle-precio-editar').val(precio);
            $('#modalEditarDetalle').modal('show');
        }

        function editarDetalle() {
            const id       = $('#detalle-id-editar').val();
            const cantidad = $('#detalle-cantidad-editar').val().trim();
            const precio   = $('#detalle-precio-editar').val().trim();

            if (!cantidad || isNaN(cantidad) || parseInt(cantidad) < 1) { toastr.error('Cantidad inválida'); return; }
            if (precio === '' || isNaN(precio) || parseFloat(precio) < 0) { toastr.error('Precio inválido'); return; }

            openLoading();
            const fd = new FormData();
            fd.append('id', id); fd.append('cantidad', cantidad); fd.append('precio', precio);

            axios.post(urlAdmin + '/admin/historial/entradas/detalle/editar', fd)
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
                        toastr.success('Actualizado correctamente');
                        $('#modalEditarDetalle').modal('hide');
                        const entradaId = $('#detalle-id-editar').data('entrada-id');
                        const lote      = $('#detalle-lote').text();
                        const fecha     = $('#detalle-fecha').text();
                        verDetalle(entradaId, lote, fecha);
                    } else { toastr.error('Error al actualizar'); }
                })
                .catch(() => { closeLoading(); toastr.error('Error al actualizar'); });
        }

        // ── Eliminar fila detalle ───────────────────────────────────
        function eliminarDetalle(id, material) {
            Swal.fire({
                title: '¿Eliminar material?',
                html: 'Se eliminará: <b>' + material + '</b><br><br><small class="text-muted">Si es el último material, la entrada también se eliminará.</small>',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    openLoading();
                    axios.post(urlAdmin + '/admin/historial/entradas/detalle/eliminar', { id: id })
                        .then((response) => {
                            closeLoading();
                            switch (response.data.success) {
                                case 1:
                                    if (response.data.entrada_borrada) {
                                        toastr.success('Material eliminado. La entrada fue eliminada por no tener más materiales.');
                                        $('#modalDetalle').modal('hide');
                                        recargar();
                                    } else {
                                        toastr.success('Material eliminado correctamente');
                                        const entradaId = $('#detalle-id-editar').data('entrada-id');
                                        const lote      = $('#detalle-lote').text();
                                        const fecha     = $('#detalle-fecha').text();
                                        verDetalle(entradaId, lote, fecha);
                                        recargar();
                                    }
                                    break;
                                case 4:
                                    Swal.fire({
                                        title: 'No se puede eliminar',
                                        text: response.data.msg || 'Este material ya tiene salidas registradas.',
                                        type: 'warning',
                                        confirmButtonText: 'Entendido'
                                    });
                                    break;
                                case 0:  toastr.error('El material no existe o ya fue eliminado'); break;
                                case 99: toastr.error(response.data.msg || 'Ocurrió un error al eliminar'); break;
                                default: toastr.error('Error al eliminar');
                            }
                        })
                        .catch(() => { closeLoading(); toastr.error('Error al eliminar'); });
                }
            });
        }
    </script>
@endsection

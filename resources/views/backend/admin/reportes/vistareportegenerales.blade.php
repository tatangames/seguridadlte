@extends('adminlte::page')

@section('title', 'Reportes')

@section('content_header')
    <h1>Reportes</h1>
@stop

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

        {{-- ══ BLOQUE 1: MATERIALES ENTREGADOS A EMPLEADO ══ --}}
        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-pdf mr-1"></i> Materiales Entregados a Empleado
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Distrito</label>
                                    <select class="form-control" id="select-distrito">
                                        <option value="0" selected disabled>Seleccionar opción</option>
                                        @foreach($arrayDistritos as $sel)
                                            <option value="{{ $sel->id }}">{{ $sel->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Unidad</label>
                                    <select class="form-control" id="select-unidad">
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Empleado</label>
                                    <select class="form-control" id="select-empleado" style="width:100%;">
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" onclick="pdfEncargado()" class="btn btn-success">
                            <i class="fas fa-file-pdf mr-1"></i> Generar PDF
                        </button>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══ BLOQUE 2: REPORTE DE INVENTARIO ══ --}}
        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-boxes mr-1"></i> Reporte de Inventario
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-0">Genera un PDF con la existencia actual de todos los materiales en bodega.</p>
                    </div>
                    <div class="card-footer">
                        <button type="button" onclick="pdfExistencias()" class="btn btn-success">
                            <i class="fas fa-file-pdf mr-1"></i> Generar PDF
                        </button>
                    </div>
                </div>
            </div>
        </section>

    </div>

@stop

@section('js')
    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}" type="text/javascript"></script>

    <script>
        $(function () {

            $('#select-distrito').select2({
                theme: 'bootstrap-5',
                language: { noResults: function () { return 'Búsqueda no encontrada'; } },
            });

            $('#select-unidad').select2({
                theme: 'bootstrap-5',
                language: { noResults: function () { return 'Búsqueda no encontrada'; } },
            });

            $('#select-empleado').select2({
                theme: 'bootstrap-5',
                language: { noResults: function () { return 'Búsqueda no encontrada'; } },
            });

            // ── Bindings de cascada (select2 ya tiene el valor actualizado en este evento) ──
            $('#select-distrito').on('select2:select change', function () {
                buscarUnidad();
            });

            $('#select-unidad').on('select2:select change', function () {
                buscarEmpleado();
            });

        });

        // ── Bloque 1: Materiales entregados a empleado ──
        function pdfEncargado() {
            var idempleado = $('#select-empleado').val();

            if (!idempleado || idempleado === '0') {
                toastr.error('Debe seleccionar un empleado');
                return;
            }

            window.open(urlAdmin + '/admin/reportes/pdf/recibe-separados/' + idempleado);
        }

        function buscarUnidad() {
            let id = $('#select-distrito').val();

            if (id == '0') {
                $('#select-unidad').empty();
                $('#select-empleado').empty();
                return false;
            }

            openLoading();

            axios.post(urlAdmin + '/admin/empleados/buscarunidad', { id: id })
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
                        $('#select-unidad').empty();
                        $('#select-unidad').append('<option value="0" disabled selected>Seleccionar opción</option>');

                        $.each(response.data.arrayUnidad, function (key, val) {
                            $('#select-unidad').append('<option value="' + val.id + '">' + val.nombre + '</option>');
                        });
                        $('#select-unidad').trigger('change.select2');
                    } else {
                        toastr.error('Información no encontrada');
                    }
                })
                .catch(() => { closeLoading(); toastr.error('Información no encontrada'); });
        }

        function buscarEmpleado() {
            let id = $('#select-unidad').val();

            if (id == '0') {
                $('#select-empleado').empty();
                return false;
            }

            openLoading();

            axios.post(urlAdmin + '/admin/empleados/buscarunidad-empleado/reporte', { id: id })
                .then((response) => {
                    closeLoading();
                    if (response.data.success === 1) {
                        $('#select-empleado').empty();
                        $('#select-empleado').append('<option value="0" disabled selected>Seleccionar opción</option>');

                        $.each(response.data.arrayEmpleados, function (key, val) {
                            $('#select-empleado').append('<option value="' + val.id + '">' + val.nombreCompleto + '</option>');
                        });
                        $('#select-empleado').trigger('change.select2');
                    } else {
                        toastr.error('Información no encontrada');
                    }
                })
                .catch(() => { closeLoading(); toastr.error('Información no encontrada'); });
        }

        // ── Bloque 2: Reporte de inventario ──
        function pdfExistencias() {
            window.open(urlAdmin + '/admin/existencia/pdf/generar');
        }
    </script>
@endsection

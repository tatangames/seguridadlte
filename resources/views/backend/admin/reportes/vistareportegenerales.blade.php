@extends('adminlte::page')

@section('title', 'Reportes')

@section('content_header')
    <h1><i class="fas fa-chart-bar" style="color:#3b82f6; margin-right:8px"></i>Reportes</h1>
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

@section('css')
    <style>
        /* ── Grid de tarjetas ── */
        .reportes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
            padding: 10px 0 20px;
        }

        /* ── Tarjeta base ── */
        .reporte-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0,0,0,.08);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform .15s, box-shadow .15s;
        }
        .reporte-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,.12);
        }

        /* ── Header de tarjeta ── */
        .reporte-card-header {
            padding: 18px 22px;
            display: flex;
            align-items: center;
            gap: 14px;
            color: #fff;
        }
        .reporte-card-header .card-icon {
            width: 44px; height: 44px;
            background: rgba(255,255,255,.2);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0;
        }
        .reporte-card-header h5 {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            line-height: 1.3;
        }

        /* Colores por tipo */
        .reporte-card.blue  .reporte-card-header { background: linear-gradient(135deg, #1d4ed8, #3b82f6); }
        .reporte-card.amber .reporte-card-header { background: linear-gradient(135deg, #b45309, #d97706); }
        .reporte-card.green .reporte-card-header { background: linear-gradient(135deg, #15803d, #22c55e); }

        /* ── Cuerpo de tarjeta ── */
        .reporte-card-body {
            padding: 20px 22px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .reporte-card-body p {
            font-size: 13px;
            color: #64748b;
            margin: 0;
            line-height: 1.6;
            border-bottom: 1px dashed #e2e8f0;
            padding-bottom: 14px;
        }

        /* Campos dentro de la tarjeta */
        .reporte-fields {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .reporte-fields .field-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .reporte-fields .field-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
            min-width: 130px;
        }
        .reporte-fields label {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin: 0;
        }
        .reporte-fields .required-star { color: #ef4444; }
        .reporte-fields input[type="date"],
        .reporte-fields select {
            height: 36px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            padding: 0 10px;
            font-size: 13px;
            background: #f8fafc;
            color: #1e293b;
            width: 100%;
            transition: border-color .15s, box-shadow .15s;
        }
        .reporte-fields input[type="date"]:focus,
        .reporte-fields select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.15);
            background: #fff;
        }

        /* ── Botón generar ── */
        .btn-generar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 20px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all .15s;
            color: #fff;
            align-self: flex-start;
        }
        .reporte-card.blue  .btn-generar { background: #2563eb; }
        .reporte-card.blue  .btn-generar:hover { background: #1d4ed8; }
        .reporte-card.amber .btn-generar { background: #d97706; }
        .reporte-card.amber .btn-generar:hover { background: #b45309; }
        .reporte-card.green .btn-generar { background: #16a34a; }
        .reporte-card.green .btn-generar:hover { background: #15803d; }

        @media (max-width: 640px) {
            .reportes-grid { grid-template-columns: 1fr; }
            .reporte-fields .field-row { flex-direction: column; }
        }
    </style>
@stop

@section('content')
    <div id="divcontenedor">
        <section class="content">
            <div class="container-fluid">

                <div class="reportes-grid">

                    {{-- ══ TARJETA 1: INVENTARIO ACTUAL ══ --}}
                    <div class="reporte-card blue">
                        <div class="reporte-card-header">
                            <div class="card-icon"><i class="fas fa-boxes"></i></div>
                            <h5>Inventario Actual de Materiales</h5>
                        </div>
                        <div class="reporte-card-body">
                            <p>Existencias actuales (entradas menos salidas). Solo muestra materiales con cantidad mayor a cero.</p>
                            <button type="button" onclick="pdfExistencias()" class="btn-generar">
                                <i class="fas fa-file-pdf"></i> Generar PDF
                            </button>
                        </div>
                    </div>

                    {{-- ══ TARJETA 2: CONTROL DE ENTRADAS/SALIDAS POR PERÍODO ══ --}}
                    <div class="reporte-card amber">
                        <div class="reporte-card-header">
                            <div class="card-icon"><i class="fas fa-exchange-alt"></i></div>
                            <h5>Control de Entradas/Salidas por Período</h5>
                        </div>
                        <div class="reporte-card-body">
                            <p>Muestra saldo inicial, entradas, salidas y saldo final de cada material dentro del rango de fechas seleccionado.</p>
                            <div class="reporte-fields">
                                <div class="field-row">
                                    <div class="field-item">
                                        <label>Fecha Desde <span class="required-star">*</span></label>
                                        <input type="date" id="periodo-desde">
                                    </div>
                                    <div class="field-item">
                                        <label>Fecha Hasta <span class="required-star">*</span></label>
                                        <input type="date" id="periodo-hasta">
                                    </div>
                                </div>
                            </div>
                            <button type="button" onclick="pdfPeriodos()" class="btn-generar">
                                <i class="fas fa-file-pdf"></i> Generar PDF
                            </button>
                        </div>
                    </div>

                    {{-- ══ TARJETA 3: MATERIALES ENTREGADOS A EMPLEADO ══ --}}
                    <div class="reporte-card green">
                        <div class="reporte-card-header">
                            <div class="card-icon"><i class="fas fa-user-check"></i></div>
                            <h5>Materiales Entregados a Empleado</h5>
                        </div>
                        <div class="reporte-card-body">
                            <p>Genera un PDF con todos los materiales de E.P.P. asignados a un empleado específico.</p>
                            <div class="reporte-fields">
                                <div class="field-row">
                                    <div class="field-item">
                                        <label>Distrito</label>
                                        <select id="select-distrito">
                                            <option value="0" selected disabled>Seleccionar opción</option>
                                            @foreach($arrayDistritos as $sel)
                                                <option value="{{ $sel->id }}">{{ $sel->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="field-row">
                                    <div class="field-item">
                                        <label>Unidad</label>
                                        <select id="select-unidad">
                                            <option value="0" disabled selected>Seleccionar opción</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="field-row">
                                    <div class="field-item">
                                        <label>Empleado</label>
                                        <select id="select-empleado">
                                            <option value="0" disabled selected>Seleccionar opción</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="button" onclick="pdfEncargado()" class="btn-generar">
                                <i class="fas fa-file-pdf"></i> Generar PDF
                            </button>
                        </div>
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
            // ── Bindings cascada distrito → unidad → empleado ──
            $('#select-distrito').on('change', function () {
                buscarUnidad();
            });
            $('#select-unidad').on('change', function () {
                buscarEmpleado();
            });
        });

        // ── Tarjeta 1: Inventario actual ─────────────────────────────────────────
        function pdfExistencias() {
            window.open(urlAdmin + '/admin/existencia/pdf/generar');
        }

        // ── Tarjeta 2: Control por período ───────────────────────────────────────
        function pdfPeriodos() {
            var desde = document.getElementById('periodo-desde').value;
            var hasta = document.getElementById('periodo-hasta').value;

            if (!desde) { toastr.error('Debe seleccionar la fecha desde'); return; }
            if (!hasta)  { toastr.error('Debe seleccionar la fecha hasta');  return; }
            if (desde > hasta) { toastr.error('La fecha desde no puede ser mayor a la fecha hasta'); return; }

            window.open(urlAdmin + '/admin/bodega/reportespdf/inicial/final/' + desde + '/' + hasta);
        }

        // ── Tarjeta 3: Materiales entregados a empleado ──────────────────────────
        function pdfEncargado() {
            var idempleado = $('#select-empleado').val();
            if (!idempleado || idempleado === '0') {
                toastr.error('Debe seleccionar un empleado');
                return;
            }
            window.open(urlAdmin + '/admin/reportes/pdf/recibe-separados/' + idempleado);
        }

        function buscarUnidad() {
            var id = $('#select-distrito').val();
            if (!id || id === '0') {
                $('#select-unidad').html('<option value="0" disabled selected>Seleccionar opción</option>');
                $('#select-empleado').html('<option value="0" disabled selected>Seleccionar opción</option>');
                return;
            }

            openLoading();
            axios.post(urlAdmin + '/admin/empleados/buscarunidad', { id: id })
                .then(function(response) {
                    closeLoading();
                    if (response.data.success === 1) {
                        var opts = '<option value="0" disabled selected>Seleccionar opción</option>';
                        $.each(response.data.arrayUnidad, function (k, v) {
                            opts += '<option value="' + v.id + '">' + v.nombre + '</option>';
                        });
                        $('#select-unidad').html(opts);
                        $('#select-empleado').html('<option value="0" disabled selected>Seleccionar opción</option>');
                    } else {
                        toastr.error('Información no encontrada');
                    }
                })
                .catch(function() { closeLoading(); toastr.error('Error al cargar unidades'); });
        }

        function buscarEmpleado() {
            var id = $('#select-unidad').val();
            if (!id || id === '0') {
                $('#select-empleado').html('<option value="0" disabled selected>Seleccionar opción</option>');
                return;
            }

            openLoading();
            axios.post(urlAdmin + '/admin/empleados/buscarunidad-empleado/reporte', { id: id })
                .then(function(response) {
                    closeLoading();
                    if (response.data.success === 1) {
                        var opts = '<option value="0" disabled selected>Seleccionar opción</option>';
                        $.each(response.data.arrayEmpleados, function (k, v) {
                            opts += '<option value="' + v.id + '">' + v.nombreCompleto + '</option>';
                        });
                        $('#select-empleado').html(opts);
                    } else {
                        toastr.error('Información no encontrada');
                    }
                })
                .catch(function() { closeLoading(); toastr.error('Error al cargar empleados'); });
        }
    </script>
@endsection

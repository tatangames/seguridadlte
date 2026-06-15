@extends('adminlte::page')

@section('title', 'Materiales')

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Sweetalert2', true)

@include('backend.urlglobal')

@section('content_header')
    <h1><i class="fas fa-boxes" style="color:#3b82f6; margin-right:8px"></i>Materiales</h1>
@stop

@section('content_top_nav_right')
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
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
        /* ── Stats ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px; margin-bottom: 20px;
        }
        .stat-card {
            background: #fff; border-radius: 10px; padding: 16px 18px;
            border-left: 4px solid; box-shadow: 0 2px 8px rgba(0,0,0,.07);
            display: flex; align-items: center; gap: 14px; transition: transform .15s;
        }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-card.blue   { border-color: #3b82f6; }
        .stat-card.green  { border-color: #22c55e; }
        .stat-card.yellow { border-color: #f59e0b; }
        .stat-card.red    { border-color: #ef4444; }
        .stat-icon {
            width: 42px; height: 42px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
        }
        .stat-card.blue   .stat-icon { background: #eff6ff; color: #3b82f6; }
        .stat-card.green  .stat-icon { background: #f0fdf4; color: #22c55e; }
        .stat-card.yellow .stat-icon { background: #fffbeb; color: #f59e0b; }
        .stat-card.red    .stat-icon { background: #fef2f2; color: #ef4444; }
        .stat-value { font-size: 22px; font-weight: 700; line-height: 1; color: #1e293b; }
        .stat-label { font-size: 11px; color: #64748b; margin-top: 3px; text-transform: uppercase; letter-spacing: .5px; }

        /* ── Filter bar ── */
        .filter-bar {
            width: 100%; box-sizing: border-box;
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 14px 16px; margin-bottom: 16px;
            display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end;
            justify-content: flex-start;
        }
        .filter-bar .filter-item { display: flex; flex-direction: column; gap: 4px; }
        .filter-bar label { font-size: 11px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: .4px; margin: 0; }
        .filter-bar select, .filter-bar input {
            height: 34px; font-size: 13px; border: 1px solid #cbd5e1;
            border-radius: 6px; padding: 0 10px; background: #fff; min-width: 140px;
        }
        .filter-bar select:focus, .filter-bar input:focus {
            outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.15);
        }
        .btn-filter-clear {
            height: 34px; padding: 0 14px; font-size: 12px;
            border: 1px solid #cbd5e1; border-radius: 6px;
            background: #fff; color: #64748b; cursor: pointer;
            align-self: flex-end; transition: all .15s;
        }
        .btn-filter-clear:hover { background: #f1f5f9; border-color: #94a3b8; }

        /* ── Stock badge ── */
        .stock-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;
        }
        .stock-badge.ok     { background: #dcfce7; color: #166534; }
        .stock-badge.warn   { background: #fef9c3; color: #854d0e; }
        .stock-badge.danger { background: #fee2e2; color: #991b1b; }
        .stock-badge .dot   { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

        /* ── Meses badge ── */
        .meses-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2px 8px; border-radius: 12px; font-size: 11px;
        }
        .meses-badge.proximo { background: #fef9c3; color: #854d0e; }
        .meses-badge.vigente { background: #f0fdf4; color: #166534; }
        .meses-badge.sindata { background: #f1f5f9; color: #64748b; }

        /* ── Table actions ── */
        .btn-action {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 10px; border-radius: 5px; font-size: 11px;
            font-weight: 600; border: none; cursor: pointer; transition: all .15s;
        }
        .btn-action.edit         { background: #3b82f6; color: #fff; }
        .btn-action.edit:hover   { background: #2563eb; }
        .btn-action.detail       { background: #22c55e; color: #fff; }
        .btn-action.detail:hover { background: #16a34a; }

        /* ── Modal ── */
        .modal-xl { max-width: 95% !important; width: 1200px; }
        @media (max-width: 1280px) { .modal-xl { width: 98%; } }
        .modal-section-title {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .8px; color: #94a3b8; margin: 18px 0 10px;
            padding-bottom: 6px; border-bottom: 1px solid #e2e8f0;
        }
        .form-label-styled { font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block; }
        .required-star { color: #ef4444; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .filter-bar { flex-direction: column; align-items: flex-start; }
            .filter-bar .filter-item { width: 100%; }
            .filter-bar select, .filter-bar input { min-width: 100%; width: 100%; }
            .btn-filter-clear { width: 100%; }
        }
    </style>
@stop

@section('content')
    <div id="divcontenedor">

        {{-- ░░ HEADER ░░ --}}
        <section class="content-header">
            <div style="display:flex; align-items:center; gap:14px; margin-bottom:16px; flex-wrap:wrap">
                <button type="button" onclick="modalAgregar()" class="btn btn-primary btn-sm" style="height:34px">
                    <i class="fas fa-plus-square"></i> Registrar Material
                </button>
            </div>
        </section>

        {{-- ░░ STATS ░░ --}}
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                <div>
                    <div class="stat-value">{{ $lista->count() }}</div>
                    <div class="stat-label">Total Materiales</div>
                </div>
            </div>
            <div class="stat-card green">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="stat-value">{{ $lista->filter(fn($r) => $r->cantidadGlobal >= 5)->count() }}</div>
                    <div class="stat-label">Con Stock (≥ 5)</div>
                </div>
            </div>
            <div class="stat-card yellow">
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <div class="stat-value">{{ $lista->filter(fn($r) => $r->cantidadGlobal >= 1 && $r->cantidadGlobal <= 4)->count() }}</div>
                    <div class="stat-label">Stock Bajo (1 – 4)</div>
                </div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                <div>
                    <div class="stat-value">{{ $lista->filter(fn($r) => $r->cantidadGlobal <= 0)->count() }}</div>
                    <div class="stat-label">Sin Stock (0)</div>
                </div>
            </div>
        </div>

        {{-- ░░ TABLA ░░ --}}
        <section class="content">
            <div class="container-fluid">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-list" style="margin-right:6px"></i>Listado de Materiales</h3>
                    </div>
                    <div class="card-body">

                        {{-- ░░ FILTER BAR ░░ --}}
                        <div class="filter-bar">
                            <div class="filter-item">
                                <label><i class="fas fa-tag"></i> Marca</label>
                                <select id="filtro-marca" onchange="aplicarFiltros()">
                                    <option value="">Todas</option>
                                    @foreach($arrayMarcas as $m)
                                        <option value="{{ $m->nombre }}">{{ $m->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-item">
                                <label><i class="fas fa-ruler"></i> Unidad</label>
                                <select id="filtro-unidad" onchange="aplicarFiltros()">
                                    <option value="">Todas</option>
                                    @foreach($arrayUnidades as $u)
                                        <option value="{{ $u->nombre }}">{{ $u->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-item">
                                <label><i class="fas fa-shield-alt"></i> Normativa</label>
                                <select id="filtro-normativa" onchange="aplicarFiltros()">
                                    <option value="">Todas</option>
                                    @foreach($arrayNormativa as $n)
                                        <option value="{{ $n->nombre }}">{{ $n->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-item">
                                <label><i class="fas fa-warehouse"></i> Stock</label>
                                <select id="filtro-stock" onchange="aplicarFiltros()">
                                    <option value="">Todos</option>
                                    <option value="ok">Con Stock (≥ 5)</option>
                                    <option value="low">Stock Bajo (1–4)</option>
                                    <option value="zero">Sin Stock (0)</option>
                                </select>
                            </div>
                            <div class="filter-item">
                                <label><i class="fas fa-clock"></i> Vencimiento</label>
                                <select id="filtro-vencimiento" onchange="aplicarFiltros()">
                                    <option value="">Todos</option>
                                    <option value="proximo">Próximos (≤ 3 meses)</option>
                                    <option value="vigente">Vigentes (> 3 meses)</option>
                                    <option value="sindata">Sin fecha</option>
                                </select>
                            </div>
                            <div class="filter-item">
                                <label><i class="fas fa-search"></i> Buscar</label>
                                <input type="text" id="filtro-texto" placeholder="Nombre o código..." oninput="aplicarFiltros()">
                            </div>
                            <button class="btn-filter-clear" onclick="limpiarFiltros()">
                                <i class="fas fa-times"></i> Limpiar
                            </button>
                        </div>

                        <div id="filtro-info" style="font-size:12px; color:#64748b; margin-bottom:8px; display:none">
                            <i class="fas fa-filter"></i> Mostrando
                            <strong id="filtro-visible">0</strong> de
                            <strong>{{ $lista->count() }}</strong> registros
                        </div>

                        {{-- ░░ TABLA ░░ --}}
                        <div id="tablaDatatable">
                            <div class="table-responsive">
                                <table id="tabla" class="table table-bordered table-striped">
                                    <thead>
                                    <tr>
                                        <th style="width:6%">Código</th>
                                        <th style="width:22%">Nombre</th>
                                        <th style="width:7%">Medida</th>
                                        <th style="width:8%">Marca</th>
                                        <th style="width:8%">Normativa</th>
                                        <th style="width:6%">Talla</th>
                                        <th style="width:6%">Color</th>
                                        <th style="width:10%">Otros</th>
                                        <th style="width:8%">Stock Actual</th>
                                        <th style="width:8%">Meses Cambio</th>
                                        <th style="width:11%">Opciones</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($lista as $dato)
                                        @php
                                            $stock = (int) $dato->cantidadGlobal;
                                            if ($stock >= 5)     { $stockClass = 'ok';     }
                                            elseif ($stock >= 1) { $stockClass = 'warn';   }
                                            else                 { $stockClass = 'danger'; }

                                            $meses = (int) $dato->meses_cambio;
                                            if ($meses === 0)    { $mesesClass = 'sindata'; $mesesLabel = '—'; }
                                            elseif ($meses <= 3) { $mesesClass = 'proximo'; $mesesLabel = $meses . ' mes' . ($meses > 1 ? 'es' : ''); }
                                            else                 { $mesesClass = 'vigente'; $mesesLabel = $meses . ' meses'; }
                                        @endphp
                                        <tr>
                                            <td>
                                                @if($dato->codigo)
                                                    <code style="font-size:11px; background:#f1f5f9; padding:2px 6px; border-radius:4px">{{ $dato->codigo }}</code>
                                                @else
                                                    <span style="color:#cbd5e1">—</span>
                                                @endif
                                            </td>
                                            <td style="font-weight:500">{{ $dato->nombre }}</td>
                                            <td style="font-size:12px">{{ $dato->unidadMedida }}</td>
                                            <td style="font-size:12px">{{ $dato->marca }}</td>
                                            <td style="font-size:12px">{{ $dato->normativa }}</td>
                                            <td style="font-size:12px">{{ $dato->talla ?: '—' }}</td>
                                            <td style="font-size:12px">{{ $dato->color ?: '—' }}</td>
                                            <td style="font-size:12px; color:#475569">{{ $dato->otros ?: '—' }}</td>
                                            <td>
                                            <span class="stock-badge {{ $stockClass }}">
                                                <span class="dot"></span> {{ $stock }}
                                            </span>
                                            </td>
                                            <td>
                                            <span class="meses-badge {{ $mesesClass }}">
                                                <i class="fas fa-clock" style="font-size:10px"></i> {{ $mesesLabel }}
                                            </span>
                                            </td>
                                            <td>
                                                <button class="btn-action edit" onclick="informacion({{ $dato->id }})">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                                <button class="btn-action detail" style="margin-top:4px" onclick="infoDetalle({{ $dato->id }})">
                                                    <i class="fas fa-eye"></i> Detalle
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        {{-- ░░ MODAL AGREGAR ░░ --}}
        <div class="modal fade" id="modalAgregar">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header" style="background:#1e293b; color:#fff">
                        <h4 class="modal-title"><i class="fas fa-plus-circle" style="margin-right:8px"></i>Nuevo Material</h4>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form id="formulario-nuevo" onsubmit="event.preventDefault(); nuevo();">
                            <div class="card-body">

                                <div class="modal-section-title"><i class="fas fa-info-circle" style="margin-right:5px"></i>Identificación</div>
                                <div class="row">
                                    <div class="col-md-9">
                                        <div class="form-group">
                                            <label class="form-label-styled">Nombre del Material <span class="required-star">*</span></label>
                                            <input id="repuesto" data-info='0' autocomplete="off" class='form-control'
                                                   onkeyup='buscarMaterial(this)' maxlength='400' type='text'
                                                   placeholder="Escriba para buscar o ingresar nuevo...">
                                            <div class='droplista' style='position:absolute; z-index:9; width:75% !important;'></div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label-styled">Código <span style="color:#94a3b8; font-weight:400">(Opcional)</span></label>
                                            <input type="text" class="form-control" autocomplete="off" id="codigo-nuevo" maxlength="100" placeholder="Ej: MAT-001">
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-section-title"><i class="fas fa-tags" style="margin-right:5px"></i>Clasificación</div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label-styled">Unidad de Medida <span class="required-star">*</span></label>
                                            <select class="form-control" id="select-unidad-nuevo">
                                                <option value="">Seleccione una opción</option>
                                                @foreach($arrayUnidades as $sel)
                                                    <option value="{{ $sel->id }}">{{ $sel->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label-styled">Marca <span class="required-star">*</span></label>
                                            <select class="form-control" id="select-marca-nuevo">
                                                <option value="">Seleccione una opción</option>
                                                @foreach($arrayMarcas as $sel)
                                                    <option value="{{ $sel->id }}">{{ $sel->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label-styled">Normativa <span class="required-star">*</span></label>
                                            <select class="form-control" id="select-normativa-nuevo">
                                                <option value="">Seleccione una opción</option>
                                                @foreach($arrayNormativa as $sel)
                                                    <option value="{{ $sel->id }}">{{ $sel->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-section-title"><i class="fas fa-sliders-h" style="margin-right:5px"></i>Atributos Opcionales</div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label-styled">Color <span style="color:#94a3b8; font-weight:400">(Opcional)</span></label>
                                            <select class="form-control" id="select-color-nuevo">
                                                <option value="">Seleccione una opción</option>
                                                @foreach($arrayColor as $sel)
                                                    <option value="{{ $sel->id }}">{{ $sel->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label-styled">Talla <span style="color:#94a3b8; font-weight:400">(Opcional)</span></label>
                                            <select class="form-control" id="select-talla-nuevo">
                                                <option value="">Seleccione una opción</option>
                                                @foreach($arrayTalla as $sel)
                                                    <option value="{{ $sel->id }}">{{ $sel->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label-styled">Meses Estimados para Cambio</label>
                                            <div style="position:relative">
                                                <input type="number" min="0" max="100" value="0" class="form-control"
                                                       id="fechacambio-nuevo" style="padding-right:50px">
                                                <span style="position:absolute; right:10px; top:50%; transform:translateY(-50%); font-size:11px; color:#94a3b8">meses</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-section-title"><i class="fas fa-sticky-note" style="margin-right:5px"></i>Notas</div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label-styled">Otros <span style="color:#94a3b8; font-weight:400">(Opcional)</span></label>
                                            <input type="text" class="form-control" autocomplete="off" id="otros-nuevo" maxlength="500" placeholder="Observaciones adicionales...">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-between" style="background:#f8fafc">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="nuevo()"><i class="fas fa-save"></i> Guardar Material</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ░░ MODAL EDITAR ░░ --}}
        <div class="modal fade" id="modalEditar">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header" style="background:#1e40af; color:#fff">
                        <h4 class="modal-title"><i class="fas fa-edit" style="margin-right:8px"></i>Editar Material</h4>
                        <button type="button" class="close" data-dismiss="modal" style="color:#fff"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form id="formulario-editar" onsubmit="event.preventDefault(); editar();">
                            <div class="card-body">
                                <input type="hidden" id="id-editar">

                                <div class="modal-section-title"><i class="fas fa-info-circle" style="margin-right:5px"></i>Identificación</div>
                                <div class="row">
                                    <div class="col-md-9">
                                        <div class="form-group">
                                            <label class="form-label-styled">Nombre del Material <span class="required-star">*</span></label>
                                            <input type="text" class="form-control" autocomplete="off" maxlength="300" id="nombre-editar">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label-styled">Código</label>
                                            <input type="text" class="form-control" autocomplete="off" id="codigo-editar" maxlength="12">
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-section-title"><i class="fas fa-tags" style="margin-right:5px"></i>Clasificación</div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label-styled">Unidad de Medida <span class="required-star">*</span></label>
                                            <select class="form-control" id="select-unidad-editar"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label-styled">Marca <span class="required-star">*</span></label>
                                            <select class="form-control" id="select-marca-editar"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label-styled">Normativa <span class="required-star">*</span></label>
                                            <select class="form-control" id="select-normativa-editar"></select>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-section-title"><i class="fas fa-sliders-h" style="margin-right:5px"></i>Atributos Opcionales</div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label-styled">Color <span style="color:#94a3b8; font-weight:400">(Opcional)</span></label>
                                            <select class="form-control" id="select-color-editar"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label-styled">Talla <span style="color:#94a3b8; font-weight:400">(Opcional)</span></label>
                                            <select class="form-control" id="select-talla-editar"></select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label-styled">Meses Estimados para Cambio</label>
                                            <div style="position:relative">
                                                <input type="number" min="0" max="100" value="0" class="form-control"
                                                       id="fechacambio-editar" style="padding-right:50px">
                                                <span style="position:absolute; right:10px; top:50%; transform:translateY(-50%); font-size:11px; color:#94a3b8">meses</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-section-title"><i class="fas fa-sticky-note" style="margin-right:5px"></i>Notas</div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label-styled">Otros <span style="color:#94a3b8; font-weight:400">(Opcional)</span></label>
                                            <input type="text" class="form-control" autocomplete="off" id="otros-editar" maxlength="500">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-between" style="background:#f8fafc">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="editar()"><i class="fas fa-save"></i> Actualizar Material</button>
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
        function initSelect2(id) {
            $('#' + id).select2({
                theme: "bootstrap-5",
                dropdownParent: $('#' + id).closest('.modal'),
                language: { noResults: function(){ return "Búsqueda no encontrada"; } }
            });
        }

        $(document).ready(function () {

            window.seguroBuscador    = true;
            window.txtContenedorGlobal = this;

            $(document).click(function () { $(".droplista").hide(); });

            // ── DataTable ────────────────────────────────────────────────────
            $('#tabla').DataTable({
                paging:       true,
                lengthChange: true,
                searching:    true,
                ordering:     true,
                info:         true,
                autoWidth:    false,
                pagingType:   "full_numbers",
                lengthMenu:   [[25, 50, 100, 500, -1], [25, 50, 100, 500, "Todo"]],
                order:        [[1, "asc"]],
                columnDefs:   [{ orderable: false, targets: 10 }],
                language: {
                    sProcessing:   "Procesando...",
                    sLengthMenu:   "Mostrar _MENU_ registros",
                    sZeroRecords:  "No se encontraron resultados",
                    sEmptyTable:   "Ningún dato disponible",
                    sInfo:         "Mostrando del _START_ al _END_ de _TOTAL_ registros",
                    sInfoEmpty:    "Mostrando 0 registros",
                    sInfoFiltered: "(filtrado de _MAX_ registros)",
                    sSearch:       "Buscar:",
                    sLoadingRecords: "Cargando...",
                    oPaginate: {
                        sFirst: "Primero", sLast: "Último",
                        sNext: "Siguiente", sPrevious: "Anterior"
                    }
                },
                responsive: true,
                dom:
                    "<'row align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-md-right'f>>" +
                    "tr" +
                    "<'row align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
            });

            // ── Select2 ──────────────────────────────────────────────────────
            ['select-unidad-nuevo','select-marca-nuevo','select-normativa-nuevo',
                'select-color-nuevo','select-talla-nuevo',
                'select-unidad-editar','select-marca-editar','select-normativa-editar',
                'select-color-editar','select-talla-editar'].forEach(initSelect2);
        });

        // ── Filtros ──────────────────────────────────────────────────────────
        function aplicarFiltros() {
            var marca     = $('#filtro-marca').val().toLowerCase();
            var unidad    = $('#filtro-unidad').val().toLowerCase();
            var normativa = $('#filtro-normativa').val().toLowerCase();
            var stockF    = $('#filtro-stock').val();
            var vencF     = $('#filtro-vencimiento').val();
            var texto     = $('#filtro-texto').val().toLowerCase();
            var visible   = 0;

            $('#tabla tbody tr').each(function () {
                var tds      = $(this).find('td');
                var cod      = tds.eq(0).text().toLowerCase();
                var nom      = tds.eq(1).text().toLowerCase();
                var med      = tds.eq(2).text().toLowerCase();
                var mar      = tds.eq(3).text().toLowerCase();
                var nor      = tds.eq(4).text().toLowerCase();
                var stock    = parseInt(tds.eq(8).text().trim()) || 0;
                var mesesTxt = tds.eq(9).text().trim();
                var meses    = parseInt(mesesTxt) || 0;
                var sinFecha = mesesTxt === '—';
                var show     = true;

                if (marca     && !mar.includes(marca))     show = false;
                if (unidad    && !med.includes(unidad))    show = false;
                if (normativa && !nor.includes(normativa)) show = false;
                if (texto     && !nom.includes(texto) && !cod.includes(texto)) show = false;

                if (stockF === 'ok'   && stock < 5)                 show = false;
                if (stockF === 'low'  && (stock < 1 || stock >= 5)) show = false;
                if (stockF === 'zero' && stock !== 0)               show = false;

                if (vencF === 'proximo' && (sinFecha || meses > 3))  show = false;
                if (vencF === 'vigente' && (sinFecha || meses <= 3)) show = false;
                if (vencF === 'sindata' && !sinFecha)                show = false;

                $(this).toggle(show);
                if (show) visible++;
            });

            var hayFiltro = marca || unidad || normativa || stockF || vencF || texto;
            if (hayFiltro) {
                $('#filtro-visible').text(visible);
                $('#filtro-info').show();
            } else {
                $('#filtro-info').hide();
            }
        }

        function limpiarFiltros() {
            $('#filtro-marca, #filtro-unidad, #filtro-normativa, #filtro-stock, #filtro-vencimiento').val('');
            $('#filtro-texto').val('');
            $('#tabla tbody tr').show();
            $('#filtro-info').hide();
        }

        // ── Modal Agregar ────────────────────────────────────────────────────
        function modalAgregar() {
            document.getElementById("formulario-nuevo").reset();
            ['select-unidad-nuevo','select-marca-nuevo','select-normativa-nuevo',
                'select-color-nuevo','select-talla-nuevo'].forEach(function (id) {
                $('#' + id).prop('selectedIndex', 0).trigger('change');
            });
            $('#modalAgregar').modal({ backdrop: 'static', keyboard: false });
        }

        // ── Nuevo ────────────────────────────────────────────────────────────
        function nuevo() {
            var nombre      = document.getElementById('repuesto').value.trim();
            var codigo      = document.getElementById('codigo-nuevo').value.trim();
            var unidad      = document.getElementById('select-unidad-nuevo').value;
            var marca       = document.getElementById('select-marca-nuevo').value;
            var normativa   = document.getElementById('select-normativa-nuevo').value;
            var color       = document.getElementById('select-color-nuevo').value;
            var talla       = document.getElementById('select-talla-nuevo').value;
            var otros       = document.getElementById('otros-nuevo').value.trim();
            var fechaCambio = document.getElementById('fechacambio-nuevo').value;

            if (!nombre)    { toastr.error('El nombre es requerido');           return; }
            if (!unidad)    { toastr.error('La Unidad de Medida es requerida'); return; }
            if (!marca)     { toastr.error('La Marca es requerida');            return; }
            if (!normativa) { toastr.error('La Normativa es requerida');        return; }

            var reglaEntero = /^[0-9]\d*$/;
            if (fechaCambio !== '') {
                if (!fechaCambio.match(reglaEntero)) { toastr.error('Meses debe ser número entero positivo'); return; }
                if (parseInt(fechaCambio) > 100)     { toastr.error('Meses máximo: 100'); return; }
            }

            openLoading();
            var fd = new FormData();
            fd.append('nombre', nombre);   fd.append('codigo', codigo);
            fd.append('unidad', unidad);   fd.append('marca', marca);
            fd.append('normativa', normativa); fd.append('color', color);
            fd.append('talla', talla);     fd.append('otros', otros);
            fd.append('fecha', fechaCambio);

            axios.post(urlAdmin + '/admin/materiales/nuevo', fd)
                .then(res => {
                    closeLoading();
                    if (res.data.success === 1) {
                        toastr.success('Material registrado correctamente');
                        $('#modalAgregar').modal('hide');
                        location.reload();
                    } else { toastr.error('Error al registrar'); }
                })
                .catch(() => { toastr.error('Error al registrar'); closeLoading(); });
        }

        // ── Información ──────────────────────────────────────────────────────
        function informacion(id) {
            openLoading();
            document.getElementById("formulario-editar").reset();

            axios.post(urlAdmin + '/admin/materiales/informacion', { id })
                .then(res => {
                    closeLoading();
                    if (res.data.success !== 1) { toastr.error('Información no encontrada'); return; }

                    var d = res.data;
                    $('#modalEditar').modal({ backdrop: 'static', keyboard: false });
                    $('#id-editar').val(id);
                    $('#nombre-editar').val(d.material.nombre);
                    $('#codigo-editar').val(d.material.codigo);
                    $('#otros-editar').val(d.material.otros);
                    $('#fechacambio-editar').val(d.material.meses_cambio);

                    ['select-unidad-editar','select-marca-editar','select-normativa-editar',
                        'select-color-editar','select-talla-editar'].forEach(function (sid) {
                        document.getElementById(sid).options.length = 0;
                    });

                    function poblarSelect(selectId, array, valorActual, conVacio) {
                        if (conVacio) $('#' + selectId).append('<option value="">Seleccionar opción</option>');
                        $.each(array, function (k, v) {
                            var sel = (valorActual == v.id) ? ' selected="selected"' : '';
                            $('#' + selectId).append('<option value="' + v.id + '"' + sel + '>' + v.nombre + '</option>');
                        });
                        $('#' + selectId).trigger('change');
                    }

                    poblarSelect('select-unidad-editar',   d.unidad,    d.material.id_medida,    false);
                    poblarSelect('select-marca-editar',    d.marca,     d.material.id_marca,     false);
                    poblarSelect('select-normativa-editar',d.normativa, d.material.id_normativa, false);
                    poblarSelect('select-color-editar',    d.color,     d.material.id_color,     true);
                    poblarSelect('select-talla-editar',    d.talla,     d.material.id_talla,     true);
                })
                .catch(() => { closeLoading(); toastr.error('Información no encontrada'); });
        }

        // ── Editar ───────────────────────────────────────────────────────────
        function editar() {
            var id          = document.getElementById('id-editar').value;
            var nombre      = document.getElementById('nombre-editar').value.trim();
            var codigo      = document.getElementById('codigo-editar').value.trim();
            var unidad      = document.getElementById('select-unidad-editar').value;
            var marca       = document.getElementById('select-marca-editar').value;
            var normativa   = document.getElementById('select-normativa-editar').value;
            var color       = document.getElementById('select-color-editar').value;
            var talla       = document.getElementById('select-talla-editar').value;
            var otros       = document.getElementById('otros-editar').value.trim();
            var fechaCambio = document.getElementById('fechacambio-editar').value;

            if (!nombre)    { toastr.error('El nombre es requerido');           return; }
            if (!unidad)    { toastr.error('La Unidad de Medida es requerida'); return; }
            if (!marca)     { toastr.error('La Marca es requerida');            return; }
            if (!normativa) { toastr.error('La Normativa es requerida');        return; }

            var reglaEntero = /^[0-9]\d*$/;
            if (fechaCambio !== '') {
                if (!fechaCambio.match(reglaEntero)) { toastr.error('Meses debe ser número entero positivo'); return; }
                if (parseInt(fechaCambio) > 100)     { toastr.error('Meses máximo: 100'); return; }
            }

            openLoading();
            var fd = new FormData();
            fd.append('id', id);           fd.append('nombre', nombre);
            fd.append('codigo', codigo);   fd.append('unidad', unidad);
            fd.append('marca', marca);     fd.append('normativa', normativa);
            fd.append('color', color);     fd.append('talla', talla);
            fd.append('otros', otros);     fd.append('fecha', fechaCambio);

            axios.post(urlAdmin + '/admin/materiales/editar', fd)
                .then(res => {
                    closeLoading();
                    if (res.data.success === 1) {
                        toastr.success('Material actualizado correctamente');
                        $('#modalEditar').modal('hide');
                        location.reload();
                    } else { toastr.error('Error al actualizar'); }
                })
                .catch(() => { toastr.error('Error al actualizar'); closeLoading(); });
        }

        // ── Detalle ──────────────────────────────────────────────────────────
        function infoDetalle(id) {
            window.location.href = "{{ url('/admin/material/detalle') }}/" + id;
        }

        // ── Buscador autocomplete ────────────────────────────────────────────
        function buscarMaterial(e) {
            if (!seguroBuscador) return;
            seguroBuscador = false;
            txtContenedorGlobal = e;
            if (e.value === '') $(e).attr('data-info', 0);

            axios.post(urlAdmin + '/admin/materiales/buscarmaterial', { query: e.value })
                .then(res => {
                    seguroBuscador = true;
                    $(e).siblings(".droplista").fadeIn().html(res.data);
                })
                .catch(() => { seguroBuscador = true; });
        }

        function modificarValor(edrop) {
            $('#codigo-nuevo').val(edrop.id);
        }
    </script>
@endsection

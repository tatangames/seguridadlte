@extends('adminlte::page')

@section('title', 'Todos los Permisos')

@section('content_header')
    <h1>Todos los Permisos</h1>
@stop
{{-- Activa plugins que necesitas --}}
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Sweetalert2', true)

@include('backend.urlglobal')

@section('content_top_nav_right')

    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />

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

    <div id="divcontenedor">
        <section class="content-header">
            <div class="container-fluid">
                <button type="button" onclick="modalAgregar()" class="btn btn-success btn-sm">
                    <i class="fas fa-pencil-alt"></i>
                    Agregar Permiso
                </button>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Lista</h3>
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


        <div class="modal fade" id="modalAgregar">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Nuevo Permiso</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formulario-nuevo">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">

                                        <p>Esta acción agregara el "Permiso", pero se debera modificar el sistema para su utilización.</p>

                                        <div class="form-group">
                                            <label>Nombre</label>
                                            <input type="text" maxlength="200" autocomplete="off" class="form-control" id="nombre-nuevo" placeholder="Nombre">
                                        </div>

                                        <div class="form-group">
                                            <label>Descripción</label>
                                            <input type="text" maxlength="200" autocomplete="off" class="form-control" id="descripcion-nuevo" placeholder="Descripción">
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-success" onclick="agregarPermiso()">Agregar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalBorrar">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Borrar Permiso Global</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formulario-borrar">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">

                                        <p>Esta acción eliminara el Permiso en "Todos los Roles."</p>

                                        <div class="form-group">
                                            <input type="hidden" id="idborrar">
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-danger" onclick="borrar()">Borrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


@stop



@section('js')
    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>


    <script src="{{ asset('js/theme.js') }}"></script>

    <script>
        $(document).ready(function () {
            const ruta = "{{ url('/admin/roles/permisos-todos/tabla') }}";

            $('#tablaDatatable').load(ruta, function () {
                // Inicializar DataTable cuando el HTML ya está en el DOM
                $('#tabla').DataTable({
                    paging: true,
                    lengthChange: true,
                    searching: true,
                    ordering: true,
                    info: true,
                    autoWidth: false,
                    responsive: true,
                    pagingType: "full_numbers",
                    lengthMenu: [[10, 25, 50, 100, 150, -1],[10, 25, 50, 100, 150, "Todo"]],
                    language: {
                        sProcessing: "Procesando...",
                        sLengthMenu: "Mostrar _MENU_ registros",
                        sZeroRecords: "No se encontraron resultados",
                        sEmptyTable: "Ningún dato disponible en esta tabla",
                        sInfo: "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                        sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
                        sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
                        sSearch: "Buscar:",
                        oPaginate: { sFirst: "Primero", sLast: "Último", sNext: "Siguiente", sPrevious: "Anterior" },
                        oAria: { sSortAscending: ": Activar para ordenar ascendente", sSortDescending: ": Activar para ordenar descendente" }
                    },

                    // 👇 Esto coloca "Mostrar" a la IZQ y "Buscar" a la DER
                    dom:
                        "<'row align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-md-right'f>>" +
                        "tr" +
                        "<'row align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
                });

                // Opcional: inputs compactos y bonitos
                $('#tabla_length select').addClass('form-control form-control-sm');
                $('#tabla_filter input').addClass('form-control form-control-sm').css('display','inline-block');
            });

        });
    </script>

    <script>

        function modalAgregar(){
            document.getElementById("formulario-nuevo").reset();
            $('#modalAgregar').modal('show');
        }

        function agregarPermiso(){
            var nombre = document.getElementById('nombre-nuevo').value;
            var descripcion = document.getElementById('descripcion-nuevo').value;

            if(nombre === ''){
                toastr.error('Nombre es requerido')
                return;
            }

            if(nombre.length > 200){
                toastr.error('Máximo 200 caracteres para Nombre')
                return;
            }

            if(descripcion === ''){
                toastr.error('Descripción es requerido')
                return;
            }

            if(descripcion.length > 200){
                toastr.error('Máximo 200 caracteres para Descripción')
                return;
            }

            var formData = new FormData();
            formData.append('nombre', nombre);
            formData.append('descripcion', descripcion);

            axios.post(urlAdmin+'/admin/permisos/extra-nuevo',formData,  {
            })
                .then((response) => {
                    closeLoading()
                    $('#modalAgregar').modal('hide');

                    if(response.data.success === 1){
                        alertaMensaje('warning', 'Nombre Repetido', 'Cambiar el nombre del Permiso');
                    }
                    else if(response.data.success === 2){
                        toastr.success('Permiso agregado');
                        recargar();
                    }
                    else{
                        toastr.error('Error al agregar');
                    }
                })
                .catch((error) => {
                    closeLoading()
                    toastr.error('Error al agregar');
                });
        }



        // se recibe el ID del permiso a eliminar
        function modalBorrar(id){
            $('#idborrar').val(id);
            $('#modalBorrar').modal('show');
        }

        function borrar(){
            openLoading()
            // se envia el ID del permiso
            var idpermiso = document.getElementById('idborrar').value;

            var formData = new FormData();
            formData.append('idpermiso', idpermiso);

            axios.post(urlAdmin+'/admin/permisos/extra-borrar', formData, {
            })
                .then((response) => {
                    closeLoading()
                    $('#modalBorrar').modal('hide');

                    if(response.data.success === 1){
                        toastr.success('Permiso globalmente eliminado');
                        recargar();
                    }else{
                        toastr.error('Error al eliminar');
                    }
                })
                .catch((error) => {
                    closeLoading()
                    toastr.error('Error al eliminar');
                });
        }

        function recargar(){
            var ruta = "{{ url('/admin/roles/permisos-todos/tabla') }}";
            $('#tablaDatatable').load(ruta);
        }
    </script>



@endsection


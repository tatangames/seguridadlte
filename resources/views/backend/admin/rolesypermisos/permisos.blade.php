@extends('adminlte::page')

@section('title', 'Usuarios')

@section('content_header')
    <h1>Usuarios</h1>
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
                    Nuevo Usuario
                </button>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card card-gray-dark">
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
                        <h4 class="modal-title">Nuevo Usuario</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formulario-nuevo">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">

                                        <div class="form-group">
                                            <label>Nombre</label>
                                            <input type="text" maxlength="50" autocomplete="off" class="form-control" id="nombre-nuevo" placeholder="Nombre">
                                        </div>

                                        <div class="form-group">
                                            <label>Usuario</label>
                                            <input type="text" maxlength="50" autocomplete="off" class="form-control" id="usuario-nuevo" placeholder="Usuario">
                                        </div>

                                        <div class="form-group">
                                            <label>Contraseña</label>
                                            <input type="text" maxlength="16" autocomplete="off" class="form-control" id="password-nuevo" placeholder="Contraseña">
                                        </div>

                                        <div class="form-group">
                                            <label style="color:#191818">Rol</label>
                                            <br>
                                            <div>
                                                <select class="form-control" id="rol-nuevo">
                                                    @foreach($roles as $key => $value)
                                                        <option value="{{ $key }}">{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-success" onclick="nuevoUsuario()">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalEditar">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Editar Usuario</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formulario-editar">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">

                                        <div class="form-group">
                                            <label style="color:#191818">Rol</label>
                                            <br>
                                            <div>
                                                <select class="form-control" id="rol-editar">
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Nombre</label>
                                            <input type="hidden" id="id-editar">
                                            <input type="text" maxlength="50" autocomplete="off" class="form-control" id="nombre-editar">
                                        </div>

                                        <div class="form-group">
                                            <label>Usuario</label>
                                            <input type="text" maxlength="50" autocomplete="off" class="form-control" id="usuario-editar">
                                        </div>

                                        <div class="form-group">
                                            <label>Contraseña</label>
                                            <input type="text" maxlength="16" autocomplete="off" class="form-control" id="password-editar" placeholder="Contraseña">
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="actualizar()">Guardar</button>
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
        $(function () {
            const ruta = "{{ url('/admin/permisos/tabla') }}";

            function initDataTable() {
                // Si ya hay instancia, destrúyela antes de re-crear
                if ($.fn.DataTable.isDataTable('#tabla')) {
                    $('#tabla').DataTable().destroy();
                }

                // Inicializa
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
                        sInfo: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                        sInfoEmpty: "Mostrando 0 a 0 de 0 registros",
                        sInfoFiltered: "(filtrado de _MAX_ registros)",
                        sSearch: "Buscar:",
                        oPaginate: { sFirst: "Primero", sLast: "Último", sNext: "Siguiente", sPrevious: "Anterior" },
                        oAria: { sSortAscending: ": Orden ascendente", sSortDescending: ": Orden descendente" }
                    },
                    dom:
                        "<'row align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-md-right'f>>" +
                        "tr" +
                        "<'row align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
                });

                // Estilitos
                $('#tabla_length select').addClass('form-control form-control-sm');
                $('#tabla_filter input').addClass('form-control form-control-sm').css('display','inline-block');
            }

            function cargarTabla() {
                $('#tablaDatatable').load(ruta, function() {
                    // AQUI debe existir exactamente un <table id="tabla"> en la parcial
                    initDataTable();
                });
            }

            // Primera carga
            cargarTabla();

            // Exponer recarga para tus flujos (crear/editar)
            window.recargar = function () {
                cargarTabla();
            };
        });
    </script>


    <script>

        function modalAgregar(){
            document.getElementById("formulario-nuevo").reset();
            $('#modalAgregar').modal('show');
        }

        function nuevoUsuario(){

            var nombre = document.getElementById('nombre-nuevo').value;
            var usuario = document.getElementById('usuario-nuevo').value;
            var password = document.getElementById('password-nuevo').value;
            var idrol = document.getElementById('rol-nuevo').value;

            if(nombre === ''){
                toastr.error('Nombre es requerido');
                return;
            }

            if(nombre.length > 50){
                toastr.error('Máximo 50 caracteres para Nombre');
                return;
            }

            if(usuario === ''){
                toastr.error('Usuario es requerido');
                return;
            }

            if(usuario.length > 50){
                toastr.error('Máximo 50 caracteres para Usuario');
                return;
            }

            if(password === ''){
                toastr.error('Contraseña es requerido');
                return;
            }

            if(password.length < 4){
                toastr.error('Mínimo 4 caracteres para contraseña');
                return;
            }

            if(password.length > 16){
                toastr.error('Máximo 16 caracteres para contraseña');
                return;
            }

            if(idrol === ''){
                toastr.error('Rol es requerido');
                return;
            }

            openLoading();
            var formData = new FormData();
            formData.append('nombre', nombre);
            formData.append('usuario', usuario);
            formData.append('password', password);
            formData.append('rol', idrol);

            axios.post(urlAdmin+'/admin/permisos/nuevo-usuario', formData, {
            })
                .then((response) => {
                    closeLoading()

                    if (response.data.success === 1) {
                        toastr.error('Nombre Usuario ya existe');
                    }
                    else if(response.data.success === 2){
                        toastr.success('Agregado');
                        $('#modalAgregar').modal('hide');
                        recargar();
                    }
                    else {
                        toastr.error('Error al guardar');
                    }
                })
                .catch((error) => {
                    closeLoading()
                    toastr.error('Error al guardar');
                });
        }

        function verInformacion(id){
            openLoading();
            document.getElementById("formulario-editar").reset();

            axios.post(urlAdmin+'/admin/permisos/info-usuario',{
                'id': id
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        $('#modalEditar').modal('show');
                        $('#id-editar').val(response.data.info.id);
                        $('#nombre-editar').val(response.data.info.nombre);
                        $('#usuario-editar').val(response.data.info.usuario);

                        document.getElementById("rol-editar").options.length = 0;

                        $.each(response.data.roles, function( key, val ){

                            if(response.data.idrol[0] == key){
                                $('#rol-editar').append('<option value="' +key +'" selected="selected">'+val+'</option>');
                            }else{
                                $('#rol-editar').append('<option value="' +key +'">'+val+'</option>');
                            }
                        });

                    }else{
                        toastr.error('Información no encontrado.');
                    }
                })
                .catch((error) => {
                    closeLoading()
                    console.log(error);
                    toastr.error('Información no encontrado..');
                });
        }

        function actualizar(){
            var id = document.getElementById('id-editar').value;
            var nombre = document.getElementById('nombre-editar').value;
            var usuario = document.getElementById('usuario-editar').value;
            var password = document.getElementById('password-editar').value;
            var idrol = document.getElementById('rol-editar').value;


            if(nombre === ''){
                toastr.error('Nombre es requerido');
                return;
            }

            if(nombre.length > 50){
                toastr.error('Máximo 50 caracteres para Nombre');
                return;
            }

            if(usuario === ''){
                toastr.error('Usuario es requerido');
                return;
            }

            if(usuario.length > 50){
                toastr.error('Máximo 50 caracteres para Usuario');
                return;
            }

            if(password.length > 0){
                if(password.length < 4){
                    toastr.error('Mínimo 4 caracteres para contraseña');
                    return;
                }

                if(password.length > 16){
                    toastr.error('Máximo 16 caracteres para contraseña');
                    return;
                }
            }

            openLoading()
            var formData = new FormData();
            formData.append('id', id);
            formData.append('nombre', nombre);
            formData.append('usuario', usuario);
            formData.append('password', password);
            formData.append('rol', idrol);

            axios.post(urlAdmin+'/admin/permisos/editar-usuario', formData, {
            })
                .then((response) => {
                    closeLoading()

                    if (response.data.success === 1) {
                        toastr.error('El Usuario ya existe');
                    }
                    else if(response.data.success === 2){
                        toastr.success('Actualizado');
                        $('#modalEditar').modal('hide');
                        recargar();
                    }
                    else {
                        toastr.error('Error al actualizar');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Error al actualizar');
                });
        }

        function recargar(){
            var ruta = "{{ url('/admin/permisos/tabla') }}";
            $('#tablaDatatable').load(ruta);
        }

    </script>





@endsection




















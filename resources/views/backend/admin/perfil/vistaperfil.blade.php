@extends('adminlte::page')

@section('title', 'Perfil')

@section('content_header')
    <h1>Perfil</h1>
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
            <span class="d-none d-md-inline">{{ Auth::guard('admin')->user()->nombre }}</span>
        </a>



        <div class="dropdown-menu dropdown-menu-right">
            <a href="{{ route('admin.perfil') }}" class="dropdown-item">
                <i class="fas fa-user mr-2"></i> Editar Perfil
            </a>

            <div class="dropdown-divider"></div>

            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button type="submit" class="dropdown-item">
                    <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                </button>
            </form>
        </div>
    </li>
@endsection

@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-gray-dark">
                        <div class="card-header">
                            <h3 class="card-title">Formulario</h3>
                        </div>
                        <form>
                            <div class="card-body">

                                <div class="form-group">
                                    <label>Usuario</label>
                                    <input type="text" class="form-control" disabled value="{{ $usuario->usuario }}">
                                </div>

                                <div class="form-group">
                                    <label>Nueva Contraseña</label>
                                    <input type="text" maxlength="16" class="form-control" id="password" placeholder="Contraseña">
                                </div>

                                <div class="form-group">
                                    <label>Repetir Contraseña</label>
                                    <input type="text" maxlength="16" class="form-control" id="password1" placeholder="Contraseña">
                                </div>

                            </div>

                            <div class="card-footer" style="float: right;">
                                <button type="button" class="btn btn-success" onclick="actualizar()">Actualizar</button>
                            </div>
                        </form>
                    </div>

                </div>

            </div>
        </div>
    </section>
@stop

@section('js')
    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>


    <script src="{{ asset('js/theme.js') }}"></script>

    <script>
        function abrirModalAgregar(){
            document.getElementById("formulario-nuevo").reset();
            $('#modalAgregar').modal('show');
        }

        function actualizar(){
            var passwordNueva = document.getElementById('password').value;
            var passwordRepetida = document.getElementById('password1').value;

            if(passwordNueva === ''){
                toastr.error('contraseña nueva es requerida');
                return;
            }

            if(passwordRepetida === ''){
                toastr.error('contraseña repetida es requerida');
                return;
            }

            if(passwordNueva.length > 16){
                toastr.error('máximo 16 caracteres para contraseña nueva');
                return;
            }

            if(passwordNueva.length < 4){
                toastr.error('mínimo 4 caracteres para contraseña nueva');
                return;
            }

            if(passwordRepetida.length > 16){
                toastr.error('máximo 16 caracteres para contraseña repetida');
                return;
            }

            if(passwordRepetida.length < 4){
                toastr.error('mínimo 4 caracteres para contraseña repetida');
                return;
            }

            if(passwordNueva !== passwordRepetida){
                toastr.error('las contraseñas no coinciden');
                return;
            }

            openLoading()
            var formData = new FormData();
            formData.append('password', passwordNueva);

            axios.post(urlAdmin+'/admin/editar-perfil/actualizar', formData, {
            })
                .then((response) => {
                    closeLoading()

                    if (response.data.success === 1) {
                        toastr.success('Contraseña Actualizada');
                        $('#modalEditar').modal('hide');
                        document.getElementById('password').value = '';
                        document.getElementById('password1').value = '';
                    }
                    else {
                        toastr.error('error al actualizar');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('error al actualizar');
                });
        }
    </script>


@endsection













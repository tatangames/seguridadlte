@extends('adminlte::page')

@section('title', 'Error 404')

@section('content_header')
    <h1>Error 404</h1>
@stop
{{-- Activa plugins que necesitas --}}
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Toastr', true)
@section('plugins.Sweetalert2', true)

@section('content_top_nav_right')

    <li class="nav-item dropdown">
        <a href="#" class="nav-link" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-cogs"></i>
            <span class="d-none d-md-inline">{{ Auth::guard('admin')->user()->nombre ?? 'Usuario' }}</span>
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

    <div id="divcontenedor">
        <section class="content-header">
            <div class="container-fluid">
                <section class="content">
                    <div class="error-page">
                        <h2 class="headline text-danger">404</h2>

                        <div class="error-content">
                            <h3><i class="fas fa-exclamation-triangle text-danger"></i> Página no encontrada</h3>
                        </div>
                    </div>
                </section>
            </div>
        </section>
    </div>

@stop

@section('js')


@endsection

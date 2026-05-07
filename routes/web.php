<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Sistema\LoginController;
use App\Http\Controllers\Sistema\ControlController;
use App\Http\Controllers\Sistema\RolesController;
use App\Http\Controllers\Sistema\PerfilController;
use App\Http\Controllers\Sistema\PermisoController;
use App\Http\Controllers\Sistema\ConfiguracionController;
use App\Http\Controllers\Sistema\RepuestosController;
use App\Http\Controllers\Sistema\TipoProyectoController;


Route::get('/', [LoginController::class,'vistaLoginForm'])->name('login.admin');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');

//Route::middleware('auth:admin')->group(function () {

    // --- ROLES ---
    Route::get('/admin/roles/index', [RolesController::class,'index'])->name('admin.roles.index');
    Route::get('/admin/roles/tabla', [RolesController::class,'tablaRoles']);
    Route::get('/admin/roles/lista/permisos/{id}', [RolesController::class,'vistaPermisos']);
    Route::get('/admin/roles/permisos/tabla/{id}', [RolesController::class,'tablaRolesPermisos']);
    Route::post('/admin/roles/permiso/borrar', [RolesController::class, 'borrarPermiso']);
    Route::post('/admin/roles/permiso/agregar', [RolesController::class, 'agregarPermiso']);
    Route::get('/admin/roles/permisos/lista', [RolesController::class,'listaTodosPermisos']);
    Route::get('/admin/roles/permisos-todos/tabla', [RolesController::class,'tablaTodosPermisos']);
    Route::post('/admin/roles/borrar-global', [RolesController::class, 'borrarRolGlobal']);

    // --- PERMISOS ---
    Route::get('/admin/permisos/index', [PermisoController::class,'index'])->name('admin.permisos.index');
    Route::get('/admin/permisos/tabla', [PermisoController::class,'tablaUsuarios']);
    Route::post('/admin/permisos/nuevo-usuario', [PermisoController::class, 'nuevoUsuario']);
    Route::post('/admin/permisos/info-usuario', [PermisoController::class, 'infoUsuario']);
    Route::post('/admin/permisos/editar-usuario', [PermisoController::class, 'editarUsuario']);
    Route::post('/admin/permisos/nuevo-rol', [PermisoController::class, 'nuevoRol']);
    Route::post('/admin/permisos/extra-nuevo', [PermisoController::class, 'nuevoPermisoExtra']);
    Route::post('/admin/permisos/extra-borrar', [PermisoController::class, 'borrarPermisoGlobal']);

    // --- PERFIL ---
    Route::get('/admin/editar-perfil/index', [PerfilController::class,'indexEditarPerfil'])->name('admin.perfil');
    Route::post('/admin/editar-perfil/actualizar', [PerfilController::class, 'editarUsuario']);

    Route::get('sin-permisos', [ControlController::class,'indexSinPermiso'])->name('no.permisos.index');

    // --- CONTROL WEB ---
    Route::get('/panel', [ControlController::class,'indexRedireccionamiento'])->name('admin.panel');


    // --- UNIDAD DE MEDIDA ---
    Route::get('/admin/unidadmedida/index', [ConfiguracionController::class,'index'])->name('admin.unidadmedida.index');
    Route::get('/admin/unidadmedida/tabla/index', [ConfiguracionController::class,'tablaUnidadMedida']);
    Route::post('/admin/unidadmedida/nuevo', [ConfiguracionController::class, 'nuevaUnidadMedida']);
    Route::post('/admin/unidadmedida/informacion', [ConfiguracionController::class, 'informacionUnidadMedida']);
    Route::post('/admin/unidadmedida/editar', [ConfiguracionController::class, 'editarUnidadMedida']);

    // --- REGISTRO DE REPUESTOS PARA TENER UN INVENTARIO ---
    Route::get('/admin/inventario/index', [RepuestosController::class,'index'])->name('admin.materiales.index');
    Route::get('/admin/inventario/tabla/index', [RepuestosController::class,'tablaMateriales']);
    Route::post('/admin/inventario/nuevo', [RepuestosController::class, 'nuevoMaterial']);
    Route::post('/admin/inventario/informacion', [RepuestosController::class, 'informacionMaterial']);
    Route::post('/admin/inventario/editar', [RepuestosController::class, 'editarMaterial']);
    Route::post('/admin/informacion/herramienta/descartar', [RepuestosController::class, 'infoHerramientaDescartar']);
    Route::post('/admin/descartar/herramienta/inventario', [RepuestosController::class, 'descartarHerramientaInventario']);

    // --- REGISTRO DE UN PROYECTO ---
    Route::get('/admin/proyecto/index', [TipoProyectoController::class,'index'])->name('admin.tiposproyecto.index');
    Route::get('/admin/proyecto/tabla/index', [TipoProyectoController::class,'tablaProyectos']);
    Route::post('/admin/proyecto/nuevo', [TipoProyectoController::class, 'nuevoProyecto']);
    Route::post('/admin/proyecto/informacion', [TipoProyectoController::class, 'informacionProyecto']);
    Route::post('/admin/proyecto/editar', [TipoProyectoController::class, 'editarProyecto']);
    Route::post('/admin/proyecto/eliminar', [TipoProyectoController::class, 'borrarProyecto']);



//}); // end auth






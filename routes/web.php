<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Sistema\LoginController;
use App\Http\Controllers\Sistema\ControlController;
use App\Http\Controllers\Sistema\RolesController;
use App\Http\Controllers\Sistema\PerfilController;
use App\Http\Controllers\Sistema\PermisoController;
use App\Http\Controllers\Sistema\ConfiguracionController;
use App\Http\Controllers\Sistema\SalidasController;
use App\Http\Controllers\Sistema\HistorialController;
use App\Http\Controllers\Sistema\ReportesController;
use App\Http\Controllers\Sistema\MaterialesController;
use App\Http\Controllers\Sistema\RegistrosController;
use App\Http\Controllers\Sistema\UnidadEmpleadoController;
use App\Http\Controllers\Sistema\EmpleadoController;
use App\Http\Controllers\Sistema\HistorialSalidasController;


Route::get('/', [LoginController::class,'vistaLoginForm'])->name('login.admin');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');

Route::middleware('auth:admin')->group(function () {

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
    Route::get('/admin/unidadmedida/index', [ConfiguracionController::class,'indexUnidadMedida'])->name('admin.unidadmedida.index');
    Route::get('/admin/unidadmedida/tabla/index', [ConfiguracionController::class,'tablaUnidadMedida']);
    Route::post('/admin/unidadmedida/nuevo', [ConfiguracionController::class, 'nuevaUnidadMedida']);
    Route::post('/admin/unidadmedida/informacion', [ConfiguracionController::class, 'informacionUnidadMedida']);
    Route::post('/admin/unidadmedida/editar', [ConfiguracionController::class, 'editarUnidadMedida']);


    // --- RUBRO ---
    Route::get('/admin/rubro/index', [ConfiguracionController::class,'indexRubro'])->name('admin.rubro.index');
    Route::get('/admin/rubro/tabla/index', [ConfiguracionController::class,'tablaRubro']);
    Route::post('/admin/rubro/nuevo', [ConfiguracionController::class, 'nuevaRubro']);
    Route::post('/admin/rubro/informacion', [ConfiguracionController::class, 'informacionRubro']);
    Route::post('/admin/rubro/editar', [ConfiguracionController::class, 'editarRubro']);

    // --- CUENTA ---
    Route::get('/admin/cuenta/index', [ConfiguracionController::class, 'indexCuenta'])->name('admin.cuenta.index');
    Route::get('/admin/cuenta/tabla/index', [ConfiguracionController::class, 'tablaCuenta']);
    Route::post('/admin/cuenta/nuevo', [ConfiguracionController::class, 'nuevaCuenta']);
    Route::post('/admin/cuenta/informacion', [ConfiguracionController::class, 'informacionCuenta']);
    Route::post('/admin/cuenta/editar', [ConfiguracionController::class, 'editarCuenta']);

    // --- OBJETO ESPECIFICO ---
    Route::get('/admin/objetoespecifico/index', [ConfiguracionController::class, 'indexObjetoEspecifico'])->name('admin.objetoespecifico.index');
    Route::get('/admin/objetoespecifico/tabla/index', [ConfiguracionController::class, 'tablaObjetoEspecifico']);
    Route::post('/admin/objetoespecifico/nuevo', [ConfiguracionController::class, 'nuevaObjetoEspecifico']);
    Route::post('/admin/objetoespecifico/informacion', [ConfiguracionController::class, 'informacionObjetoEspecifico']);
    Route::post('/admin/objetoespecifico/editar', [ConfiguracionController::class, 'editarObjetoEspecifico']);

    // --- MARCA ---
    Route::get('/admin/marca/index', [ConfiguracionController::class,'vistaMarca'])->name('admin.marca.index');
    Route::get('/admin/marca/tabla/index', [ConfiguracionController::class,'tablaMarca']);
    Route::post('/admin/marca/nuevo', [ConfiguracionController::class,'nuevoMarca']);
    Route::post('/admin/marca/informacion', [ConfiguracionController::class,'infoMarca']);
    Route::post('/admin/marca/editar', [ConfiguracionController::class,'actualizarMarca']);

    // --- COLOR ---
    Route::get('/admin/color/index', [ConfiguracionController::class,'vistaColor'])->name('admin.color.index');
    Route::get('/admin/color/tabla/index', [ConfiguracionController::class,'tablaColor']);
    Route::post('/admin/color/nuevo', [ConfiguracionController::class,'nuevoColor']);
    Route::post('/admin/color/informacion', [ConfiguracionController::class,'infoColor']);
    Route::post('/admin/color/editar', [ConfiguracionController::class,'actualizarColor']);

    // --- TALLA ---
    Route::get('/admin/talla/index', [ConfiguracionController::class,'vistaTalla'])->name('admin.talla.index');
    Route::get('/admin/talla/tabla/index', [ConfiguracionController::class,'tablaTalla']);
    Route::post('/admin/talla/nuevo', [ConfiguracionController::class,'nuevoTalla']);
    Route::post('/admin/talla/informacion', [ConfiguracionController::class,'infoTalla']);
    Route::post('/admin/talla/editar', [ConfiguracionController::class,'actualizarTalla']);

    // --- NORMATIVA ---
    Route::get('/admin/normativa/index', [ConfiguracionController::class,'vistaNormativa'])->name('admin.normativa.index');
    Route::get('/admin/normativa/tabla/index', [ConfiguracionController::class,'tablaNormativa']);
    Route::post('/admin/normativa/nuevo', [ConfiguracionController::class,'nuevoNormativa']);
    Route::post('/admin/normativa/informacion', [ConfiguracionController::class,'infoNormativa']);
    Route::post('/admin/normativa/editar', [ConfiguracionController::class,'actualizarNormativa']);

    // --- PROVEEDOR ---
    Route::get('/admin/proveedor/index', [ConfiguracionController::class,'vistaProveedor'])->name('admin.proveedor.index');
    Route::get('/admin/proveedor/tabla/index', [ConfiguracionController::class,'tablaProveedor']);
    Route::post('/admin/proveedor/nuevo', [ConfiguracionController::class,'nuevoProveedor']);
    Route::post('/admin/proveedor/informacion', [ConfiguracionController::class,'infoProveedor']);
    Route::post('/admin/proveedor/editar', [ConfiguracionController::class,'actualizarProveedor']);

    // --- CARGO ---
    Route::get('/admin/cargo/index', [ConfiguracionController::class,'vistaCargo'])->name('admin.cargo.index');
    Route::get('/admin/cargo/tabla/index', [ConfiguracionController::class,'tablaCargo']);
    Route::post('/admin/cargo/nuevo', [ConfiguracionController::class,'nuevoCargo']);
    Route::post('/admin/cargo/informacion', [ConfiguracionController::class,'infoCargo']);
    Route::post('/admin/cargo/editar', [ConfiguracionController::class,'actualizarCargo']);

    // --- JEFE FIRMA  ---
    Route::get('/admin/jefefirma/index', [ConfiguracionController::class,'vistaJefeFirma'])->name('admin.jefefirma.index');
    Route::get('/admin/jefefirma/tabla/index', [ConfiguracionController::class,'tablaJefeFirma']);
    Route::post('/admin/jefefirma/nuevo', [ConfiguracionController::class,'nuevoJefeFirma']);
    Route::post('/admin/jefefirma/informacion', [ConfiguracionController::class,'infoJefeFirma']);
    Route::post('/admin/jefefirma/editar', [ConfiguracionController::class,'actualizarJefeFirma']);

    // --- MATERIALES ---
    Route::get('/admin/materiales/index', [MaterialesController::class,'vistaMateriales'])->name('admin.materiales.index');
    Route::get('/admin/materiales/tabla', [MaterialesController::class,'tablaMateriales']);

    Route::post('/admin/materiales/nuevo', [MaterialesController::class, 'nuevoMaterial']);
    Route::post('/admin/materiales/informacion', [MaterialesController::class, 'informacionMaterial']);
    Route::post('/admin/materiales/editar', [MaterialesController::class, 'editarMaterial']);

    // REGISTRO DE ENTRADAS
    Route::get('/admin/entradas/vista', [RegistrosController::class,'indexRegistroEntrada'])->name('admin.entrada.registro.index');
    Route::post('/admin/buscar/material',  [RegistrosController::class,'buscadorMaterialGlobal']);
    Route::post('/admin/entradas/guardar',  [RegistrosController::class,'guardarEntrada']);
    Route::post('/admin/buscar/materiales/porcodigo',  [RegistrosController::class,'buscarMaterialesPorCodigo']);

    // REGISTRO DE SALIDAS
    Route::get('/admin/salidas/vista', [RegistrosController::class,'indexRegistroSalida'])->name('admin.salidas.registro.index');
    Route::post('/admin/buscar/material/disponible',  [RegistrosController::class,'buscadorMaterialDisponible']);
    Route::post('/admin/buscar/material/disponibilidad', [RegistrosController::class, 'infoBodegaMaterialDetalleFila']);
    Route::post('/admin/salida/guardar',  [RegistrosController::class,'guardarSalidaMateriales']);
    Route::post('/admin/salidas/pdf-temporal', [RegistrosController::class, 'generarPdfTemporal']);
    Route::get('/admin/salidas/pdfcompleto/{idsalida}', [RegistrosController::class,'generarPdfSalida']);
    Route::post('/admin/empleados/buscarunidad', [RegistrosController::class,'buscarUnidadConDistrito']);
    Route::post('/admin/empleados/buscarunidad-empleado', [RegistrosController::class,'buscarUnidadConDistritoEmpleado']);

    // UNIDAD EMPLEADO
    Route::get('/admin/unidadempleado/index', [UnidadEmpleadoController::class,'vistaUnidadEmpleado'])->name('admin.unidadempleado.index');
    Route::get('/admin/unidadempleado/tabla', [UnidadEmpleadoController::class, 'tablaUnidadEmpleado']);
    Route::post('/admin/unidadempleado/nuevo', [UnidadEmpleadoController::class,'nuevoUnidadEmpleado']);
    Route::post('/admin/unidadempleado/informacion', [UnidadEmpleadoController::class,'infoUnidadEmpleado']);
    Route::post('/admin/unidadempleado/editar', [UnidadEmpleadoController::class,'actualizarUnidadEmpleado']);
    Route::post('/admin/unidadempleado/jefeinmediato/editar', [UnidadEmpleadoController::class,'editarJefeInmediato']);
    Route::post('/admin/unidadempleado/jefes/informacion', [UnidadEmpleadoController::class, 'informacionJefesUnidad']);
    Route::post('/admin/unidadempleado/jefes/agregar',     [UnidadEmpleadoController::class, 'agregarJefeUnidad']);
    Route::post('/admin/unidadempleado/jefes/quitar',      [UnidadEmpleadoController::class, 'quitarJefeUnidad']);

    // EMPLEADOS
    Route::get('/admin/empleados/index',         [EmpleadoController::class, 'index']        )->name('admin.empleados.index');
    Route::get('/admin/empleados/tabla',         [EmpleadoController::class, 'tabla']        );
    Route::post('/admin/empleados/buscarunidad', [EmpleadoController::class, 'buscarUnidad'] );
    Route::post('/admin/empleados/nuevo',        [EmpleadoController::class, 'nuevo']        );
    Route::post('/admin/empleados/informacion',  [EmpleadoController::class, 'informacion']  );
    Route::post('/admin/empleados/actualizar',   [EmpleadoController::class, 'actualizar']   );








    // --- HISTORIAL / ENTRADAS ---
    Route::get('/admin/historial/entradas', [HistorialController::class,'indexHistorialEntradas'])->name('admin.historial.entradas.index');
    Route::get('/admin/historial/entradas/tabla',  [HistorialController::class,'tablaHistorialEntradas']);
    Route::post('/admin/historial/entradas/informacion', [HistorialController::class, 'informacionEntrada']);
    Route::post('/admin/historial/entradas/editar',      [HistorialController::class, 'editarEntrada']);
    Route::post('/admin/historial/entradas/eliminar',    [HistorialController::class, 'eliminarEntrada']);
    Route::post('/admin/historial/entradas/detalle',        [HistorialController::class, 'detalleEntrada']);
    Route::post('/admin/historial/entradas/detalle/editar', [HistorialController::class, 'editarDetalleEntrada']);
    Route::post('/admin/historial/entradas/detalle/eliminar', [HistorialController::class, 'eliminarDetalleEntrada']);
    Route::get('/admin/historial/nuevoingresoentradadetalle/index/{id}', [HistorialController::class, 'vistaExtrasEntrada'])->name('admin.historial.entradas.extras');
    Route::post('/admin/historial/entradas/extras/guardar', [HistorialController::class, 'guardarExtrasEntrada']);



    // --- HISTORIAL / SALIDAS ---
    Route::get('/admin/historial/salidas', [HistorialSalidasController::class,'indexHistorialSalidas'])->name('admin.historial.salidas.index');
    Route::get('/admin/historial/salidas/tabla',  [HistorialSalidasController::class,'tablaHistorialSalidas']);
    Route::post('/admin/historial/salidas/informacion', [HistorialSalidasController::class, 'informacionSalida']);
    Route::post('/admin/historial/salidas/editar',      [HistorialSalidasController::class, 'editarSalida']);
    Route::post('/admin/historial/salidas/eliminar',    [HistorialSalidasController::class, 'eliminarSalida']);
    Route::post('/admin/historial/salidas/detalle', [HistorialSalidasController::class, 'detalleSalida']);
    Route::get('/admin/historial/salidas/extras/{id}',      [HistorialSalidasController::class, 'vistaExtrasSalida'])->name('admin.historial.salidas.extras');
    Route::post('/admin/historial/salidas/extras/guardar',  [HistorialSalidasController::class, 'guardarExtrasSalida']);
    Route::post('/admin/historial/salidas/detalle/eliminar', [HistorialSalidasController::class, 'eliminarItemDetalleSalida']);






    // --- REPORTES DE DIFERENTES FORMULARIOS ---
    Route::post('/admin/reporte/form/solicitud/preview',
        [ReportesController::class, 'formSolicitudPreview'])
        ->name('reporte.form.solicitud.preview');

    Route::post('/admin/reporte/form003/solicitud/preview',
        [ReportesController::class, 'form003SolicitudPreview'])
        ->name('reporte.form003.solicitud.preview');

    Route::post('/admin/reporte/acta/preview',
        [ReportesController::class, 'actaRecepcionPreview'])
        ->name('reporte.acta.preview');

    Route::post('/admin/reporte/acta/preview/reserva',
        [ReportesController::class, 'actaRecepcionPreviewReserva'])
        ->name('reporte.acta.preview');


    Route::post('/admin/reporte/form001/reserva/preview',
        [ReportesController::class, 'form001ReservaPreview'])
        ->name('reporte.form001.reserva.preview');


    // --- REPORTE / ENTRADA POR PROYECTO
    Route::get('/admin/reporte/inventario/quehaentrado/proyecto', [ReportesController::class,'vistaQueHaEntradoProyecto'])->name('admin.reporte.inventario.entradaproyecto.index');
    Route::get('/admin/reporte/quehaentrado/proyectos/pdf/{idproy}/{desde}/{hasta}/{tipo}', [ReportesController::class,'pdfQueHaEntradoProyectos']);

    // --- REPORTE / SALIDA POR PROYECTO
    Route::get('/admin/reporte/quehasalido/proyectos/pdf/{idproy}/{desde}/{hasta}/{tipo}', [ReportesController::class,'pdfQueHaSalidoProyectos']);

    // --- REPORTE / INVENTARIO PROYECTO
    Route::get('/admin/reporte/inventario/quetengopor/proyecto', [ReportesController::class,'vistaQueTengoPorProyecto'])->name('admin.reporte.inventario.tengoporproyecto.index');
    Route::get('/admin/reporte/quetengopor/proyectos/pdf/{idproy}', [ReportesController::class,'reporteQueTengoPorProyecto']);
    Route::post('/admin/firmas/proyectos/completado/actualizar', [ReportesController::class, 'actualizarFirmasSobrantes']);
    Route::post('/admin/firmas/proyectos/traspaso/actualizar', [ReportesController::class, 'actualizarFirmasTraspaso']);




    // --- REPORTE / VER LOS MATERIALES QUE SOBRARON DE UN PROYECTO COMPLETADO
    Route::get('/admin/reporte/inventario/sobranteterminado/proy/{idtrans}', [ReportesController::class,'reporteProyectoTerminado']);


    // Destino de sobrantes — a proyecto o salida general - GEAD-002-FORM
    Route::get('/admin/reporte/inventario/destino/sobrantes/{idtrans}/{tipo}',
        [ReportesController::class, 'reporteDestinoSobrantes']);

    // Destino de sobrantes — reporte DESCRIPTIVO (transferencias + generales + reservas)
    Route::get('/admin/reporte/inventario/destino/sobrantesdescriptivo/{idtrans}',
        [ReportesController::class, 'reporteDestinoSobrantesDescriptivo']);


    // --- REPORTE / ENTREGAS MENSUALES - GEAD-002-REPO
    Route::get('/admin/reporte/proyectos/codigos', [ReportesController::class,'vistaReporteProyectoCodigos'])->name('admin.reporte.proyectos.codigos.index');
    Route::get('/admin/reporte/proyectos/codigos/pdf/{idproy}/{desde}/{hasta}/{descripcion?}', [ReportesController::class, 'reportePDFProyectoCodigos']);

    // --- REPORTE / PROYECTO CERRADO - INVENTARIO QUE SOBRO
    Route::get('/admin/reporte/proyectos/codigos', [ReportesController::class,'vistaReporteSobranteProyectoCerrado'])->name('reporte.proyecto.cerrado.index');
    Route::post('/admin/reporte/proyectos/cerrado/pdf', [ReportesController::class, 'vistaPDFReporteSobranteProyectoCerrado']);
    Route::post('/admin/firmas/proyectos/cerrado/actualizar', [ReportesController::class, 'actualizarFirmasReporteCerrado']);

    // --- REPORTE /POR PERIODOS
    Route::get('/admin/reporte/proyectos/periodos', [ReportesController::class,'vistaReportePorPeriodos'])->name('reporte.proyecto.porperiodos.index');
    Route::post('/admin/reporte/proyectos/periodos/pdf', [ReportesController::class, 'vistaPDFReportePorPeriodos']);
    Route::post('/admin/firmas/proyectos/periodos/actualizar', [ReportesController::class, 'actualizarFirmasReportePeriodos']);

    // --- ACTUALIZAR FIRMAS LAS DISTANCIAS DE LOS REPORTES ---
    Route::post('/admin/informacion/actualizar/px', [ReportesController::class, 'actualizarPxInformacionGeneral'])
        ->name('admin.informacion.actualizar.px');

    // --- REPORTE SALIDA TALONARIO ---
    Route::post('/admin/reporte/talonario/salida', [ReportesController::class, 'pdfReporteSalidaTalonario']);





}); // end auth






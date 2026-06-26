<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="tabla" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th style="width:9%">Fecha</th>
                                <th style="width:9%">Bodega</th>
                                <th style="width:10%">Lote / Factura</th>
                                <th style="width:13%">Proveedor</th>
                                <th style="width:9%">Total</th>
                                <th style="width:23%">Descripción</th>
                                <th style="width:27%">Opciones</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach($arrayEntradas as $dato)
                                <tr>
                                    <td data-order="{{ $dato->fecha }}">
                                        {{ $dato->fecha_fmt }}
                                    </td>

                                    <td>
                                        {{ $dato->bodega->nombre ?? 'Sin bodega' }}
                                    </td>

                                    <td>
                                        {{ $dato->lote ?? '—' }}
                                    </td>

                                    <td>
                                        {{ $dato->proveedor->nombre ?? '—' }}
                                    </td>

                                    <td class="text-right font-weight-bold text-success">
                                        ${{ number_format($dato->totalEntrada, 4) }}
                                    </td>

                                    <td>
                                        {{ $dato->descripcion ?? '—' }}
                                    </td>

                                    <td class="text-center">
                                        <button type="button"
                                                style="margin:2px"
                                                class="btn btn-success btn-xs"
                                                onclick="infoNuevoIngreso({{ $dato->id }})">
                                            <i class="fas fa-plus"></i> Ingreso
                                        </button>

                                        <button type="button"
                                                style="margin:2px"
                                                class="btn btn-info btn-xs"
                                                onclick="verDetalle({{ $dato->id }}, '{{ addslashes($dato->lote ?? '') }}', '{{ $dato->fecha_fmt }}')">
                                            <i class="fas fa-list"></i> Detalle
                                        </button>

                                        <button type="button"
                                                style="margin:2px"
                                                class="btn btn-warning btn-xs"
                                                onclick="modalEditar({{ $dato->id }})">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>

                                        <button type="button"
                                                style="margin:2px"
                                                class="btn btn-danger btn-xs"
                                                onclick="eliminar({{ $dato->id }})">
                                            <i class="fas fa-trash"></i> Borrar
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

<script>
    function infoNuevoIngreso(id) {
        window.location.href =
            urlAdmin + '/admin/historial/nuevoingresoentradadetalle/index/' + id;
    }

    setTimeout(function () {
        closeLoading();
    }, 400);
</script>

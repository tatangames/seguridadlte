<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="tabla" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th style="width:10%">Fecha</th>
                                <th style="width:15%">Empleado</th>
                                <th style="width:15%">Colaborador</th>
                                <th style="width:28%">Descripción</th>
                                <th style="width:20%">Opciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($arraySalidas as $dato)
                                <tr>
                                    <td data-order="{{ $dato->fecha }}">{{ $dato->fecha_fmt }}</td>
                                    <td>{{ $dato->empleado->nombre ?? '—' }}</td>
                                    <td>{{ $dato->colaborador ?? '—' }}</td>
                                    <td>{{ $dato->descripcion ?? '—' }}</td>
                                    <td class="text-center">
                                        <button type="button" style="margin:2px"
                                                class="btn btn-success btn-xs"
                                                onclick="window.location.href='{{ url('/admin/historial/salidas/extras') }}/' + {{ $dato->id }}">
                                            <i class="fas fa-plus"></i> Extras
                                        </button>
                                        <button type="button" style="margin:2px"
                                                class="btn btn-info btn-xs"
                                                onclick="verDetalle({{ $dato->id }}, '{{ $dato->fecha_fmt }}')">
                                            <i class="fas fa-list"></i> Detalle
                                        </button>
                                        <button type="button" style="margin:2px"
                                                class="btn btn-secondary btn-xs"
                                                onclick="window.open('{{ url('/admin/salidas/pdfcompleto') }}/' + {{ $dato->id }}, '_blank')">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </button>
                                        <button type="button" style="margin:2px"
                                                class="btn btn-warning btn-xs"
                                                onclick="modalEditar({{ $dato->id }})">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                        <button type="button" style="margin:2px"
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
    setTimeout(function () { closeLoading(); }, 400);
</script>

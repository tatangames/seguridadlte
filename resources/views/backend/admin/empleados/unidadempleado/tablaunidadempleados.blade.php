
<table id="tablaUnidad" class="table table-bordered table-striped" style="width:100%">
    <thead>
    <tr>
        <th style="width:25%">Nombre</th>
        <th style="width:16%">Distrito</th>
        <th style="width:35%">Jefes Asignados</th>
        <th style="width:24%">Opciones</th>
    </tr>
    </thead>
    <tbody>
    @foreach($listado as $dato)
        <tr>
            <td class="nombre-uni">{{ $dato['nombre'] }}</td>
            <td>{{ $dato['distrito'] }}</td>
            <td>
                @if(count($dato['jefes']) > 0)
                    @foreach($dato['jefes'] as $j)
                        <span class="badge-jefe-asig">
                            <i class="fas fa-user-tie" style="font-size:9px"></i>
                            {{ $j['nombre'] }}
                        </span>
                    @endforeach
                @else
                    <span class="badge-sin-jefe">Sin asignar</span>
                @endif
            </td>
            <td>
                <button type="button"
                        class="btn btn-info btn-xs btn-editar-unidad"
                        data-id="{{ $dato['id'] }}">
                    <i class="fas fa-edit"></i> Editar
                </button>

                <button type="button"
                        class="btn btn-success btn-xs btn-gestionar-jefes"
                        data-id="{{ $dato['id'] }}"
                        data-nombre="{{ addslashes($dato['nombre']) }}">
                    <i class="fas fa-user-tie"></i> Gestionar Jefes
                </button>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

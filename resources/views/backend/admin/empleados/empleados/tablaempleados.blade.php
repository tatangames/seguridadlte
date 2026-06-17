<table id="tablaEmpleados" class="table table-bordered table-striped" style="width:100%">
    <thead>
    <tr>
        <th style="width:18%">Nombre</th>
        <th style="width:11%">Distrito</th>
        <th style="width:15%">Unidad</th>
        <th style="width:12%">Cargo</th>
        <th style="width:10%">DUI</th>
        <th style="width:7%">Rol</th>
        <th style="width:7%">Estado</th>
        <th style="width:12%">Jefe Directo</th>
        <th style="width:8%">Opciones</th>
    </tr>
    </thead>
    <tbody>
    @foreach($listado as $dato)
        <tr data-distrito="{{ $dato['distrito'] }}"
            data-unidad="{{ $dato['unidad'] }}"
            data-jefe="{{ $dato['jefe'] }}"
            data-activo="{{ $dato['activo'] }}">
            <td class="nombre-emp">{{ $dato['nombre'] }}</td>
            <td>{{ $dato['distrito'] }}</td>
            <td>{{ $dato['unidad'] }}</td>
            <td>{{ $dato['cargo'] }}</td>
            <td class="dui-txt">{{ $dato['dui'] }}</td>
            <td>
                @if($dato['jefe'])
                    <span class="badge-jefe"><i class="fas fa-star" style="font-size:9px"></i> Jefe</span>
                @else
                    <span class="badge-empleado">Empleado</span>
                @endif
            </td>
            <td>
                @if($dato['activo'])
                    <span class="badge-activo"><i class="fas fa-check" style="font-size:9px"></i> Activo</span>
                @else
                    <span class="badge-inactivo"><i class="fas fa-times" style="font-size:9px"></i> Inactivo</span>
                @endif
            </td>
            <td class="jefe-txt">{{ $dato['jefe_nombre'] }}</td>
            <td>
                <button type="button"
                        class="btn btn-success btn-xs btn-editar-empleado"
                        data-id="{{ $dato['id'] }}">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

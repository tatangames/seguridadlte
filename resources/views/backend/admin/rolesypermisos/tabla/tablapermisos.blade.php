<table id="tabla" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Nombre</th>
        <th>Rol</th>
        <th>Usuario</th>
        <th>Opciones</th>
    </tr>
    </thead>
    <tbody>

    @foreach($usuarios as $dato)
        <tr>
            <td>{{ $dato->nombre }}</td>
            <td>{{ $dato->roles->implode('name', ', ') }}</td>
            <td>{{ $dato->usuario }}</td>

            <td>
                <button type="button" class="btn btn-primary btn-xs" onclick="verInformacion({{ $dato->id }})">
                    <i class="fas fa-pencil-alt" title="Editar"></i>&nbsp; Editar
                </button>
            </td>

        </tr>
    @endforeach

    </tbody>

</table>

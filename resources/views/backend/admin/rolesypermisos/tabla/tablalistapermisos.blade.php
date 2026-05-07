<table id="tabla" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>ID</th>
        <th>Permiso</th>
        <th>Descripción</th>
        <th>Opciones</th>
    </tr>
    </thead>
    <tbody>

    @foreach($permisos as $ll)
        <tr>
            <td>{{ $ll->id }}</td>
            <td>{{ $ll->name }}</td>
            <td>{{ $ll->description }}</td>

            <td>
                <button type="button" class="btn btn-danger btn-xs" onclick="modalBorrar({{ $ll->id }})">
                    <i class="fas fa-trash-alt" title="Eliminar Global"></i>&nbsp; Eliminar Global
                </button>
            </td>
        </tr>

    @endforeach

    </tbody>

</table>

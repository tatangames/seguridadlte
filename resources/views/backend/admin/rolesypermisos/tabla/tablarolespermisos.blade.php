<table id="tabla" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>ID</th>
        <th>Permiso</th>
        <th>Opciones</th>
    </tr>
    </thead>
    <tbody>

    @foreach($permisos as $key => $value)
        <tr>
            <td>{{ $key }}</td>
            <td>{{ $value }}</td>

            <td>
                <button type="button" class="btn btn-danger btn-xs" onclick="modalBorrar({{ $key }})">
                    <i class="fas fa-trash-alt" title="Eliminar"></i>&nbsp; Eliminar
                </button>
            </td>
        </tr>

    @endforeach

    </tbody>

</table>

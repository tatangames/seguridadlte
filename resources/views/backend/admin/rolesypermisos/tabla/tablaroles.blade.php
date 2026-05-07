<table id="tabla" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>ID</th>
        <th>Rol</th>
        <th>Opciones</th>
    </tr>
    </thead>
    <tbody>
    @foreach($roles as $key => $value)
        <tr>
            <td>{{ $key }}</td>
            <td>{{ $value }}</td>
            <td>
                <button type="button" class="btn btn-primary btn-xs" onclick="verInformacion({{ $key }})">
                    <i class="fas fa-pencil-alt"></i>&nbsp; Editar
                </button>
                <button type="button" class="btn btn-danger btn-xs" onclick="modalBorrar({{ $key }})">
                    <i class="fas fa-trash-alt"></i>&nbsp; Eliminación Global
                </button>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

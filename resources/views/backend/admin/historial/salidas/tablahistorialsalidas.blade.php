@php
    $mesActual = \Carbon\Carbon::now()->startOfMonth();
@endphp

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover table-sm" id="tabla">
        <thead class="thead-dark">
        <tr>
            <th style="width:8%">Fecha</th>
            <th>Empleado</th>
            <th>Área</th>
            <th>Cargo</th>
            <th>Descripción</th>
            <th class="text-center">Acciones</th>
        </tr>
        </thead>
        <tbody>
        @forelse($arraySalidas as $dato)
            @php
                $esMesActual = \Carbon\Carbon::parse($dato->fecha)->isSameMonth($mesActual);
            @endphp
            <tr>
                <td data-order="{{ $dato->fecha }}">{{ $dato->fecha_fmt }}</td>
                <td>{{ $dato->empleado->nombre ?? '—' }}</td>
                <td>{{ $dato->area ?? '—' }}</td>
                <td>{{ $dato->cargo ?? '—' }}</td>
                <td>{{ $dato->descripcion ?? '—' }}</td>
                <td class="text-center text-nowrap">

                    {{-- Detalle: siempre visible --}}
                    <button type="button" style="margin:2px"
                            class="btn btn-info btn-xs"
                            onclick="verDetalle({{ $dato->id }}, '{{ $dato->fecha_fmt }}')">
                        <i class="fas fa-list"></i> Detalle
                    </button>

                    {{-- PDF: siempre visible --}}
                    <button type="button" style="margin:2px"
                            class="btn btn-secondary btn-xs"
                            onclick="window.open('{{ url('/admin/salidas/pdfcompleto') }}/{{ $dato->id }}', '_blank')">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>

                    {{-- Solo mes actual: Extras, Editar, Borrar --}}
                    @if($esMesActual)
                        <button type="button" style="margin:2px"
                                class="btn btn-success btn-xs"
                                onclick="window.location.href='{{ url('/admin/historial/salidas/extras') }}/{{ $dato->id }}'">
                            <i class="fas fa-plus"></i> Extras
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
                    @endif

                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-3">
                    No se encontraron registros con los filtros aplicados.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<script>
    setTimeout(function () { closeLoading(); }, 400);
</script>

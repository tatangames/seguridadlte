<tbody>
@foreach($lista as $dato)
    @php
        $stock = (int) ($dato->cantidadGlobal ?? 0);

        if ($stock >= 5) {
            $stockClass = 'ok';
        } elseif ($stock >= 1) {
            $stockClass = 'warn';
        } else {
            $stockClass = 'danger';
        }

        $meses = (int) ($dato->meses_cambio ?? 0);

        if ($meses === 0) {
            $mesesClass = 'sindata';
            $mesesLabel = '—';
        } elseif ($meses <= 3) {
            $mesesClass = 'proximo';
            $mesesLabel = $meses . ' mes' . ($meses > 1 ? 'es' : '');
        } else {
            $mesesClass = 'vigente';
            $mesesLabel = $meses . ' meses';
        }
    @endphp

    <tr
        data-marca="{{ strtolower($dato->marca ?? '') }}"
        data-unidad="{{ strtolower($dato->unidadMedida ?? '') }}"
        data-normativa="{{ strtolower($dato->normativa ?? '') }}"
        data-stock="{{ $stock }}"
        data-meses="{{ $meses }}"
        data-sinfecha="{{ $meses === 0 ? '1' : '0' }}"
    >

        {{-- Código --}}
        <td data-order="{{ $dato->codigo ?? '' }}">
            @if($dato->codigo)
                {{ $dato->codigo }}
            @else
                <span style="color:#cbd5e1">—</span>
            @endif
        </td>

        {{-- Nombre --}}
        <td data-order="{{ $dato->nombre }}" style="font-weight:500">
            {{ $dato->nombre }}
        </td>

        {{-- Medida --}}
        <td data-order="{{ $dato->unidadMedida }}" style="font-size:12px">
            {{ $dato->unidadMedida }}
        </td>

        {{-- Marca --}}
        <td data-order="{{ $dato->marca }}" style="font-size:12px">
            {{ $dato->marca }}
        </td>

        {{-- Normativa --}}
        <td data-order="{{ $dato->normativa }}" style="font-size:12px">
            {{ $dato->normativa }}
        </td>

        {{-- Talla --}}
        <td data-order="{{ $dato->talla }}" style="font-size:12px">
            {{ $dato->talla ?: '—' }}
        </td>

        {{-- Otros --}}
        <td data-order="{{ $dato->otros }}" style="font-size:12px;color:#475569">
            {{ $dato->otros ?: '—' }}
        </td>

        {{-- Stock --}}
        <td data-order="{{ $stock }}">
            <span class="stock-badge {{ $stockClass }}">
                <span class="dot"></span>
                {{ $stock }}
            </span>
        </td>

        {{-- Meses Cambio --}}
        <td data-order="{{ $meses }}">
            <span class="meses-badge {{ $mesesClass }}">
                <i class="fas fa-clock" style="font-size:10px"></i>
                {{ $mesesLabel }}
            </span>
        </td>

        {{-- Opciones --}}
        <td>
            <button type="button" style="margin: 2px" class="btn btn-info btn-xs"
                    onclick="informacion({{ $dato->id }})">
                <i class="fas fa-edit"></i> Editar
            </button>
        </td>
    </tr>
@endforeach
</tbody>

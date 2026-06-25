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

      $oeLabel = $dato->oe_codigo ?? $dato->oe_nombre ?? null;
    @endphp

    <tr
        data-marca="{{ strtolower($dato->marca ?? '') }}"
        data-unidad="{{ strtolower($dato->unidadMedida ?? '') }}"
        data-normativa="{{ strtolower($dato->normativa ?? '') }}"
        data-stock="{{ $stock }}"
        data-oe="{{ strtolower($oeLabel ?? '') }}"
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
        <td data-order="{{ $dato->unidadMedida }}">
            {{ $dato->unidadMedida }}
        </td>

        {{-- Marca --}}
        <td data-order="{{ $dato->marca }}">
            {{ $dato->marca }}
        </td>

        {{-- Normativa --}}
        <td data-order="{{ $dato->normativa }}">
            {{ $dato->normativa }}
        </td>

        {{-- Talla --}}
        <td data-order="{{ $dato->talla }}">
            {{ $dato->talla ?: '—' }}
        </td>

        {{-- Otros --}}
        <td data-order="{{ $dato->otros }}" style="color:#475569">
            {{ $dato->otros ?: '—' }}
        </td>

        {{-- Stock --}}
        <td data-order="{{ $stock }}">
            <span class="stock-badge {{ $stockClass }}">
                <span class="dot"></span>
                {{ $stock }}
            </span>
        </td>

        {{-- Objeto Específico --}}
        <td data-order="{{ $oeLabel ?? '' }}">
            @if($oeLabel)
                <span class="badge badge-success" style="font-size:11px; font-weight:600; letter-spacing:.3px;">
            {{ $oeLabel }}
        </span>
            @else
                <span style="color:#cbd5e1">—</span>
            @endif
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

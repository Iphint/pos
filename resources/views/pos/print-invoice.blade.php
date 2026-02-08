<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nota Pembayaran</title>

    <style>
        body {
            font-family: monospace;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .receipt {
            width: 300px; /* 58mm = ±220px | 80mm = ±300px */
            margin: auto;
            padding: 10px;
        }

        .center {
            text-align: center;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .small {
            font-size: 11px;
        }
    </style>
</head>
<body>

<div class="receipt">

    {{-- HEADER --}}
    <div class="center">
        <strong>TOKO LILY</strong><br>
        Jl. Griya Permata Raya 1 No.54<br>
        Handil Bakti, Kalsel<br>
        <span class="small">Telp: {{ $customer->phone }}</span>
    </div>

    <div class="line"></div>

    {{-- INFO --}}
    <div>
        Tanggal : {{ now()->format('d/m/Y H:i') }}<br>
        Customer: {{ $customer->name }}<br>
        Invoice : #{{ rand(100000,999999) }}
    </div>

    <div class="line"></div>

    {{-- ITEMS --}}
    @foreach ($content as $item)
        <div>
            {{ $item->name }}
            <div class="item small">
                <span>{{ $item->qty }} x {{ number_format($item->price) }}</span>
                <span>{{ number_format($item->subtotal) }}</span>
            </div>
        </div>
    @endforeach

    <div class="line"></div>

    {{-- TOTAL --}}
    <div class="item">
        <strong>TOTAL</strong>
        <strong>{{ number_format(Cart::total()) }}</strong>
    </div>

    <div class="line"></div>

    {{-- FOOTER --}}
    <div class="center small">
        Terima kasih 🙏<br>
        Barang yang sudah dibeli<br>
        tidak dapat dikembalikan
    </div>

</div>

<script>
    window.onload = function () {
        window.print();
    }
</script>

</body>
</html>
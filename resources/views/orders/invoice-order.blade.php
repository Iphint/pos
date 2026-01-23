<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>POS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">

    <!-- External CSS libraries -->
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/css/bootstrap.min.css') }}">

    <!-- Google fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Custom Stylesheet -->
    <link type="text/css" rel="stylesheet" href="{{ asset('assets/invoice/css/style.css') }}">
</head>
<body>
    <div class="invoice-16 invoice-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="invoice-inner-9" id="invoice_wrapper">
                        <div class="invoice-top">
                            <div class="row">
                                <div class="col-lg-6 col-sm-6">
                                    <div class="logo">
                                        <img class="logo" src="{{ asset('assets/images/logo.png') }}" alt="logo">
                                    </div>
                                </div>
                                <div class="col-lg-6 col-sm-6">
                                    <div class="invoice">
                                        <h1>#<span>{{ $order->invoice_no }}</span></h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="invoice-info">
                            <div class="row">
                                <div class="col-sm-6 mb-50">
                                    <div class="invoice-number">
                                        <h4 class="inv-title-1">Tanggal Invoice:</h4>
                                        <p class="invo-addr-1">
                                            {{ $order->order_date }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-sm-6 text-end mb-50">
                                    <h4 class="inv-title-1">Toko Lily</h4>
                                    <p class="inv-from-1">Jl. Griya Permata raya 1 No.54, Handil Bakti, Kec. Alalak,</p>
                                    <p class="inv-from-2">Kabupaten Barito Kuala, Kalimantan Selatan 70582</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 mb-50">
                                    <h4 class="inv-title-1">Pelanggan</h4>
                                    <p class="inv-from-1">{{ $order->customer ? $order->customer->name : 'Pelanggan Umum' }}</p>
                                    <p class="inv-from-1">{{ $order->customer ? $order->customer->email : '-' }}</p>
                                    <p class="inv-from-1">{{ $order->customer ? $order->customer->phone : '-' }}</p>
                                    <p class="inv-from-2">{{ $order->customer ? $order->customer->address : '-' }}</p>
                                </div>
                                <div class="col-sm-6 text-end mb-50">
                                    <h4 class="inv-title-1">Detail</h4>
                                    <p class="inv-from-1">Status Pembayaran: {{ $order->payment_status }}</p>
                                    <p class="inv-from-1">Total: Rp {{ number_format($order->pay, 0, ',', '.') }}</p>
                                    <p class="inv-from-1">Hutang: Rp {{ number_format($order->due, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="order-summary">
                            <div class="table-outer">
                                <table class="default-table invoice-table">
                                    <thead>
                                    <tr>
                                        <th>Deskripsi</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Total (+Pajak)</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($orderDetails as $item)
                                        <tr>
                                            <td>{{ $item->product->product_name }}</td>
                                            <td>Rp {{ number_format($item->unitcost, 0, ',', '.') }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                        <tr>
                                            <td><strong class="text-danger">Total</strong></td>
                                            <td></td>
                                            <td></td>
                                            <td><strong class="text-danger">Rp {{ number_format($order->total, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        {{-- <div class="invoice-informeshon-footer">
                            <ul>
                                <li><a href="https://themeforest.net/user/themevessel/portfolio">www.themevessel.com</a></li>
                                <li><a href="mailto:sales@hotelempire.com">info@themevessel.com</a></li>
                                <li><a href="tel:+088-01737-133959">+088 01737 133959</a></li>
                            </ul>
                        </div> --}}
                    </div>

                    <div class="invoice-btn-section clearfix d-print-none">
                        <a href="javascript:window.print()" class="btn btn-lg btn-print">
                            Cetak Invoice
                        </a>
                        <a id="invoice_download_btn" class="btn btn-lg btn-download">
                            Unduh Invoice
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="{{ asset('assets/invoice/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/invoice/js/jspdf.min.js') }}"></script>
    <script src="{{ asset('assets/invoice/js/html2canvas.js') }}"></script>
    <script src="{{ asset('assets/invoice/js/app.js') }}"></script>
</body>
</html>

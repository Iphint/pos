@extends('dashboard.body.main')

@section('specificpagescripts')
<!-- QuaggaJS for Barcode Scanning -->
<script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2@1.8.4/dist/quagga.min.js"></script>
<!-- Select2 for Searchable Dropdown -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Custom POS Scripts -->
<link rel="stylesheet" href="{{ asset('assets/css/pos-scanner.css') }}">
<script src="{{ asset('assets/js/pos-live-search.js') }}" defer></script>
<script src="{{ asset('assets/js/pos-barcode-scanner.js') }}" defer></script>

<style>
/* Custom Select2 Styling */
.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px;
    padding-left: 12px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

.select2-dropdown {
    border: 1px solid #ced4da;
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #007bff;
}

.select2-container {
    width: 100% !important;
}
</style>

<script>
$(document).ready(function() {
    $('#customer_id').select2({
        placeholder: '-- Pilih atau Cari Pelanggan --',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return "Pelanggan tidak ditemukan";
            },
            searching: function() {
                return "Mencari...";
            }
        }
    });
});
</script>
@endsection

@section('container')
<div class="container-fluid">

    <div class="row">
        <div class="col-lg-12">
            @if (session()->has('success'))
                <div class="alert text-white bg-success" role="alert">
                    <div class="iq-alert-text">{{ session('success') }}</div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <i class="ri-close-line"></i>
                    </button>
                </div>
            @endif
            <div>
                <h4 class="mb-3">Point of Sale</h4>
            </div>
        </div>

        <div class="col-lg-6 col-md-12 mb-3">
            <table class="table">
                <thead>
                    <tr class="ligth">
                        <th scope="col">Nama</th>
                        <th scope="col">Jumlah</th>
                        <th scope="col">Harga</th>
                        <th scope="col">Subtotal</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productItem as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td style="min-width: 140px;">
                            <form action="{{ route('pos.updateCart', $item->rowId) }}" method="POST">
                                @csrf
                                <div class="input-group">
                                    <input type="number" class="form-control" name="qty" required value="{{ old('qty', $item->qty) }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-success border-none" data-toggle="tooltip" data-placement="top" title="Perbarui"><i class="fas fa-check"></i></button>
                                    </div>
                                </div>
                            </form>
                        </td>
                        <td>Rp {{ $item->price(0, ',', '.') }}</td>
                        <td>Rp {{ $item->subtotal(0, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('pos.deleteCart', $item->rowId) }}" class="btn btn-danger border-none" data-toggle="tooltip" data-placement="top" title="Hapus"><i class="fa-solid fa-trash mr-0"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="container row text-center">
                <div class="form-group col-sm-6">
                    <p class="h4 text-primary">Jumlah: {{ Cart::count() }}</p>
                </div>
                <div class="form-group col-sm-6"> 
                    <p class="h4 text-primary">Subtotal: Rp {{ Cart::subtotal() }}</p>
                </div>
                <div class="form-group col-sm-6">
                    <p class="h4 text-primary">Total: Rp {{ Cart::total() }}</p>
                </div>
            </div>

            <form action="{{ route('pos.createInvoice') }}" method="POST">
                @csrf
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label for="customer_id" class="form-label">
                            <i class="fa-solid fa-user mr-1"></i>Pilih Pelanggan
                        </label>
                        <div class="input-group">
                            <select class="form-control" id="customer_id" name="customer_id">
                                <option value="">-- Pelanggan Umum --</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" data-email="{{ $customer->email ?? '' }}" data-phone="{{ $customer->phone ?? '' }}">
                                        {{ $customer->name }}
                                        @if($customer->email || $customer->phone)
                                            - ({{ $customer->email ?? $customer->phone }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('customer_id')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                        @enderror
                        <small class="form-text text-muted">
                            <i class="fa-solid fa-info-circle"></i> Ketik untuk mencari pelanggan berdasarkan nama, email, atau telepon
                        </small>
                    </div>
                    
                    <!-- Customer Info Display (Optional) -->
                    <div class="col-md-12 mt-3" id="customer-info-display" style="display: none;">
                        <div class="alert alert-info">
                            <strong><i class="fa-solid fa-user-check"></i> Pelanggan Terpilih:</strong>
                            <div id="selected-customer-details"></div>
                        </div>
                    </div>

                    <div class="col-md-12 mt-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-center">
                            <a href="{{ route('customers.create') }}" class="btn btn-primary add-list mx-1">
                                <i class="fa-solid fa-user-plus mr-1"></i>Tambah Pelanggan
                            </a>
                            <button type="submit" class="btn btn-success add-list mx-1">
                                <i class="fa-solid fa-file-invoice mr-1"></i>Buat Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-6 col-md-12">
            <div class="card card-block card-stretch card-height">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                        <div class="form-group row mb-0">
                            <label for="row" class="align-self-center mx-2">Baris:</label>
                            <div>
                                <select class="form-control" name="row" id="row-select">
                                    <option value="10" @if(request('row') == '10')selected="selected"@endif>10</option>
                                    <option value="25" @if(request('row') == '25')selected="selected"@endif>25</option>
                                    <option value="50" @if(request('row') == '50')selected="selected"@endif>50</option>
                                    <option value="100" @if(request('row') == '100')selected="selected"@endif>100</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row mb-0 flex-grow-1 mx-2">
                            <label class="control-label col-sm-3 align-self-center" for="search">Cari:</label>
                            <div class="input-group col-sm-9">
                                <input type="text" id="live-search-input" class="form-control" placeholder="Cari produk atau barcode..." autocomplete="off">
                                <div class="input-group-append">
                                    <button type="button" id="open-scanner-btn" class="btn btn-success" title="Pindai Barcode">
                                        <i class="fa-solid fa-camera"></i> Pindai
                                    </button>
                                    <button type="button" id="clear-search-btn" class="btn btn-danger" title="Hapus Pencarian">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading Indicator -->
                    <div id="search-loading" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Mencari...</span>
                        </div>
                    </div>

                    <div class="table-responsive rounded mb-3 border-none">
                        <table class="table mb-0">
                            <thead class="bg-white text-uppercase">
                                <tr class="ligth ligth-data">
                                    <th>No.</th>
                                    <th>@sortablelink('product_name', 'Nama')</th>
                                    <th>@sortablelink('selling_price', 'Harga')</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="ligth-body" id="search-results-container">
                                @forelse ($products as $product)
                                <tr>
                                    <td>{{ (($products->currentPage() * 10) - 10) + $loop->iteration  }}</td>
                                    <td>{{ $product->product_name }}</td>
                                    <td>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                                    <td>
                                        <form action="{{ route('pos.addCart') }}" method="POST"  style="margin-bottom: 5px">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $product->id }}">
                                            <input type="hidden" name="name" value="{{ $product->product_name }}">
                                            <input type="hidden" name="price" value="{{ $product->selling_price }}">

                                            <button type="submit" class="btn btn-primary border-none" data-toggle="tooltip" data-placement="top" title="Tambah"><i class="far fa-plus mr-0"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="alert text-white bg-danger" role="alert">
                                            <div class="iq-alert-text">Data tidak ditemukan.</div>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Barcode Scanner Modal -->
<div class="modal fade" id="barcode-scanner-modal" tabindex="-1" role="dialog" aria-labelledby="scannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scannerModalLabel">
                    <i class="fa-solid fa-camera mr-2"></i>Pindai Barcode Produk
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Scanner Viewport -->
                <div id="scanner-viewport">
                    <div class="barcode-guide"></div>
                </div>

                <!-- Scanner Status -->
                <div id="scanner-status" class="alert" style="display: none;"></div>

                <!-- Product Info -->
                <div id="product-info"></div>

                <!-- Manual Barcode Input -->
                <div class="manual-barcode-section">
                    <h6>Atau masukkan barcode secara manual:</h6>
                    <div class="input-group">
                        <input type="text" id="manual-barcode-input" class="form-control" placeholder="Masukkan kode barcode...">
                        <div class="input-group-append">
                            <button type="button" id="manual-barcode-btn" class="btn btn-primary">
                                <i class="fa-solid fa-check"></i> Tambah
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="close-scanner-btn" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
// Additional script for customer info display
$(document).ready(function() {
    $('#customer_id').on('select2:select', function (e) {
        var data = e.params.data;
        var selectedOption = $(data.element);
        
        if (data.id) {
            var customerName = data.text;
            var customerEmail = selectedOption.data('email');
            var customerPhone = selectedOption.data('phone');
            
            var detailsHtml = '<div class="mt-2">';
            detailsHtml += '<p class="mb-1"><strong>Nama:</strong> ' + customerName + '</p>';
            if (customerEmail) {
                detailsHtml += '<p class="mb-1"><strong>Email:</strong> ' + customerEmail + '</p>';
            }
            if (customerPhone) {
                detailsHtml += '<p class="mb-0"><strong>Telepon:</strong> ' + customerPhone + '</p>';
            }
            detailsHtml += '</div>';
            
            $('#selected-customer-details').html(detailsHtml);
            $('#customer-info-display').fadeIn();
        } else {
            $('#customer-info-display').fadeOut();
        }
    });
    
    $('#customer_id').on('select2:clear', function (e) {
        $('#customer-info-display').fadeOut();
    });
});
</script>
@endsection
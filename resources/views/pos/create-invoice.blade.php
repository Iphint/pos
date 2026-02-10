@extends('dashboard.body.main')

@section('container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block">
                    <div class="card-header d-flex justify-content-between bg-primary">
                        <div class="iq-header-title">
                            <h4 class="card-title mb-0">Invoice</h4>
                        </div>

                        <div class="invoice-btn d-flex">
                            <form action="{{ route('pos.printInvoice') }}" method="post">
                                @csrf
                                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                                <button type="submit" class="btn btn-primary-dark mr-2"><i class="las la-print"></i>
                                    Print</button>
                            </form>

                            <button type="button" class="btn btn-primary-dark mr-2" data-toggle="modal"
                                data-target=".bd-example-modal-lg">Create</button>

                            <div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-white">
                                            <h3 class="modal-title text-center mx-auto">Invoice of
                                                {{ $customer->name }}<br />Total Amount Rp.{{ Cart::total() }}</h3>
                                        </div>
                                        <form action="{{ route('pos.storeOrder') }}" method="post">
                                            @csrf
                                            <div class="modal-body">
                                                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="payment_status">Payment</label>
                                                        <select
                                                            class="form-control @error('payment_status') is-invalid @enderror"
                                                            name="payment_status">
                                                            <option selected="" disabled="">-- Select Payment --
                                                            </option>
                                                            <option value="HandCash">HandCash</option>
                                                            <option value="Transfer">Transfer</option>
                                                            <option value="Qris">Qris</option>
                                                        </select>
                                                        @error('payment_status')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="pay">Pay Now</label>
                                                        <input type="text"
                                                            class="form-control @error('pay') is-invalid @enderror"
                                                            id="pay" name="pay" value="{{ old('pay') }}">
                                                        @error('pay')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body d-flex justify-content-center">
                        <div style="width: 320px; font-family: monospace; font-size: 13px">

                            <div class="text-center">
                                <strong>TOKO LILY</strong><br>
                                Jl. Griya Permata Raya 1 No.54<br>
                                Handil Bakti, Kalsel
                                <hr>
                            </div>

                            <div>
                                Tanggal : {{ now()->format('d/m/Y H:i') }}<br>
                                Kasir : {{ auth()->user()->name }}<br>
                                Customer: {{ $customer->name }}
                            </div>

                            <hr>

                            @foreach ($content as $item)
                                <div class="d-flex justify-content-between">
                                    <div>
                                        {{ $item->name }}<br>
                                        {{ $item->qty }} x {{ number_format($item->price) }}
                                    </div>
                                    <div>
                                        Rp.{{ number_format($item->subtotal) }}
                                    </div>
                                </div>
                            @endforeach

                            <hr>

                            <div class="d-flex justify-content-between">
                                <strong>Total</strong>
                                <strong>Rp {{ Cart::total() }}</strong>
                            </div>

                            <hr>

                            <div class="text-center">
                                Terima kasih 🙏<br>
                                Barang yang sudah dibeli<br>
                                tidak dapat dikembalikan
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

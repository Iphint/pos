@extends('dashboard.body.main')

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
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-3">Riwayat Pembayaran Gaji</h4>
                    <p class="mb-0">Dashboard riwayat pembayaran gaji memungkinkan Anda mengumpulkan dan memvisualisasikan data riwayat pembayaran gaji dengan mudah untuk mengoptimalkan <br>
                        pengalaman pembayaran gaji, memastikan retensi pembayaran gaji. </p>
                </div>
                <div>
                <a href="{{ route('advance-salary.index') }}" class="btn btn-danger add-list"><i class="fa-solid fa-trash mr-3"></i>Hapus Pencarian</a>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <form action="{{ route('advance-salary.index') }}" method="get">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="form-group row">
                        <label for="row" class="col-sm-3 align-self-center">Baris:</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="row">
                                <option value="10" @if(request('row') == '10')selected="selected"@endif>10</option>
                                <option value="25" @if(request('row') == '25')selected="selected"@endif>25</option>
                                <option value="50" @if(request('row') == '50')selected="selected"@endif>50</option>
                                <option value="100" @if(request('row') == '100')selected="selected"@endif>100</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-sm-3 align-self-center" for="search">Cari:</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input type="text" id="search" class="form-control" name="search" placeholder="Cari karyawan" value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button type="submit" class="input-group-text bg-primary"><i class="fa-solid fa-magnifying-glass font-size-20"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-12">
            <div class="table-responsive rounded mb-3">
                <table class="table mb-0">
                    <thead class="bg-white text-uppercase">
                        <tr class="ligth ligth-data">
                            <th>No.</th>
                            <th>Foto</th>
                            <th>@sortablelink('employee.name', 'Nama')</th>
                            <th>@sortablelink('date', 'Tanggal')</th>
                            <th>@sortablelink('paid_amount', 'Jumlah Dibayar')</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="ligth-body">
                        @forelse ($paySalaries as $paySalary)
                        <tr>
                            <td>{{ (($paySalaries->currentPage() * 10) - 10) + $loop->iteration  }}</td>
                            <td>
                                <img class="avatar-60 rounded" src="{{ $paySalary->employee->photo ? asset('storage/employees/'.$paySalary->employee->photo) : asset('assets/images/user/1.png') }}">
                            </td>
                            <td>{{ $paySalary->employee->name }}</td>
                            <td>{{ Carbon\Carbon::parse($paySalary->date)->format('M/Y') }}</td>
                            <td>Rp {{ number_format($paySalary->paid_amount, 0, ',', '.') }}</td>
                            <td>
                                <span class="btn btn-success text-white mr-2">Lunas</span>
                            </td>
                            <td>
                                <form action="{{ route('pay-salary.destroy', $paySalary->id) }}" method="POST">
                                    @method('delete')
                                    @csrf
                                    <div class="d-flex align-items-center list-action">
                                        <a class="btn btn-info mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Riwayat" href="{{ route('pay-salary.payHistoryDetail', $paySalary->id) }}">
                                            <i class="ri-eye-line mr-0"></i> Riwayat
                                        </a>
                                        <button type="submit" class="btn btn-warning mr-2 border-none" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')" data-toggle="tooltip" data-placement="top" title="" data-original-title="Hapus">
                                            <i class="ri-delete-bin-line mr-0"></i> Hapus
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>

                        @empty
                        <div class="alert text-white bg-danger" role="alert">
                            <div class="iq-alert-text">Data tidak ditemukan.</div>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <i class="ri-close-line"></i>
                            </button>
                        </div>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $paySalaries->links() }}
        </div>
    </div>
    <!-- Page end  -->
</div>

@endsection

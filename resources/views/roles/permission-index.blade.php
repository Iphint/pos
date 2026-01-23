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
                    <h4 class="mb-3">Daftar Izin</h4>
                    <p class="mb-0">Dashboard izin memungkinkan Anda mengumpulkan dan memvisualisasikan data izin dengan mudah untuk mengoptimalkan <br>
                        pengalaman izin, memastikan retensi izin. </p>
                </div>
                <div>
                    <a href="{{ route('permission.create') }}" class="btn btn-primary add-list"><i class="fa-solid fa-plus mr-3"></i>Tambah Izin</a>
                </div>
            </div>
        </div>

        {{-- <div class="col-lg-12">
            <form action="{{ route('customers.index') }}" method="get">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="form-group row">
                        <label for="row" class="col-sm-3 align-self-center">Row:</label>
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
                        <label class="control-label col-sm-3 align-self-center" for="search">Search:</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input type="text" id="search" class="form-control" name="search" placeholder="Search customer" value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button type="submit" class="input-group-text bg-primary"><i class="fa-solid fa-magnifying-glass font-size-20"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div> --}}

        <div class="col-lg-12">
            <div class="table-responsive rounded mb-3">
                <table class="table mb-0">
                    <thead class="bg-white text-uppercase">
                        <tr class="ligth ligth-data">
                            <th>No.</th>
                            <th>Nama Izin</th>
                            <th>Nama Grup</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="ligth-body">
                        @foreach ($permissions as $permission)
                        <tr>
                            <td>{{ (($permissions->currentPage() * 10) - 10) + $loop->iteration  }}</td>
                            <td>{{ $permission->name }}</td>
                            <td>{{ $permission->group_name }}</td>
                            <td>
                                <form action="{{ route('permission.destroy', $permission->id) }}" method="POST" style="margin-bottom: 5px">
                                    @method('delete')
                                    @csrf
                                    <div class="d-flex align-items-center list-action">
                                        <a class="btn btn-success mr-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Ubah"
                                            href="{{ route('permission.edit', $permission->id) }}""><i class="ri-pencil-line mr-0"></i> Ubah
                                        </a>
                                        <button type="submit" class="btn btn-warning mr-2 border-none" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')" data-toggle="tooltip" data-placement="top" title="" data-original-title="Hapus"><i class="ri-delete-bin-line mr-0"></i> Hapus</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $permissions->links() }}
        </div>
    </div>
    <!-- Page end  -->
</div>

@endsection

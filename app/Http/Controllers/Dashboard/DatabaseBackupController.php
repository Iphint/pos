<?php

namespace App\Http\Controllers\Dashboard;

use File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class DatabaseBackupController extends Controller
{
    public function index()
    {
        $path = storage_path('app/POS');

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        return view('database.index', [
            'files' => File::files($path)
        ]);
    }

    /**
     * Run database backup
     */
    public function create()
    {
        try {
            Artisan::call('backup:run', [
                '--only-db' => true
            ]);

            return Redirect::route('backup.index')
                ->with('success', 'Database backup berhasil!');
        } catch (\Exception $e) {
            return Redirect::route('backup.index')
                ->with('error', 'Backup gagal: ' . $e->getMessage());
        }
    }

    /**
     * Download backup file
     */
    public function download(string $fileName)
    {
        $path = storage_path('app/POS/' . $fileName);

        if (!File::exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

    /**
     * Delete backup file
     */
    public function delete(string $fileName)
    {
        Storage::delete('POS/' . $fileName);

        return Redirect::route('backup.index')
            ->with('success', 'Backup berhasil dihapus!');
    }
}

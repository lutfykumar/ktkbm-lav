<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class PanduanController extends Controller
{
    public function index(Request $request)
    {
        ActivityLogService::log('Mengakses Panduan Pengguna', 'view', 'panduan');

        return view('panduan', [
            'title' => 'Panduan Pengguna',
            'title_header' => 'Panduan Pengguna',
            'title_parent' => 'Dokumentasi',
            'side_active' => 'panduan',
        ]);
    }
}

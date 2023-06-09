<?php

namespace App\Http\Controllers;

use App\Models\Kos;
use App\Models\Pemilik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\View;


class KosController extends Controller
{
    public function index()
    {
        $title = 'Halaman List Kos';
        $kos = Kos::all();
        return view(view: 'kos/listKos', data: compact('kos', 'title'));
    }

    public function create()
    {
        $pemilik = Pemilik::select('id', 'nama')->get();
        return view('kos/addKos', ['pemilik' => $pemilik]);
    }
    public function store(Request $request)
    {

        $request->validate([
            'nama' => 'required|max:30',
            'alamat' => 'required|max:50',
            'no_telp' => 'required|max:15',
        ], [
            'nama.required' => 'Nama Wajib Diisi',
            'nama.max' => 'Nama Maksimal 30 Karakter',
            'alamat.required' => 'Alamat Wajib Diisi',
            'alamat.max' => 'Alamat Maksimal 50 Karakter',
            'no_telp.required' => 'No Telp Wajib Diisi',
            'no_telp.max' => 'No Telp Maksimal 15 Karakter',
        ]);

        $kos = new Kos;
        $kos->nama = $request->nama;
        $kos->alamat = $request->alamat;
        $kos->no_telp = $request->no_telp;
        $kos->pemilik_id = $request->pemilik_id;
        $kos->save();

        if ($kos) {
            Session::flash('insert', 'suskes');
            Session::flash('pesan', 'Data Berhasil Ditambahkan');
        }

        return redirect('/kos');
    }

    public function edit($id)
    {
        $kos = Kos::with('pemilik')->findOrFail($id);
        $pemilik = Pemilik::all();
        return view('kos/editKos', ['kos' => $kos], ['pemilik' => $pemilik]);
    }

    public function update(Request $request, $id)
    {
        $kos = Kos::findOrFail($id);
        $kos->nama = $request->nama;
        $kos->alamat = $request->alamat;
        $kos->no_telp = $request->no_telp;
        $kos->pemilik_id = $request->pemilik_id;

        $kos->save();

        if ($kos) {
            Session::flash('update', 'suskes');
            Session::flash('pesan', 'Data ' . $kos->nama . ' berhasil Diedit');
        }
        return redirect('/kos');
    }
    public function destroy($id)
    {
        try {
            $kos = Kos::findOrFail($id);
            $kos->delete();

            if ($kos) {
                Session::flash('delete', 'suskes');
                Session::flash('pesan', 'Data ' . $kos->nama . ' berhasil dihapus');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $error = $e->errorInfo[1];
            if ($error == 1451) {
                Session::flash('delete', 'gagal');
                Session::flash('pesan', 'Data ' . $kos->nama . ' tidak bisa dihapus karena terdapat data kamar di dalamnya ');
            }
        }

        return redirect('/kos');
    }

    public function cari(Request $request)
    {
        $title = 'Halaman List Kos';
        $request->validate([
            'cari' => 'required',
        ], [
            'cari.required' => 'Kolom pencarian wajib diisi',
        ]);

        $cari = $request->cari;

        $kos = DB::table('koss')
            ->where('nama', 'like', "%" . $cari . "%")
            ->orWhere('alamat', 'like', "%" . $cari . "%")
            ->paginate(5);

        return view('kos/listKos', compact('kos', 'title', 'cari'));
    }
}

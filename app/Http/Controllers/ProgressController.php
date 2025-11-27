<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

Carbon::setlocale('id');

class ProgressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = \DB::table('kalkulasi_heads')->where('user_id', auth()->user()->id)->orderBy('id','desc')->get();
        $res = [];
        foreach ($data as $da) {
            $res[] = [
                'id' => $da->id,
                'nama' => $da->nama,
                'umur' => $da->age,
                'tgl' => Carbon::parse($da->tgl)->translatedFormat('l, d F Y')
            ];
        }
        return response()->json($res);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = \DB::table('kalkulasi_heads')->join('kalkulasi_details', 'kalkulasi_heads.id', '=', 'kalkulasi_details.kalkulasi_id')
            ->where('kalkulasi_heads.id', $id)
            ->where('kalkulasi_heads.user_id', auth()->user()->id)
            ->first();

        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

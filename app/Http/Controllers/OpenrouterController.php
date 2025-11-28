<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OpenrouterController extends Controller
{
    public function analisisGizi(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'age' => 'required',
            'gender' => 'required',
            'age' => 'required',
            'bb' => 'required',
            'tb' => 'required',
        ]);

        $umur = $request->age * 12;

        if($umur <= 59){
            $tbu = $request->tb / $request->age;
            $bbu = $request->bb / $request->age;
            $imt = $request->bb / ($request->tb * $request->tb);
        }else{
            $tbu = $request->tb / $request->age;
            $bbu = $request->bb;
            $imt = $request->bb / ($request->tb * $request->tb);
        }

        $prompt = "
            Anda adalah seorang ahli gizi WHO yang menghitung status gizi anak menggunakan Standar WHO 2006 (Growth Standard).

            Data anak:
            - Jenis kelamin: $request->gender
            - Umur: $request->age (dalam tahun atau bulan)
            - Berat badan: $request->bb kg
            - Tinggi badan: $request->tb cm

            Hasil perhitungan:
            - TB/Umur: $tbu
            - BB/Umur: $bbu
            - IMT: $imt
            
            Tugas Anda:
            1. Tentukan indikator yang paling relevan berdasarkan umur anak:
                - Jika umur < 60 bulan: gunakan TB/U, BB/U, atau BB/TB sesuai tujuan.
                - Jika umur ≥ 60 bulan: gunakan IMT/U.
            2. Gunakan nilai rasio yang sudah dihitung di atas sebagai data dasar (tidak perlu menghitung ulang).
            3. Tentukan Z-score (boleh estimasi, karena data referensi tidak diberikan).
            4. Tentukan status gizi:
                - Stunting: TB/U < -2 SD
                - Underweight: BB/U < -2 SD
                - Wasting: BB/TB < -2 SD
                - Severe (< -3 SD)
                - Normal: -2 s/d +2 SD
                - Obesitas (IMT/U > +2 SD)
            5. Buat penjelasan singkat (boleh pakai <b>, <i>, <ul>, <li> dan emoji).
            6. Buat rekomendasi singkat dalam HTML dengan bentuk paragraf tanpa bullet list.
            Format output HARUS JSON, tanpa backtick, tanpa teks tambahan: 
            { 
                \"z_score\": \"\",
                \"kategori\": \"\",
                \"status\": \"\",
                \"penjelasan\": \"\",
                \"rekomendasi\": \"\" 
            }
        ";

        $response = Http::withHeaders([
            "Authorization" => "Bearer ".env("OPENROUTER_API_KEY"),
            "HTTP-Referer" => url('/'),
        ])->post(env("OPENROUTER_BASE_URL"), [
            "model" => "openai/gpt-oss-20b:free",
            "messages" => [
                [
                    "role" => "user",
                    "content" => $prompt,
                ],
            ],
        ]);

        $content = $response->json()['choices'][0]['message']['content'];
        $content = str_replace(["```json", "```"],"",$content);
        $data = json_decode($content, true);

        $head = \DB::table('kalkulasi_heads')->insertGetId([
            'user_id' => auth()->user()->id,
            'tgl' => date('Y-m-d'),
            'nama' => $request->nama,
            'gender' => $request->gender,
            'age' => $request->age,
            'bb' => $request->bb,
            'tb' => $request->tb,
        ]);

        $detail = \DB::table('kalkulasi_details')->insert([
            'kalkulasi_id' => $head,
            'z_score' => $data['z_score'],
            'kategori' => $data['kategori'],
            'status' => $data['status'],
            'penjelasan' => $data['penjelasan'],
            'rekomendasi' => $data['rekomendasi'],
        ]);

        return response()->json($data);
    }

    public function rekomendasiGizi()
    {
        $data = \DB::table('kalkulasi_heads')
            ->join('kalkulasi_details', 'kalkulasi_heads.id', '=', 'kalkulasi_details.kalkulasi_id')
            ->where('kalkulasi_heads.user_id', auth()->user()->id)
            ->select('kalkulasi_details.rekomendasi','kalkulasi_details.z_score')
            ->orderBy('kalkulasi_heads.id', 'desc')
            ->first();

        return response()->json($data);
    }
}

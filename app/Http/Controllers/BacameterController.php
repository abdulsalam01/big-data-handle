<?php

namespace App\Http\Controllers;

use App\Models\Rayon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BacameterController extends Controller
{
    //
    public function getDetail($id, Request $request) {
        $take = $request->query('take') ?? 10;
        $skip = $request->query('skip') ?? 0;
        $rayon = Rayon::where('rayon_id', $id)->first();
        $total =
        DB::table("ms_pelanggan as a")
            ->selectRaw("COALESCE(COUNT(*), 0) as cnt")
            ->join("ms_jenispelanggan_tarif as b", "a.jenis_pelanggan_id", "b.jenispelanggan_id")
            ->where([
                ["cabang_id", "=", $rayon->cabang_id],
                ["wilayah_id", "=", $rayon->wilayah_id],
                ["blok_id", "=", $rayon->blok_id],
                ["status_id", 1]
            ])
            ->get()
            ->pluck("cnt")[0];

        $query =
        DB::query()
            ->select(DB::raw("a.*, b.jenispelanggan_name as sub_jenis_pelanggan,
                b.tarif1, b.tarif2, b.tarif3, b.biaya_pemeliharaan,
                b.biaya_administrasi, b.biaya_denda, p1.angkakini_1bulanlalu,
                p2.angkalalu_1bulanlalu, p2.angkalalu_1bulanlalu as angkakini_2bulanlalu,
                p3.angkalalu_2bulanlalu, p3.angkalalu_2bulanlalu as angkakini_3bulanlalu,
                p4.angkalalu_3bulanlalu"))
            ->from(DB::raw("ms_pelanggan a, ms_jenispelanggan_tarif b, (
                SELECT p.pelanggan_id, SUM(P.stand_angka) AS angkakini_1bulanlalu
                FROM tbl_bacameter P
                WHERE to_char(P.waktu_baca, 'YYYY-MM' :: TEXT) =
                    to_char (( now() - '1 mons' :: INTERVAL ), 'YYYY-MM' :: TEXT )
                GROUP BY p.pelanggan_id
                ORDER BY p.pelanggan_id asc) p1,
                (
                SELECT p.pelanggan_id, SUM(P.stand_angka) AS angkalalu_1bulanlalu
                FROM tbl_bacameter P
                WHERE to_char(P.waktu_baca, 'YYYY-MM' :: TEXT) =
                    to_char (( now() - '2 mons' :: INTERVAL ), 'YYYY-MM' :: TEXT )
                GROUP BY p.pelanggan_id
                ORDER BY p.pelanggan_id asc) p2,
                (
                SELECT p.pelanggan_id, SUM(P.stand_angka) AS angkalalu_2bulanlalu
                FROM tbl_bacameter P
                WHERE to_char(P.waktu_baca, 'YYYY-MM' :: TEXT) =
                    to_char (( now() - '3 mons' :: INTERVAL ), 'YYYY-MM' :: TEXT )
                GROUP BY p.pelanggan_id
                ORDER BY p.pelanggan_id asc) p3,
                (
                SELECT p.pelanggan_id, SUM(P.stand_angka) AS angkalalu_3bulanlalu
                FROM tbl_bacameter P
                WHERE to_char(P.waktu_baca, 'YYYY-MM' :: TEXT) =
                    to_char (( now() - '4 mons' :: INTERVAL ), 'YYYY-MM' :: TEXT )
                GROUP BY p.pelanggan_id
                ORDER BY p.pelanggan_id asc) p4"))
            ->where([
                ["a.id", "=", DB::raw("p1.pelanggan_id::INTEGER")],
                ["a.id", "=", DB::raw("p2.pelanggan_id::INTEGER")],
                ["a.id", '=', DB::raw("p3.pelanggan_id::INTEGER")],
                ["a.id", '=', DB::raw("p4.pelanggan_id::INTEGER")],
                ["a.jenis_pelanggan_id", "=", DB::raw("b.jenispelanggan_id::INTEGER")],
                ["cabang_id", "=", $rayon->cabang_id],
                ["wilayah_id", "=", $rayon->wilayah_id],
                ["blok_id", "=", $rayon->blok_id],
                ["status_id", 1]
            ])
            ->orderBy('a.id')
            ->take($take)
            ->skip($skip)
            ->get();
            // ->chunk(50, function($data) use(&$query) {
            //     foreach ($data as $key => $value) {
            //         array_push($query, $value);
            //     }
            // });

        return response()->json([
            'data' => $query,
            'take' => $take,
            'skip' => $skip,
            'total' => $total
        ], 200);
    }
}

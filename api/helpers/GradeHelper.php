<?php
class GradeHelper {
    public static function calculate(?float $absen, ?float $tugas, ?float $uts, ?float $uas): array {
        if ($absen === null || $tugas === null || $uts === null || $uas === null) {
            return ['akhir' => null, 'huruf' => null, 'bobot' => null];
        }

        $akhir = ($absen * 0.1) + ($tugas * 0.2) + ($uts * 0.3) + ($uas * 0.4);

        if ($akhir >= 85)      { $huruf = 'A'; $bobot = 4.0; }
        elseif ($akhir >= 70)  { $huruf = 'B'; $bobot = 3.0; }
        elseif ($akhir >= 55)  { $huruf = 'C'; $bobot = 2.0; }
        elseif ($akhir >= 40)  { $huruf = 'D'; $bobot = 1.0; }
        else                   { $huruf = 'E'; $bobot = 0.0; }

        return [
            'akhir' => round($akhir, 2),
            'huruf' => $huruf,
            'bobot' => $bobot,
        ];
    }
}

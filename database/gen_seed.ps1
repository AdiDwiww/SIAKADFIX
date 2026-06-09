# Generate KRS + Nilai SQL
$A = "100,88,85,90,89.10,'A',4.00,1"
$B = "85,78,75,78,77.80,'B',3.00,1"
$C = "70,65,60,65,64.00,'C',2.00,1"

# Grade patterns per student (7 MK per sem, pattern repeats each sem)
$patterns = @{
  1  = @($A,$B,$A,$B,$A,$B,$A)  # Andi
  2  = @($B,$B,$B,$B,$B,$B,$B)  # Budi
  3  = @($A,$A,$A,$A,$A,$A,$A)  # Citra
  4  = @($B,$C,$B,$C,$B,$C,$B)  # Dedi
  5  = @($A,$B,$A,$B,$A,$B,$A)  # Eka
  6  = @($B,$A,$B,$A,$B,$A,$B)  # Fajar
  7  = @($A,$A,$A,$A,$A,$A,$A)  # Gita
  8  = @($B,$B,$B,$B,$B,$B,$B)  # Hendra
  9  = @($A,$B,$A,$B,$A,$B,$A)  # Indah
  10 = @($B,$C,$B,$C,$B,$C,$B)  # Joko
  11 = @($A,$A,$A,$A,$A,$A,$A)  # Kartika
  12 = @($B,$B,$B,$B,$B,$B,$B)  # Luki
  13 = @($A,$B,$A,$B,$A,$B,$A)  # Maya
  14 = @($B,$C,$B,$C,$B,$C,$B)  # Nanda
  15 = @($A,$B,$A,$B,$A,$B,$A)  # Olivia
}

$krsRows = @()
$nilaiRows = @()
$krsId = 1

# 2021 batch (9 students), 4 semesters x 7 MK
$sem1_k = 1..7; $sem2_k = 8..14; $sem3_k = 15..21; $sem4_k = 22..28
foreach ($m in @(1,2,3,4,5,11,12,13,14)) {
  $semConfig = @(
    @{k=$sem1_k; ta='2021/2022'; sa='GANJIL'},
    @{k=$sem2_k; ta='2021/2022'; sa='GENAP'},
    @{k=$sem3_k; ta='2022/2023'; sa='GANJIL'},
    @{k=$sem4_k; ta='2022/2023'; sa='GENAP'}
  )
  foreach ($sc in $semConfig) {
    $ki = 0
    foreach ($k in $sc.k) {
      $krsRows += "($m,$k,'$($sc.ta)','$($sc.sa)','DISETUJUI')"
      $grade = $patterns[$m][$ki]
      $nilaiRows += "($krsId,$grade)"
      $krsId++; $ki++
    }
  }
}

# 2022 batch (6 students), 2 semesters x 7 MK
foreach ($m in @(6,7,8,9,10,15)) {
  $semConfig = @(
    @{k=$sem1_k; ta='2022/2023'; sa='GANJIL'},
    @{k=$sem2_k; ta='2022/2023'; sa='GENAP'}
  )
  foreach ($sc in $semConfig) {
    $ki = 0
    foreach ($k in $sc.k) {
      $krsRows += "($m,$k,'$($sc.ta)','$($sc.sa)','DISETUJUI')"
      $grade = $patterns[$m][$ki]
      $nilaiRows += "($krsId,$grade)"
      $krsId++; $ki++
    }
  }
}

$out = "USE uas_psi;`n"
$out += "INSERT INTO krs(mahasiswa_id,kuliah_id,tahun_akademik,semester_akademik,status)VALUES`n"
$out += ($krsRows -join ",`n") + ";`n`n"
$out += "INSERT INTO nilai(krs_id,absen,tugas,uts,uas,nilai_akhir,nilai_huruf,bobot,published)VALUES`n"
$out += ($nilaiRows -join ",`n") + ";"

$out | Out-File -FilePath 'c:\xampp\htdocs\SIAKADFIX\database\seed_part2.sql' -Encoding UTF8
Write-Host "KRS: $($krsRows.Count), Nilai: $($nilaiRows.Count)"

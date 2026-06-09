$rows = @()
$batch2021 = @(1,2,3,4,5,11,12,13,14)
foreach ($m in $batch2021) {
  foreach ($k in 1..7)  { $rows += "($m,$k,'2021/2022','GANJIL','DISETUJUI')" }
  foreach ($k in 8..14) { $rows += "($m,$k,'2021/2022','GENAP','DISETUJUI')" }
  foreach ($k in 15..21){ $rows += "($m,$k,'2022/2023','GANJIL','DISETUJUI')" }
  foreach ($k in 22..28){ $rows += "($m,$k,'2022/2023','GENAP','DISETUJUI')" }
}
$batch2022 = @(6,7,8,9,10,15)
foreach ($m in $batch2022) {
  foreach ($k in 1..7)  { $rows += "($m,$k,'2022/2023','GANJIL','DISETUJUI')" }
  foreach ($k in 8..14) { $rows += "($m,$k,'2022/2023','GENAP','DISETUJUI')" }
}
$sql = "USE uas_psi;`nINSERT INTO krs(mahasiswa_id,kuliah_id,tahun_akademik,semester_akademik,status)VALUES`n"
$sql += ($rows -join ",`n") + ";"
$sql | Out-File -FilePath 'c:\xampp\htdocs\SIAKADFIX\database\krs_insert.sql' -Encoding UTF8
Write-Host "Total KRS rows: $($rows.Count)"

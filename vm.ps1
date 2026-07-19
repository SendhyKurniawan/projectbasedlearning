# Nyalakan / matikan VM produksi PJBL di GCP.
# Pemakaian (dari PowerShell):
#   .\vm.ps1 on       # nyalakan VM
#   .\vm.ps1 off      # matikan VM
#   .\vm.ps1 status   # cek status + IP
#
# gcloud sudah terkonfigurasi (project pjbl-app-btgs6, zone asia-southeast2-a).

param(
  [Parameter(Position = 0)]
  [string]$Action = "status"
)

$VM      = "pjbl-vm"
$PROJECT = "pjbl-app-btgs6"
$ZONE    = "asia-southeast2-a"

function Show-Status {
  gcloud compute instances describe $VM `
    --project=$PROJECT --zone=$ZONE `
    --format="value(status, networkInterfaces[0].accessConfigs[0].natIP)"
}

switch ($Action.ToLower()) {
  { $_ -in "on", "start" } {
    Write-Host "Menyalakan $VM ..."
    gcloud compute instances start $VM --project=$PROJECT --zone=$ZONE
    Write-Host "Selesai. Status sekarang:"
    Show-Status
    Write-Host "URL: https://polimedia.pblworkspace.com (butuh ~1-2 menit sampai container siap)"
  }
  { $_ -in "off", "stop" } {
    Write-Host "Mematikan $VM ..."
    gcloud compute instances stop $VM --project=$PROJECT --zone=$ZONE
    Write-Host "Selesai. VM sudah mati (IP static 34.50.107.24 tetap aman)."
  }
  { $_ -in "status", "st" } {
    Write-Host "Status ${VM}:"
    Show-Status
  }
  default {
    Write-Host "Pemakaian: .\vm.ps1 {on|off|status}"
  }
}

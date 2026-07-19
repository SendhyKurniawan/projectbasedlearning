#!/usr/bin/env bash
# Nyalakan / matikan VM produksi PJBL di GCP.
# Pemakaian:
#   bash vm.sh on       # nyalakan VM
#   bash vm.sh off      # matikan VM
#   bash vm.sh status   # cek status + IP
#
# gcloud sudah terkonfigurasi (project pjbl-app-btgs6, zone asia-southeast2-a).

set -euo pipefail

VM="pjbl-vm"
PROJECT="pjbl-app-btgs6"
ZONE="asia-southeast2-a"

usage() {
  echo "Pemakaian: bash vm.sh {on|off|status}"
  exit 1
}

status() {
  gcloud compute instances describe "$VM" \
    --project="$PROJECT" --zone="$ZONE" \
    --format="value(status, networkInterfaces[0].accessConfigs[0].natIP)"
}

case "${1:-}" in
  on|start)
    echo "Menyalakan $VM ..."
    gcloud compute instances start "$VM" --project="$PROJECT" --zone="$ZONE"
    echo "Selesai. Status sekarang:"
    status
    echo "URL: https://polimedia.pblworkspace.com (butuh ~1-2 menit sampai container siap)"
    ;;
  off|stop)
    echo "Mematikan $VM ..."
    gcloud compute instances stop "$VM" --project="$PROJECT" --zone="$ZONE"
    echo "Selesai. VM sudah mati (IP static 34.50.107.24 tetap aman)."
    ;;
  status|st)
    echo "Status $VM:"
    status
    ;;
  *)
    usage
    ;;
esac

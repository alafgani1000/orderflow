# OrderFlow Product Roadmap

Dokumen ini adalah acuan kanonik pengerjaan produk berdasarkan analisis paid beta. Setiap fase harus diimplementasikan, diuji, dan dilaporkan sebelum fase berikutnya dimulai.

## Status

- `[x]` selesai dan teruji
- `[~]` sedang dikerjakan
- `[ ]` belum dikerjakan

## Fondasi yang sudah selesai

- [x] Core SaaS: pelanggan, pesanan, Kanban, pembayaran/refund, invoice/SPK, laporan, staf, dan tracking publik
- [x] Bahasa Indonesia dan Inggris pada alur aktif
- [x] Preferensi bahasa per akun dan template WhatsApp bilingual
- [x] Halaman legal bilingual dan rekam persetujuan pengguna baru
- [x] Konfigurasi rekening serta kontak billing melalui environment
- [x] Validasi metode dan bukti pembayaran manual

## Gerbang keamanan sebelum paid beta

- [x] Private storage untuk file desain dan bukti pembayaran
- [x] Download terotorisasi atau signed URL
- [x] Verifikasi email wajib untuk akun email/password
- [x] Konfigurasi produksi, backup, monitoring, dan smoke test staging

## Fase 1 — Aktivasi pengguna

- [x] Onboarding identitas toko, kontak, alamat, dan rekening usaha
- [x] Checklist aktivasi: profil toko, pelanggan pertama, dan pesanan pertama
- [x] Import pelanggan dari CSV/Excel
- [x] Import pesanan dari CSV/Excel dengan preview dan validasi
- [x] Data contoh toko yang dapat dibuat dan dihapus dengan aman

## Fase 2 — Penawaran menjadi pesanan

- [x] Modul penawaran harga
- [x] Item, jumlah, harga, masa berlaku, dan catatan penawaran
- [x] Tautan penawaran publik
- [x] Persetujuan atau penolakan pelanggan
- [x] Konversi penawaran yang disetujui menjadi pesanan

## Fase 3 — Kolaborasi pelanggan

- [ ] Upload referensi oleh pelanggan
- [ ] Approval atau revisi desain melalui tautan pelanggan
- [ ] Catatan revisi dan versi desain
- [ ] Bukti waktu persetujuan

## Fase 4 — Operasional produksi

- [ ] Penugasan PIC pada pesanan
- [ ] Checklist produksi dan quality control
- [ ] Riwayat aktivitas lengkap untuk harga, status, pembayaran, file, dan penugasan
- [ ] Tampilan beban kerja operator

## Fase 5 — Reminder dan tindak lanjut

- [ ] Pusat reminder deadline, keterlambatan, dan tagihan
- [ ] Reminder manual terjadwal
- [ ] Integrasi WhatsApp Cloud API untuk otomasi pada paket yang sesuai
- [ ] Riwayat pengiriman dan kegagalan notifikasi

## Fase 6 — Portabilitas dan perlindungan data

- [ ] Ekspor seluruh data toko
- [ ] Penghapusan atau anonimisasi akun
- [ ] Backup otomatis dan uji pemulihan
- [ ] Persetujuan ulang saat versi kebijakan berubah

## Fase 7 — Fleksibilitas workshop

- [ ] Status produksi yang dapat disesuaikan
- [ ] Template pesan per toko
- [ ] Custom field sederhana untuk pesanan
- [ ] Preset workflow per segmen usaha

## Fase 8 — Billing otomatis

- [ ] Payment gateway dan webhook
- [ ] QRIS dinamis
- [ ] Perpanjangan, masa tenggang, pembatalan, dan rekonsiliasi otomatis
- [ ] Invoice langganan PDF

## Fase 9 — Profitabilitas dan pertumbuhan

- [ ] Biaya bahan, tenaga kerja, dan biaya tambahan per pesanan
- [ ] Laba kotor dan margin
- [ ] Repeat order dan duplikasi pesanan
- [ ] PWA serta optimasi pengalaman mobile
- [ ] Validasi paket dan harga melalui pelanggan pilot

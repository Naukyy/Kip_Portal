# TODO

## Pemecahan bug pemindahan izin/sakit saat kelas asal gagal diakhiri
- [ ] Ubah `AttendanceController@moveStatus` agar menulis Attendance target hanya jika kelas asal berhasil di-end.
      (Opsi: buat state "draft"/penanda pemindahan yang bisa dihapus saat endClass gagal.)
- [ ] Tambahkan rollback saat `endClass()` gagal.
      Target: `moveStatus` yang terjadi pada meeting ini tidak boleh meninggalkan data “terkunci” di tanggal target.
- [ ] Implementasi praktis sesuai constraint database saat ini:
      - [ ] Tandai record hasil pemindahan dengan kolom tambahan/relasi (butuh migrasi) **atau**
      - [ ] gunakan transaksi + simpan data perubahan dalam transaction yang sama (jika memungkinkan).
- [ ] Setelah fix: validasi ulang flow:
      1) moveStatus sukses
      2) endClass gagal (pending>0)
      3) ulangi moveStatus ke target tanggal yang sama → harus bisa memilih/berubah lagi.


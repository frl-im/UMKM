const express = require("express");
const router = express.Router();

// Data dummy (simulasi database)
let produk = [
  { id: 1, nama: "Batik Tulis", harga: 150000 },
  { id: 2, nama: "Keripik Tempe", harga: 25000 }
];

// Ambil semua produk
router.get("/", (req, res) => {
  res.json(produk);
});

// Tambah produk baru
router.post("/", (req, res) => {
  const { nama, harga } = req.body;
  const id = produk.length + 1;
  const produkBaru = { id, nama, harga };
  produk.push(produkBaru);
  res.status(201).json(produkBaru);
});

// Hapus produk
router.delete("/:id", (req, res) => {
  const id = parseInt(req.params.id);
  produk = produk.filter(p => p.id !== id);
  res.json({ message: "Produk dihapus" });
});

module.exports = router;


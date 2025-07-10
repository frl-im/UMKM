const express = require("express");
const router = express.Router();
const fs = require("fs");
const path = require("path");

const dataPath = path.join(__dirname, "../data/umkm.json");

router.get("/", (req, res) => {
  const data = fs.readFileSync(dataPath);
  res.json(JSON.parse(data));
});

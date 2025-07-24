const express = require("express");
const cors = require("cors");
const app = express();
const port = 3000;
const umkmRoutes = require("./backend/Routes");

app.use(cors());
app.use(express.json());
app.use("/api/umkm", umkmRoutes);

app.listen(port, () => {
  console.log(`Server berjalan di http://localhost:${port}`);
});

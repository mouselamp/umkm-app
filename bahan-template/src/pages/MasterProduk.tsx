import { useState } from "react";
import { MainLayout } from "@/components/layout/MainLayout";
import { PageHeader } from "@/components/ui/page-header";
import { DataTable, Column } from "@/components/ui/data-table";
import { StatusBadge } from "@/components/ui/status-badge";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { Search, Pencil, Trash2, Package } from "lucide-react";

interface Produk {
  id: string;
  kode: string;
  nama: string;
  kategori: string;
  satuan: string;
  hargaBeli: number;
  hargaJual: number;
  stok: number;
  minStok: number;
  status: "aktif" | "nonaktif";
  deskripsi?: string;
}

const kategoriOptions = [
  "Makanan",
  "Minuman",
  "Snack",
  "Bahan Baku",
  "Kemasan",
];

const satuanOptions = ["Pcs", "Kg", "Gram", "Liter", "Box", "Pack", "Lusin"];

const initialProducts: Produk[] = [
  {
    id: "1",
    kode: "PRD001",
    nama: "Nasi Goreng Spesial",
    kategori: "Makanan",
    satuan: "Pcs",
    hargaBeli: 12000,
    hargaJual: 18000,
    stok: 50,
    minStok: 10,
    status: "aktif",
    deskripsi: "Nasi goreng dengan telur, ayam, dan sayuran",
  },
  {
    id: "2",
    kode: "PRD002",
    nama: "Mie Ayam Bakso",
    kategori: "Makanan",
    satuan: "Pcs",
    hargaBeli: 8000,
    hargaJual: 15000,
    stok: 30,
    minStok: 10,
    status: "aktif",
  },
  {
    id: "3",
    kode: "PRD003",
    nama: "Es Teh Manis",
    kategori: "Minuman",
    satuan: "Pcs",
    hargaBeli: 2000,
    hargaJual: 5000,
    stok: 100,
    minStok: 20,
    status: "aktif",
  },
  {
    id: "4",
    kode: "PRD004",
    nama: "Kerupuk Udang",
    kategori: "Snack",
    satuan: "Pack",
    hargaBeli: 5000,
    hargaJual: 8000,
    stok: 5,
    minStok: 10,
    status: "aktif",
  },
  {
    id: "5",
    kode: "PRD005",
    nama: "Ayam Goreng",
    kategori: "Makanan",
    satuan: "Pcs",
    hargaBeli: 10000,
    hargaJual: 16000,
    stok: 0,
    minStok: 5,
    status: "nonaktif",
  },
];

const emptyProduct: Omit<Produk, "id"> = {
  kode: "",
  nama: "",
  kategori: "",
  satuan: "",
  hargaBeli: 0,
  hargaJual: 0,
  stok: 0,
  minStok: 0,
  status: "aktif",
  deskripsi: "",
};

export default function MasterProduk() {
  const [products, setProducts] = useState<Produk[]>(initialProducts);
  const [searchQuery, setSearchQuery] = useState("");
  const [filterKategori, setFilterKategori] = useState<string>("semua");
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
  const [editingProduct, setEditingProduct] = useState<Produk | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<Produk | null>(null);
  const [formData, setFormData] = useState<Omit<Produk, "id">>(emptyProduct);
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 10;

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
    }).format(value);
  };

  const filteredProducts = products.filter((product) => {
    const matchesSearch =
      product.nama.toLowerCase().includes(searchQuery.toLowerCase()) ||
      product.kode.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesKategori =
      filterKategori === "semua" || product.kategori === filterKategori;
    return matchesSearch && matchesKategori;
  });

  const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
  const paginatedProducts = filteredProducts.slice(
    (currentPage - 1) * itemsPerPage,
    currentPage * itemsPerPage
  );

  const handleOpenAdd = () => {
    setEditingProduct(null);
    setFormData(emptyProduct);
    setIsDialogOpen(true);
  };

  const handleOpenEdit = (product: Produk) => {
    setEditingProduct(product);
    setFormData({
      kode: product.kode,
      nama: product.nama,
      kategori: product.kategori,
      satuan: product.satuan,
      hargaBeli: product.hargaBeli,
      hargaJual: product.hargaJual,
      stok: product.stok,
      minStok: product.minStok,
      status: product.status,
      deskripsi: product.deskripsi || "",
    });
    setIsDialogOpen(true);
  };

  const handleOpenDelete = (product: Produk) => {
    setDeleteTarget(product);
    setIsDeleteDialogOpen(true);
  };

  const handleSave = () => {
    if (editingProduct) {
      setProducts((prev) =>
        prev.map((p) =>
          p.id === editingProduct.id ? { ...formData, id: editingProduct.id } : p
        )
      );
    } else {
      const newId = (Math.max(...products.map((p) => parseInt(p.id))) + 1).toString();
      setProducts((prev) => [...prev, { ...formData, id: newId }]);
    }
    setIsDialogOpen(false);
    setFormData(emptyProduct);
  };

  const handleDelete = () => {
    if (deleteTarget) {
      setProducts((prev) => prev.filter((p) => p.id !== deleteTarget.id));
      setIsDeleteDialogOpen(false);
      setDeleteTarget(null);
    }
  };

  const columns: Column<Produk>[] = [
    { key: "kode", header: "Kode", className: "w-24" },
    {
      key: "nama",
      header: "Nama Produk",
      cell: (row) => (
        <div className="flex items-center gap-3">
          <div className="h-10 w-10 rounded-lg bg-primary/10 flex items-center justify-center">
            <Package className="h-5 w-5 text-primary" />
          </div>
          <div>
            <p className="font-medium">{row.nama}</p>
            <p className="text-xs text-muted-foreground">{row.kategori}</p>
          </div>
        </div>
      ),
    },
    { key: "satuan", header: "Satuan", className: "w-20" },
    {
      key: "hargaBeli",
      header: "Harga Beli",
      className: "text-right",
      cell: (row) => (
        <span className="text-right block">{formatCurrency(row.hargaBeli)}</span>
      ),
    },
    {
      key: "hargaJual",
      header: "Harga Jual",
      className: "text-right",
      cell: (row) => (
        <span className="text-right block font-medium text-primary">
          {formatCurrency(row.hargaJual)}
        </span>
      ),
    },
    {
      key: "stok",
      header: "Stok",
      className: "text-center w-20",
      cell: (row) => (
        <span
          className={`font-medium ${
            row.stok <= row.minStok ? "text-destructive" : ""
          }`}
        >
          {row.stok}
        </span>
      ),
    },
    {
      key: "status",
      header: "Status",
      className: "w-24",
      cell: (row) => (
        <StatusBadge
          variant={row.status === "aktif" ? "success" : "default"}
        >
          {row.status === "aktif" ? "Aktif" : "Nonaktif"}
        </StatusBadge>
      ),
    },
    {
      key: "actions",
      header: "Aksi",
      className: "w-24",
      cell: (row) => (
        <div className="flex items-center gap-1">
          <Button
            variant="ghost"
            size="icon"
            className="h-8 w-8"
            onClick={() => handleOpenEdit(row)}
          >
            <Pencil className="h-4 w-4" />
          </Button>
          <Button
            variant="ghost"
            size="icon"
            className="h-8 w-8 text-destructive hover:text-destructive"
            onClick={() => handleOpenDelete(row)}
          >
            <Trash2 className="h-4 w-4" />
          </Button>
        </div>
      ),
    },
  ];

  return (
    <MainLayout title="Master Produk" breadcrumbs={[{ label: "Master Data" }, { label: "Produk" }]}>
      <PageHeader
        title="Master Produk"
        description="Kelola data produk dan menu"
        action={{
          label: "Tambah Produk",
          onClick: handleOpenAdd,
        }}
      />

      {/* Filter & Search */}
      <div className="flex flex-col sm:flex-row gap-4 mb-6">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            placeholder="Cari kode atau nama produk..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="pl-9"
          />
        </div>
        <Select value={filterKategori} onValueChange={setFilterKategori}>
          <SelectTrigger className="w-full sm:w-[180px]">
            <SelectValue placeholder="Filter Kategori" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="semua">Semua Kategori</SelectItem>
            {kategoriOptions.map((kategori) => (
              <SelectItem key={kategori} value={kategori}>
                {kategori}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>

      {/* Data Table */}
      <DataTable
        columns={columns}
        data={paginatedProducts}
        currentPage={currentPage}
        totalPages={totalPages}
        onPageChange={setCurrentPage}
        emptyMessage="Tidak ada produk ditemukan"
      />

      {/* Add/Edit Dialog */}
      <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
        <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>
              {editingProduct ? "Edit Produk" : "Tambah Produk Baru"}
            </DialogTitle>
          </DialogHeader>

          <div className="grid gap-4 py-4">
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="kode">Kode Produk</Label>
                <Input
                  id="kode"
                  value={formData.kode}
                  onChange={(e) =>
                    setFormData((prev) => ({ ...prev, kode: e.target.value }))
                  }
                  placeholder="PRD001"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="nama">Nama Produk</Label>
                <Input
                  id="nama"
                  value={formData.nama}
                  onChange={(e) =>
                    setFormData((prev) => ({ ...prev, nama: e.target.value }))
                  }
                  placeholder="Masukkan nama produk"
                />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="kategori">Kategori</Label>
                <Select
                  value={formData.kategori}
                  onValueChange={(value) =>
                    setFormData((prev) => ({ ...prev, kategori: value }))
                  }
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Pilih kategori" />
                  </SelectTrigger>
                  <SelectContent>
                    {kategoriOptions.map((kategori) => (
                      <SelectItem key={kategori} value={kategori}>
                        {kategori}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label htmlFor="satuan">Satuan</Label>
                <Select
                  value={formData.satuan}
                  onValueChange={(value) =>
                    setFormData((prev) => ({ ...prev, satuan: value }))
                  }
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Pilih satuan" />
                  </SelectTrigger>
                  <SelectContent>
                    {satuanOptions.map((satuan) => (
                      <SelectItem key={satuan} value={satuan}>
                        {satuan}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="hargaBeli">Harga Beli (Rp)</Label>
                <Input
                  id="hargaBeli"
                  type="number"
                  value={formData.hargaBeli}
                  onChange={(e) =>
                    setFormData((prev) => ({
                      ...prev,
                      hargaBeli: parseInt(e.target.value) || 0,
                    }))
                  }
                  placeholder="0"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="hargaJual">Harga Jual (Rp)</Label>
                <Input
                  id="hargaJual"
                  type="number"
                  value={formData.hargaJual}
                  onChange={(e) =>
                    setFormData((prev) => ({
                      ...prev,
                      hargaJual: parseInt(e.target.value) || 0,
                    }))
                  }
                  placeholder="0"
                />
              </div>
            </div>

            <div className="grid grid-cols-3 gap-4">
              <div className="space-y-2">
                <Label htmlFor="stok">Stok Awal</Label>
                <Input
                  id="stok"
                  type="number"
                  value={formData.stok}
                  onChange={(e) =>
                    setFormData((prev) => ({
                      ...prev,
                      stok: parseInt(e.target.value) || 0,
                    }))
                  }
                  placeholder="0"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="minStok">Stok Minimum</Label>
                <Input
                  id="minStok"
                  type="number"
                  value={formData.minStok}
                  onChange={(e) =>
                    setFormData((prev) => ({
                      ...prev,
                      minStok: parseInt(e.target.value) || 0,
                    }))
                  }
                  placeholder="0"
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="status">Status</Label>
                <Select
                  value={formData.status}
                  onValueChange={(value: "aktif" | "nonaktif") =>
                    setFormData((prev) => ({ ...prev, status: value }))
                  }
                >
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="aktif">Aktif</SelectItem>
                    <SelectItem value="nonaktif">Nonaktif</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="deskripsi">Deskripsi (Opsional)</Label>
              <Textarea
                id="deskripsi"
                value={formData.deskripsi}
                onChange={(e) =>
                  setFormData((prev) => ({ ...prev, deskripsi: e.target.value }))
                }
                placeholder="Deskripsi produk..."
                rows={3}
              />
            </div>
          </div>

          <DialogFooter>
            <Button variant="outline" onClick={() => setIsDialogOpen(false)}>
              Batal
            </Button>
            <Button onClick={handleSave}>
              {editingProduct ? "Simpan Perubahan" : "Tambah Produk"}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Delete Confirmation Dialog */}
      <AlertDialog open={isDeleteDialogOpen} onOpenChange={setIsDeleteDialogOpen}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Hapus Produk?</AlertDialogTitle>
            <AlertDialogDescription>
              Apakah Anda yakin ingin menghapus produk "{deleteTarget?.nama}"?
              Tindakan ini tidak dapat dibatalkan.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Batal</AlertDialogCancel>
            <AlertDialogAction
              onClick={handleDelete}
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
            >
              Hapus
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </MainLayout>
  );
}

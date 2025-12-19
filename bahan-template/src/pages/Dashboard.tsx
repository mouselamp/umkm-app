import { MainLayout } from "@/components/layout/MainLayout";
import { StatCard } from "@/components/ui/stat-card";
import { DataTable, Column } from "@/components/ui/data-table";
import { StatusBadge } from "@/components/ui/status-badge";
import {
  ShoppingCart,
  Package,
  TrendingUp,
  AlertTriangle,
  Factory,
  Users,
} from "lucide-react";

// Mock data untuk demo
const recentOrders = [
  { id: "ORD-001", pelanggan: "Toko Maju", total: "Rp 1.250.000", status: "selesai", tanggal: "2024-01-15" },
  { id: "ORD-002", pelanggan: "Warung Bu Siti", total: "Rp 850.000", status: "proses", tanggal: "2024-01-15" },
  { id: "ORD-003", pelanggan: "Resto Sederhana", total: "Rp 2.100.000", status: "pending", tanggal: "2024-01-14" },
  { id: "ORD-004", pelanggan: "Catering Berkah", total: "Rp 3.500.000", status: "selesai", tanggal: "2024-01-14" },
  { id: "ORD-005", pelanggan: "Toko Jaya", total: "Rp 750.000", status: "proses", tanggal: "2024-01-13" },
];

const lowStockItems = [
  { nama: "Tepung Terigu", stok: 5, satuan: "kg", minimum: 20 },
  { nama: "Minyak Goreng", stok: 3, satuan: "liter", minimum: 10 },
  { nama: "Bumbu Racik", stok: 2, satuan: "pcs", minimum: 15 },
];

const orderColumns: Column<typeof recentOrders[0]>[] = [
  { key: "id", header: "No. Order" },
  { key: "pelanggan", header: "Pelanggan" },
  { key: "total", header: "Total" },
  {
    key: "status",
    header: "Status",
    cell: (row) => (
      <StatusBadge
        variant={
          row.status === "selesai"
            ? "success"
            : row.status === "proses"
            ? "warning"
            : "info"
        }
      >
        {row.status === "selesai" ? "Selesai" : row.status === "proses" ? "Proses" : "Pending"}
      </StatusBadge>
    ),
  },
  { key: "tanggal", header: "Tanggal" },
];

const stockColumns: Column<typeof lowStockItems[0]>[] = [
  { key: "nama", header: "Nama Bahan" },
  {
    key: "stok",
    header: "Stok",
    cell: (row) => (
      <span className="font-medium text-destructive">
        {row.stok} {row.satuan}
      </span>
    ),
  },
  {
    key: "minimum",
    header: "Minimum",
    cell: (row) => (
      <span className="text-muted-foreground">
        {row.minimum} {row.satuan}
      </span>
    ),
  },
];

export default function Dashboard() {
  return (
    <MainLayout
      title="Dashboard"
      breadcrumbs={[{ label: "Dashboard" }]}
    >
      <div className="space-y-6 animate-fade-in">
        {/* Stats Grid */}
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <StatCard
            title="Penjualan Hari Ini"
            value="Rp 5.450.000"
            icon={ShoppingCart}
            variant="primary"
            trend={{ value: 12, isPositive: true }}
          />
          <StatCard
            title="Pesanan Baru"
            value="24"
            subtitle="8 menunggu proses"
            icon={Package}
            variant="info"
          />
          <StatCard
            title="Produksi Hari Ini"
            value="150"
            subtitle="porsi siomay & dimsum"
            icon={Factory}
            variant="success"
          />
          <StatCard
            title="Stok Rendah"
            value="3"
            subtitle="bahan perlu restock"
            icon={AlertTriangle}
            variant="warning"
          />
        </div>

        {/* Two Column Layout */}
        <div className="grid gap-6 lg:grid-cols-2">
          {/* Recent Orders */}
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <h3 className="text-lg font-semibold">Pesanan Terbaru</h3>
              <a
                href="/penjualan/pesanan"
                className="text-sm text-primary hover:underline"
              >
                Lihat Semua
              </a>
            </div>
            <DataTable columns={orderColumns} data={recentOrders} />
          </div>

          {/* Low Stock Alert */}
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <h3 className="text-lg font-semibold flex items-center gap-2">
                <AlertTriangle className="h-5 w-5 text-warning" />
                Peringatan Stok Rendah
              </h3>
              <a
                href="/inventori/stok"
                className="text-sm text-primary hover:underline"
              >
                Kelola Stok
              </a>
            </div>
            <DataTable
              columns={stockColumns}
              data={lowStockItems}
              emptyMessage="Semua stok aman"
            />
          </div>
        </div>

        {/* Quick Stats Row */}
        <div className="grid gap-4 sm:grid-cols-3">
          <div className="rounded-xl border bg-card p-5">
            <div className="flex items-center gap-4">
              <div className="rounded-full bg-primary/10 p-3">
                <TrendingUp className="h-6 w-6 text-primary" />
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Pendapatan Bulan Ini</p>
                <p className="text-xl font-bold">Rp 45.230.000</p>
              </div>
            </div>
          </div>
          <div className="rounded-xl border bg-card p-5">
            <div className="flex items-center gap-4">
              <div className="rounded-full bg-success/10 p-3">
                <Factory className="h-6 w-6 text-success" />
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Total Produksi Bulan Ini</p>
                <p className="text-xl font-bold">2.450 porsi</p>
              </div>
            </div>
          </div>
          <div className="rounded-xl border bg-card p-5">
            <div className="flex items-center gap-4">
              <div className="rounded-full bg-info/10 p-3">
                <Users className="h-6 w-6 text-info" />
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Pelanggan Aktif</p>
                <p className="text-xl font-bold">48 pelanggan</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </MainLayout>
  );
}

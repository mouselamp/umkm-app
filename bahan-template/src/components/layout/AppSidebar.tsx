import { useState } from "react";
import { NavLink, useLocation } from "react-router-dom";
import {
  LayoutDashboard,
  Package,
  Boxes,
  Users,
  Truck,
  UserCog,
  CreditCard,
  Ruler,
  Wallet,
  ShoppingCart,
  ClipboardList,
  Factory,
  BookOpen,
  Receipt,
  Landmark,
  PiggyBank,
  HandCoins,
  FileText,
  Building2,
  ChevronLeft,
  ChevronDown,
  ChevronRight,
  UtensilsCrossed,
} from "lucide-react";
import { cn } from "@/lib/utils";

interface MenuItem {
  title: string;
  icon: React.ElementType;
  path?: string;
  children?: { title: string; path: string }[];
}

const menuItems: MenuItem[] = [
  { title: "Dashboard", icon: LayoutDashboard, path: "/" },
  {
    title: "Master Data",
    icon: Boxes,
    children: [
      { title: "Produk", path: "/master/produk" },
      { title: "Bahan Baku", path: "/master/bahan" },
      { title: "Pelanggan", path: "/master/pelanggan" },
      { title: "Supplier", path: "/master/supplier" },
      { title: "Karyawan", path: "/master/karyawan" },
      { title: "Metode Pembayaran", path: "/master/pembayaran" },
      { title: "Satuan", path: "/master/satuan" },
      { title: "Akun Keuangan", path: "/master/akun" },
    ],
  },
  {
    title: "Inventori",
    icon: Package,
    children: [
      { title: "Stok Bahan", path: "/inventori/stok" },
      { title: "Pembelian", path: "/inventori/pembelian" },
      { title: "Riwayat Pembelian", path: "/inventori/riwayat" },
      { title: "Stock Opname", path: "/inventori/opname" },
    ],
  },
  {
    title: "Produksi",
    icon: Factory,
    children: [
      { title: "Resep/BOM", path: "/produksi/resep" },
      { title: "Input Produksi", path: "/produksi/input" },
      { title: "Riwayat Produksi", path: "/produksi/riwayat" },
      { title: "Stok Produk Jadi", path: "/produksi/stok" },
    ],
  },
  {
    title: "Penjualan",
    icon: ShoppingCart,
    children: [
      { title: "Kasir/POS", path: "/penjualan/kasir" },
      { title: "Daftar Pesanan", path: "/penjualan/pesanan" },
      { title: "Laporan Penjualan", path: "/penjualan/laporan" },
    ],
  },
  {
    title: "Keuangan",
    icon: Wallet,
    children: [
      { title: "Input Modal", path: "/keuangan/modal" },
      { title: "Input Upah", path: "/keuangan/upah" },
      { title: "Daftar Utang", path: "/keuangan/utang" },
      { title: "Pelunasan Utang", path: "/keuangan/pelunasan" },
      { title: "Riwayat Transaksi", path: "/keuangan/riwayat" },
      { title: "Laporan Laba Rugi", path: "/keuangan/labarugi" },
    ],
  },
  {
    title: "Aset",
    icon: Building2,
    children: [
      { title: "Daftar Aset", path: "/aset/daftar" },
      { title: "Penyusutan", path: "/aset/penyusutan" },
      { title: "Laporan Aset", path: "/aset/laporan" },
    ],
  },
];

interface AppSidebarProps {
  collapsed: boolean;
  onToggle: () => void;
}

export function AppSidebar({ collapsed, onToggle }: AppSidebarProps) {
  const location = useLocation();
  const [expandedMenus, setExpandedMenus] = useState<string[]>(["Master Data"]);

  const toggleMenu = (title: string) => {
    setExpandedMenus((prev) =>
      prev.includes(title) ? prev.filter((t) => t !== title) : [...prev, title]
    );
  };

  const isActive = (path: string) => location.pathname === path;
  const isParentActive = (children?: { path: string }[]) =>
    children?.some((child) => location.pathname === child.path);

  return (
    <aside
      className={cn(
        "fixed left-0 top-0 z-40 h-screen bg-sidebar text-sidebar-foreground transition-all duration-300 flex flex-col",
        collapsed ? "w-16" : "w-64"
      )}
    >
      {/* Logo */}
      <div className="flex h-16 items-center justify-between border-b border-sidebar-border px-4">
        {!collapsed && (
          <div className="flex items-center gap-2">
            <UtensilsCrossed className="h-6 w-6 text-sidebar-primary" />
            <span className="font-semibold text-lg">UMKM Makanan</span>
          </div>
        )}
        {collapsed && <UtensilsCrossed className="h-6 w-6 text-sidebar-primary mx-auto" />}
        <button
          onClick={onToggle}
          className={cn(
            "rounded-lg p-1.5 hover:bg-sidebar-accent transition-colors",
            collapsed && "mx-auto"
          )}
        >
          <ChevronLeft
            className={cn("h-5 w-5 transition-transform", collapsed && "rotate-180")}
          />
        </button>
      </div>

      {/* Navigation */}
      <nav className="flex-1 overflow-y-auto scrollbar-thin py-4 px-2">
        <ul className="space-y-1">
          {menuItems.map((item) => (
            <li key={item.title}>
              {item.path ? (
                <NavLink
                  to={item.path}
                  className={cn(
                    "flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors",
                    isActive(item.path)
                      ? "bg-sidebar-primary text-sidebar-primary-foreground"
                      : "hover:bg-sidebar-accent text-sidebar-foreground"
                  )}
                >
                  <item.icon className="h-5 w-5 flex-shrink-0" />
                  {!collapsed && <span className="text-sm font-medium">{item.title}</span>}
                </NavLink>
              ) : (
                <>
                  <button
                    onClick={() => !collapsed && toggleMenu(item.title)}
                    className={cn(
                      "flex w-full items-center gap-3 rounded-lg px-3 py-2.5 transition-colors",
                      isParentActive(item.children)
                        ? "bg-sidebar-accent text-sidebar-accent-foreground"
                        : "hover:bg-sidebar-accent text-sidebar-foreground"
                    )}
                  >
                    <item.icon className="h-5 w-5 flex-shrink-0" />
                    {!collapsed && (
                      <>
                        <span className="flex-1 text-left text-sm font-medium">
                          {item.title}
                        </span>
                        <ChevronDown
                          className={cn(
                            "h-4 w-4 transition-transform",
                            expandedMenus.includes(item.title) && "rotate-180"
                          )}
                        />
                      </>
                    )}
                  </button>
                  {!collapsed && expandedMenus.includes(item.title) && item.children && (
                    <ul className="mt-1 space-y-1 pl-4">
                      {item.children.map((child) => (
                        <li key={child.path}>
                          <NavLink
                            to={child.path}
                            className={cn(
                              "flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition-colors",
                              isActive(child.path)
                                ? "bg-sidebar-primary text-sidebar-primary-foreground"
                                : "hover:bg-sidebar-accent text-sidebar-foreground/80"
                            )}
                          >
                            <ChevronRight className="h-3 w-3" />
                            {child.title}
                          </NavLink>
                        </li>
                      ))}
                    </ul>
                  )}
                </>
              )}
            </li>
          ))}
        </ul>
      </nav>

      {/* Footer */}
      {!collapsed && (
        <div className="border-t border-sidebar-border p-4">
          <p className="text-xs text-sidebar-foreground/60">
            © 2024 UMKM Makanan
          </p>
        </div>
      )}
    </aside>
  );
}

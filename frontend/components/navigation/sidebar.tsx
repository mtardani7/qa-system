"use client";

import { Boxes, Bug, Cable, ChevronLeft, ChevronRight, ClipboardList, Cog, Factory, LayoutDashboard, Clock3, Users, UserCheck, X } from "lucide-react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { Button } from "@/components/ui/button";
import type { NavigationItem } from "@/types/navigation";
import { usePermission } from "@/hooks/use-permission";
import { useAuthStore } from "@/features/auth/store";

const primary: NavigationItem[] = [{ label: "Dashboard", href: "/dashboard", icon: LayoutDashboard, permission: "dashboard.view" }, { label: "Daily Reports", href: "/daily-reports", icon: ClipboardList, permission: "daily-reports.view" }, { label: "Users", href: "/users", icon: Users, superAdminOnly: true }];
const master: NavigationItem[] = [{ label: "Plants", href: "/master-plants", icon: Factory, permission: "plants.view" }, { label: "Lines", href: "/master-lines", icon: Cable, permission: "lines.view" }, { label: "Machines", href: "/master-machines", icon: Cog, permission: "machines.view" }, { label: "Shifts", href: "/master-shifts", icon: Clock3, permission: "shifts.view" }, { label: "Products", href: "/master-products", icon: Boxes, permission: "products.view" }, { label: "Defects", href: "/master-defects", icon: Bug, permission: "defects.view" }, { label: "QA Checkers", href: "/master-qa-checkers", icon: UserCheck, permission: "qa-checkers.view" }];

type Props = { open?: boolean; collapsed?: boolean; onClose?: () => void; onToggleCollapse?: () => void };

export function Sidebar({ open = true, collapsed = false, onClose, onToggleCollapse }: Props) {
  const pathname = usePathname();
  const { can } = usePermission();
  const user = useAuthStore((state) => state.user);
  const isSuperAdmin = user?.roles?.includes("Super Admin") ?? false;
  const renderItem = (entry: NavigationItem) => {
    if (entry.superAdminOnly && !isSuperAdmin) return null;
    if (entry.permission && !can(entry.permission)) return null;
    const Icon = entry.icon;
    const active = pathname === entry.href || pathname.startsWith(`${entry.href}/`);
    return <Link key={entry.href} href={entry.href} onClick={onClose} title={collapsed ? entry.label : undefined} className={`flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors ${collapsed ? "justify-center" : ""} ${active ? "bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300" : "text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"}`}><Icon className="size-4 shrink-0" />{!collapsed && entry.label}</Link>;
  };
  return <aside className={`${open ? "translate-x-0" : "-translate-x-full"} fixed inset-y-0 left-0 z-40 flex h-screen w-64 flex-col border-r border-slate-200 bg-white transition-all duration-200 lg:translate-x-0 dark:border-slate-800 dark:bg-slate-900 ${collapsed ? "lg:w-20" : "lg:w-64"}`}>
    <div className={`flex h-16 items-center border-b border-slate-200 px-4 dark:border-slate-800 ${collapsed ? "justify-center" : "justify-between"}`}>
      <Link href="/dashboard" className="flex min-w-0 items-center gap-2" onClick={onClose}><img src={`${process.env.NEXT_PUBLIC_BASE_PATH ?? ""}/qa-logo.png`} alt="QA System" className="size-9 shrink-0 object-contain" />{!collapsed && <span className="truncate font-semibold tracking-tight text-slate-950 dark:text-white">QA System</span>}</Link>
      <div className="flex items-center gap-1">{onToggleCollapse && <Button variant="ghost" size="icon-sm" className="hidden lg:inline-flex" onClick={onToggleCollapse} aria-label={collapsed ? "Expand sidebar" : "Collapse sidebar"}>{collapsed ? <ChevronRight className="size-4" /> : <ChevronLeft className="size-4" />}</Button>}<Button variant="ghost" size="icon-sm" className="lg:hidden" onClick={onClose} aria-label="Close navigation"><X className="size-4" /></Button></div>
    </div>
    <nav className="flex-1 space-y-6 overflow-y-auto p-4"><div><p className={`mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 ${collapsed ? "text-center" : ""}`}>{collapsed ? "•" : "Workspace"}</p><div className="space-y-1">{primary.map(renderItem)}</div></div><div><p className={`mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 ${collapsed ? "text-center" : ""}`}>{collapsed ? "•" : "Master data"}</p><div className="space-y-1">{master.map(renderItem)}</div></div></nav>
    {!collapsed && <div className="border-t border-slate-200 p-4 dark:border-slate-800"><div className="flex items-center gap-3 rounded-lg bg-slate-50 p-3 dark:bg-slate-950"><div className="flex size-8 shrink-0 items-center justify-center rounded-full bg-red-100 text-xs font-semibold text-red-700">{user?.name?.slice(0, 2).toUpperCase() || "QA"}</div><div className="min-w-0"><p className="truncate text-sm font-medium text-slate-900 dark:text-white">{user?.name || "User"}</p><p className="truncate text-xs text-slate-500">{user?.roles?.join(", ") || "Authenticated user"}</p></div></div></div>}
  </aside>;
}

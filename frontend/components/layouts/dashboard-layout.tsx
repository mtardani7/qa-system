"use client";

import { useEffect, useState } from "react";
import { usePathname, useRouter } from "next/navigation";
import { Skeleton } from "@/components/ui/skeleton";
import { useAuthStore } from "@/features/auth/store";
import { Sidebar } from "@/components/navigation/sidebar";
import { Header } from "@/components/navigation/header";
import { usePermission } from "@/hooks/use-permission";

export function DashboardLayout({ children }: { children: React.ReactNode }) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [sidebarCollapsed, setSidebarCollapsed] = useState(false);
  const router = useRouter();
  const pathname = usePathname();
  const hydrated = useAuthStore((state) => state.hydrated);
  const user = useAuthStore((state) => state.user);
  const { can } = usePermission();
  const isSuperAdmin = user?.roles?.includes("Super Admin") ?? false;
  const required = pathname.startsWith("/dashboard") ? "dashboard.view" : pathname.startsWith("/daily-reports") ? "daily-reports.view" : pathname.startsWith("/master-plants") ? "plants.view" : pathname.startsWith("/master-lines") ? "lines.view" : pathname.startsWith("/master-machines") ? "machines.view" : pathname.startsWith("/master-shifts") ? "shifts.view" : pathname.startsWith("/master-products") ? "products.view" : pathname.startsWith("/master-defects") ? "defects.view" : pathname.startsWith("/master-qa-checkers") ? "qa-checkers.view" : pathname.startsWith("/users") ? "users.view" : pathname.startsWith("/settings/users") ? "users.view" : null;
  const superAdminOnlyRoute = pathname.startsWith("/users") || pathname.startsWith("/settings/users");
  const loading = !hydrated || !user;
  const unauthorized = Boolean(hydrated && user && ((superAdminOnlyRoute && !isSuperAdmin) || (!superAdminOnlyRoute && required && !can(required))));
  const shouldRedirectToLogin = hydrated && !user;

  useEffect(() => {
    if (shouldRedirectToLogin) {
      router.replace(`/login?next=${encodeURIComponent(pathname)}`);
      return;
    }

    if (unauthorized) {
      router.replace("/403");
    }
  }, [pathname, router, shouldRedirectToLogin, unauthorized]);

  return <div className="flex h-screen min-h-0 overflow-hidden bg-slate-50 dark:bg-slate-950"><Sidebar open={sidebarOpen} collapsed={sidebarCollapsed} onClose={() => setSidebarOpen(false)} onToggleCollapse={() => setSidebarCollapsed((collapsed) => !collapsed)} /><div className="fixed inset-0 z-30 bg-slate-950/40 lg:hidden" hidden={!sidebarOpen} onClick={() => setSidebarOpen(false)} /><div className={`flex min-h-0 min-w-0 flex-1 flex-col transition-[padding] duration-200 ${sidebarCollapsed ? "lg:pl-20" : "lg:pl-64"}`}><Header onMenuClick={() => setSidebarOpen(true)} /><main className="min-h-0 min-w-0 flex-1 overflow-y-auto">{loading || unauthorized ? <div className="mx-auto w-full max-w-7xl space-y-4 p-6 lg:p-10"><Skeleton className="h-8 w-48" /><Skeleton className="h-20 w-full" /><Skeleton className="h-64 w-full" /></div> : children}</main></div></div>;
}

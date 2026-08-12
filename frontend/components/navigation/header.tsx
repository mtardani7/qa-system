"use client";

import { ChevronDown, LogOut, Menu, Moon, Sun, UserCircle } from "lucide-react";
import { useState } from "react";
import { useRouter } from "next/navigation";
import { Button } from "@/components/ui/button";
import { useTheme } from "@/providers/theme-provider";
import { useAuthStore } from "@/features/auth/store";
import { logout } from "@/features/auth/services";
import { Breadcrumb } from "./breadcrumb";

export function Header({ onMenuClick }: { onMenuClick: () => void }) {
  const { theme, mounted, toggleTheme } = useTheme();
  const router = useRouter();
  const user = useAuthStore((state) => state.user);
  const clearSession = useAuthStore((state) => state.clearSession);
  const [profileOpen, setProfileOpen] = useState(false);

  async function signOut() {
    try { await logout(); } finally { clearSession(); router.replace("/login"); }
  }

  return (
    <header className="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-5 lg:px-8 dark:border-slate-800 dark:bg-slate-900">
      <div className="flex items-center gap-3">
        <Button variant="ghost" size="icon-sm" className="lg:hidden" onClick={onMenuClick} aria-label="Open navigation"><Menu className="size-5" /></Button>
        <Breadcrumb />
      </div>
      <div className="flex items-center gap-2">
        <Button variant="ghost" size="icon-sm" onClick={toggleTheme} aria-label="Toggle theme" disabled={!mounted}>{!mounted ? <span className="size-4" aria-hidden="true" /> : theme === "dark" ? <Sun className="size-4" /> : <Moon className="size-4" />}</Button>
        <div className="relative">
          <Button variant="ghost" className="gap-2 px-2" onClick={() => setProfileOpen((open) => !open)} aria-expanded={profileOpen} aria-label="Open profile menu">
            <UserCircle className="size-6 text-red-700 dark:text-red-400" />
            <span className="hidden max-w-32 truncate text-sm font-medium sm:inline">{user?.name || "User"}</span>
            <ChevronDown className="size-4" />
          </Button>
          {profileOpen && <div className="absolute right-0 z-50 mt-2 w-56 rounded-xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-700 dark:bg-slate-900">
            <div className="border-b border-slate-100 px-3 py-2 dark:border-slate-800"><p className="truncate text-sm font-semibold">{user?.name || "User"}</p><p className="truncate text-xs text-slate-500">{user?.email || "Authenticated user"}</p></div>
            <button type="button" className="mt-1 flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/30" onClick={signOut}><LogOut className="size-4" /> Logout</button>
          </div>}
        </div>
      </div>
    </header>
  );
}

"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAuthStore } from "@/features/auth/store";

export function AuthLayout({ children }: { children: React.ReactNode }) {
  const router = useRouter();
  const hydrated = useAuthStore((state) => state.hydrated);
  const user = useAuthStore((state) => state.user);
  useEffect(() => { if (hydrated && user) router.replace("/dashboard"); }, [hydrated, user, router]);
  return <main className="flex min-h-screen items-center justify-center bg-slate-50 p-6 dark:bg-slate-950"><div className="w-full max-w-md"><div className="mb-8 flex items-center justify-center"><img src="/qa-logo.png" alt="QA Management System" className="mr-2 size-11 object-contain" /><span className="text-lg font-semibold text-slate-950 dark:text-white">QA Management System</span></div>{children}</div></main>;
}

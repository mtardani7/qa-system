"use client";
import { useEffect } from "react";
import { getCurrentUser } from "@/features/auth/services";
import { useAuthStore } from "@/features/auth/store";
export function AuthProvider({ children }: { children: React.ReactNode }) { const setUser = useAuthStore((state) => state.setUser); const setHydrated = useAuthStore((state) => state.setHydrated); useEffect(() => { const token = window.localStorage.getItem("qms_token"); if (!token) { setHydrated(true); return; } getCurrentUser().then(({ user, roles, permissions }) => setUser({ ...user, roles, permissions })).catch(() => { window.localStorage.removeItem("qms_token"); }).finally(() => setHydrated(true)); }, [setHydrated, setUser]); return children; }

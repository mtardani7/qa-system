"use client";
import { create } from "zustand";
import type { AuthUser } from "./types";
type AuthState = { token: string | null; user: AuthUser | null; roles: string[]; permissions: string[]; hydrated: boolean; setSession: (token: string, user: AuthUser, roles: string[], permissions: string[]) => void; setUser: (user: AuthUser) => void; clearSession: () => void; setHydrated: (hydrated: boolean) => void; can: (permission: string) => boolean };
export const useAuthStore = create<AuthState>((set, get) => ({ token: null, user: null, roles: [], permissions: [], hydrated: false, setSession: (token, user, roles, permissions) => { if (typeof window !== "undefined") window.localStorage.setItem("qms_token", token); set({ token, user, roles, permissions, hydrated: true }); }, setUser: (user) => set({ user, roles: user.roles, permissions: user.permissions }), clearSession: () => { if (typeof window !== "undefined") window.localStorage.removeItem("qms_token"); set({ token: null, user: null, roles: [], permissions: [], hydrated: true }); }, setHydrated: (hydrated) => set({ hydrated }), can: (permission) => get().permissions.includes(permission) }));
export const authStore = useAuthStore;

"use client";

import { QueryProvider } from "./query-provider";
import { ThemeProvider } from "./theme-provider";
import { PwaRegister } from "@/components/pwa-register";
import { AuthProvider } from "./auth-provider";
import { SessionExpiredDialog } from "@/components/session-expired-dialog";
import { Toaster } from "sonner";

export function AppProviders({ children }: { children: React.ReactNode }) { return <ThemeProvider><QueryProvider><AuthProvider><PwaRegister /><SessionExpiredDialog /><Toaster position="bottom-right" richColors closeButton />{children}</AuthProvider></QueryProvider></ThemeProvider>; }

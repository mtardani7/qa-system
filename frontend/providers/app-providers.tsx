"use client";

import { QueryProvider } from "./query-provider";
import { ThemeProvider } from "./theme-provider";
import { PwaRegister } from "@/components/pwa-register";
import { AuthProvider } from "./auth-provider";
import { SessionExpiredDialog } from "@/components/session-expired-dialog";

export function AppProviders({ children }: { children: React.ReactNode }) { return <ThemeProvider><QueryProvider><AuthProvider><PwaRegister /><SessionExpiredDialog />{children}</AuthProvider></QueryProvider></ThemeProvider>; }

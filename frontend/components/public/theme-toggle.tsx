"use client";
import { Moon, Sun } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useTheme } from "@/providers/theme-provider";
export function ThemeToggle() { const { theme, mounted, toggleTheme } = useTheme(); return <Button variant="ghost" size="icon-sm" aria-label="Toggle theme" disabled={!mounted} onClick={toggleTheme}>{!mounted ? <span className="size-4" /> : theme === "dark" ? <Sun className="size-4" /> : <Moon className="size-4" />}</Button>; }

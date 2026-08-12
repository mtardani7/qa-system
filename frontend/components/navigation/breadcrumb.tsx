"use client";

import { ChevronRight, Home } from "lucide-react";
import Link from "next/link";
import { usePathname } from "next/navigation";
export function Breadcrumb() { const segments = usePathname().split("/").filter(Boolean); return <nav aria-label="Breadcrumb" className="flex items-center gap-2 text-sm"><Link href="/dashboard" className="text-slate-400 hover:text-slate-600" aria-label="Dashboard"><Home className="size-4" /></Link>{segments.map((segment, index) => <span key={segment} className="flex items-center gap-2"><ChevronRight className="size-3 text-slate-300" /><span className={index === segments.length - 1 ? "font-medium capitalize text-slate-900 dark:text-white" : "capitalize text-slate-400"}>{segment.replaceAll("-", " ")}</span></span>)}</nav>; }

"use client";

import Link from "next/link";
import { ArrowLeft } from "lucide-react";
import { DailyReportTable } from "@/features/daily-reports/components/daily-report-table";

export default function DailyReportHistoryPage() { return <main className="min-h-screen bg-slate-50 px-6 py-8 lg:px-10 dark:bg-slate-950"><div className="mx-auto max-w-[1600px]"><div className="mb-8 mt-6 flex items-end justify-between"><div><p className="text-sm font-medium text-emerald-600">Quality operations</p><h1 className="mt-2 text-3xl font-semibold tracking-tight">Daily QA History</h1><p className="mt-2 text-sm text-muted-foreground">Search and review recorded Daily QA Reports.</p></div><Link href="/daily-reports" className="inline-flex h-8 items-center gap-1.5 rounded-lg border px-2.5 text-sm font-medium hover:bg-muted"><ArrowLeft className="size-4" /> Entry</Link></div><DailyReportTable /></div></main>; }

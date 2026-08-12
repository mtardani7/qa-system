"use client";

import Link from "next/link";
import { Plus, History, Upload } from "lucide-react";
import { useState } from "react";
import { Button } from "@/components/ui/button";
import { DailyReportFormDialog } from "@/features/daily-reports/components/daily-report-form-dialog";
import { DailyReportTable } from "@/features/daily-reports/components/daily-report-table";
import type { DailyReport } from "@/features/daily-reports/types";
import { DailyReportImportDialog } from "@/features/daily-reports/components/daily-report-import-dialog";

export default function DailyReportsPage() { const [open, setOpen] = useState(false); const [importOpen, setImportOpen] = useState(false); const [selected, setSelected] = useState<DailyReport | null>(null); return <main className="min-h-screen bg-slate-50 px-6 py-8 lg:px-10 dark:bg-slate-950"><div className="mx-auto max-w-[1600px]"><div className="mb-8 mt-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p className="text-sm font-medium text-emerald-600">Quality operations</p><h1 className="mt-2 text-3xl font-semibold tracking-tight">Daily QA Report</h1><p className="mt-2 text-sm text-muted-foreground">Capture production output and multiple quality defects by shift.</p></div><div className="flex flex-wrap gap-2"><Link href="/daily-reports/history" className="inline-flex h-8 items-center gap-1.5 rounded-lg border px-2.5 text-sm font-medium hover:bg-muted"><History className="size-4" /> History</Link><Button variant="outline" onClick={() => setImportOpen(true)}><Upload className="size-4" /> Import Excel</Button><Button onClick={() => { setSelected(null); setOpen(true); }}><Plus className="size-4" /> New report</Button></div></div><DailyReportTable onEdit={(report) => { setSelected(report); setOpen(true); }} /><DailyReportFormDialog open={open} report={selected} onClose={() => setOpen(false)} /><DailyReportImportDialog open={importOpen} onClose={() => setImportOpen(false)} /></div></main>; }

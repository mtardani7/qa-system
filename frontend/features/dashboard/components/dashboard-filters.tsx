"use client";

import { RotateCcw, SlidersHorizontal } from "lucide-react";
import { Button } from "@/components/ui/button";
import type { LookupOption } from "@/features/daily-reports/types";
import type { DashboardQuery } from "../types";

type Props = { query: DashboardQuery; plants: LookupOption[]; lines: LookupOption[]; machines: LookupOption[]; shifts: LookupOption[]; products: LookupOption[]; checkers: LookupOption[]; onChange: (next: Partial<DashboardQuery>) => void; onReset: () => void };
const selectClass = "h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-red-500 focus:ring-2 focus:ring-red-500/15 dark:border-slate-700 dark:bg-slate-900";

export function DashboardFilters({ query, plants, lines, machines, shifts, products, checkers, onChange, onReset }: Props) {
  const scopedLines = lines.filter((item) => !query.plant_id || item.plant_id === query.plant_id);
  const scopedMachines = machines.filter((item) => (!query.plant_id || item.plant_id === query.plant_id) && (!query.line_id || item.line_id === query.line_id));
  const scopedProducts = products.filter((item) => !query.plant_id || !item.plant_id || item.plant_id === query.plant_id);
  const field = (name: keyof DashboardQuery, value: string) => onChange({ [name]: value ? (name === "period" ? value : Number(value)) : undefined });
  return <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-5">
    <div className="mb-4 flex items-center justify-between gap-3"><div className="flex items-center gap-2"><span className="flex size-8 items-center justify-center rounded-lg bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300"><SlidersHorizontal className="size-4" /></span><div><h2 className="text-sm font-semibold">Dashboard filters</h2><p className="text-xs text-muted-foreground">Refine the production and quality view.</p></div></div><Button type="button" variant="outline" size="sm" className="gap-1.5" onClick={onReset}><RotateCcw className="size-3.5" /> Reset</Button></div>
    <div className="grid min-w-0 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
      <label className="min-w-0 text-xs font-medium text-muted-foreground">Period<select className={selectClass} value={query.period ?? "daily"} onChange={(event) => field("period", event.target.value)}><option value="daily">Daily</option><option value="weekly">Weekly</option><option value="monthly">Monthly</option><option value="yearly">Yearly</option></select></label>
      <label className="min-w-0 text-xs font-medium text-muted-foreground">Date<input type="date" className={selectClass} value={query.date} onChange={(event) => onChange({ date: event.target.value })} /></label>
      <label className="min-w-0 text-xs font-medium text-muted-foreground">Plant<select className={selectClass} value={query.plant_id ?? ""} onChange={(event) => onChange({ plant_id: event.target.value ? Number(event.target.value) : undefined, line_id: undefined, machine_id: undefined })}><option value="">All plants</option>{plants.map((item) => <option key={item.id} value={item.id}>{item.code} — {item.name}</option>)}</select></label>
      <label className="min-w-0 text-xs font-medium text-muted-foreground">Line<select className={selectClass} value={query.line_id ?? ""} onChange={(event) => onChange({ line_id: event.target.value ? Number(event.target.value) : undefined, machine_id: undefined })}><option value="">All lines</option>{scopedLines.map((item) => <option key={item.id} value={item.id}>{item.code} — {item.name}</option>)}</select></label>
      <label className="min-w-0 text-xs font-medium text-muted-foreground">Machine<select className={selectClass} value={query.machine_id ?? ""} onChange={(event) => field("machine_id", event.target.value)}><option value="">All machines</option>{scopedMachines.map((item) => <option key={item.id} value={item.id}>{item.code} — {item.name}</option>)}</select></label>
      <label className="min-w-0 text-xs font-medium text-muted-foreground">Shift<select className={selectClass} value={query.shift_id ?? ""} onChange={(event) => field("shift_id", event.target.value)}><option value="">All shifts</option>{shifts.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}</select></label>
      <label className="min-w-0 text-xs font-medium text-muted-foreground">Product<select className={selectClass} value={query.product_id ?? ""} onChange={(event) => field("product_id", event.target.value)}><option value="">All products</option>{scopedProducts.map((item) => <option key={item.id} value={item.id}>{item.mm_number || item.name}</option>)}</select></label>
      <label className="min-w-0 text-xs font-medium text-muted-foreground">QA checker<select className={selectClass} value={query.checker_id ?? ""} onChange={(event) => field("checker_id", event.target.value)}><option value="">All QA checkers</option>{checkers.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}</select></label>
    </div>
  </section>;
}

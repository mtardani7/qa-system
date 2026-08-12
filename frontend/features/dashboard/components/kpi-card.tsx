import type { LucideIcon } from "lucide-react";
import { CheckCircle2, CircleAlert, CircleX } from "lucide-react";

export function KpiCard({ label, value, icon: Icon, indicator }: { label: string; value: string; icon: LucideIcon; indicator?: "green" | "yellow" | "red" }) {
  const Indicator = indicator === "green" ? CheckCircle2 : indicator === "yellow" ? CircleAlert : CircleX;
  const accent = indicator === "green" ? "text-emerald-600" : indicator === "yellow" ? "text-amber-500" : indicator === "red" ? "text-red-600" : "text-red-700";
  return <article className="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-800 dark:bg-slate-900"><div className="flex items-start justify-between gap-3"><span className="text-xs font-medium uppercase tracking-wide text-muted-foreground">{label}</span><span className={`flex size-9 shrink-0 items-center justify-center rounded-xl bg-slate-50 dark:bg-slate-950 ${accent}`}>{indicator ? <Indicator className="size-5" /> : <Icon className="size-5" />}</span></div><p className="mt-5 truncate text-2xl font-bold tracking-tight text-slate-950 dark:text-white">{value}</p>{indicator && <p className={`mt-2 text-xs font-medium ${accent}`}>{indicator === "green" ? "On target" : indicator === "yellow" ? "Needs attention" : "Critical"}</p>}</article>;
}

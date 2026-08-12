"use client";

import ReactECharts from "echarts-for-react";
import type { EChartsOption } from "echarts";

type Props = { title: string; subtitle?: string; option: EChartsOption; onReportClick?: (reportId?: number) => void };
export function EChartsPanel({ title, subtitle, option, onReportClick }: Props) {
  const events = onReportClick ? { click: (event: { data?: { report_id?: number } }) => onReportClick(event.data?.report_id) } : undefined;
  return <section className="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6"><div className="mb-2"><h2 className="text-base font-semibold text-slate-950 dark:text-white">{title}</h2>{subtitle && <p className="mt-1 text-xs text-muted-foreground">{subtitle}</p>}</div><div className="min-w-0"><ReactECharts option={option} onEvents={events} style={{ height: 320, width: "100%" }} opts={{ renderer: "canvas" }} /></div></section>;
}

"use client";

import { Activity, Factory, Gauge, RefreshCw, ShieldAlert } from "lucide-react";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { Button } from "@/components/ui/button";
import { useDailyReportLookups } from "@/features/daily-reports/hooks";
import { DashboardFilters } from "@/features/dashboard/components/dashboard-filters";
import { EChartsPanel } from "@/features/dashboard/components/echarts-panel";
import { KpiCard } from "@/features/dashboard/components/kpi-card";
import { RankedBarChart } from "@/features/dashboard/components/ranked-bar-chart";
import { paretoOption, trendOption } from "@/features/dashboard/components/dashboard-chart-options";
import { useDashboard } from "@/features/dashboard/hooks";
import type { DashboardQuery, DashboardRanking } from "@/features/dashboard/types";

const initialQuery = (): DashboardQuery => {
  const now = new Date();
  const date = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(now.getDate()).padStart(2, "0")}`;

  return { date, period: "monthly" };
};

function DashboardSkeleton() {
  return <div className="space-y-6">
    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">{Array.from({ length: 4 }, (_, index) => <div key={index} className="h-32 animate-pulse rounded-lg bg-slate-200 dark:bg-slate-800" />)}</div>
    <div className="grid gap-5 lg:grid-cols-2">{Array.from({ length: 2 }, (_, index) => <div key={index} className="h-[400px] animate-pulse rounded-lg bg-slate-200 dark:bg-slate-800" />)}</div>
  </div>;
}

export default function DashboardPage() {
  const router = useRouter();
  const [query, setQuery] = useState<DashboardQuery>(initialQuery);
  const { data: lookups } = useDailyReportLookups();
  const dashboard = useDashboard(query);
  const { data, isLoading, isError, refetch, dataUpdatedAt } = dashboard;
  const updateQuery = (next: Partial<DashboardQuery>) => setQuery((current) => ({ ...current, ...next }));
  const drillDown = (reportId?: number) => { if (reportId) router.push(`/daily-reports/${reportId}`); };
  const updatedLabel = dataUpdatedAt ? new Date(dataUpdatedAt).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" }) : "-";
  const defectRows: DashboardRanking[] = data?.top_defects.map((item) => ({ id: item.id, report_id: item.report_id, code: item.code, name: item.name, value: item.quantity })) ?? [];
  const period = query.period ?? "daily";
  const periodLabel = period.charAt(0).toUpperCase() + period.slice(1);

  return (
    <main className="min-h-screen bg-slate-50 px-4 py-6 dark:bg-slate-950 sm:px-6 lg:px-8 lg:py-8">
      <div className="mx-auto w-full max-w-[1800px] space-y-6">
        <section className="flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <div className="flex items-center gap-2 text-sm font-semibold text-red-700 dark:text-red-400"><Activity className="size-4" /> QA SYSTEM</div>
            <h1 className="mt-2 text-2xl font-bold tracking-tight text-slate-950 dark:text-white sm:text-3xl">Performance Dashboard</h1>
            <p className="mt-2 max-w-2xl text-sm text-muted-foreground">Monitor production and quality performance across all plants.</p>
          </div>
          <div className="flex items-center gap-3">
            <div className="text-right"><p className="text-[11px] font-medium uppercase tracking-wide text-muted-foreground">Last updated</p><p className="text-sm font-semibold">{updatedLabel}</p></div>
            <Button type="button" variant="outline" size="sm" className="gap-2" onClick={() => refetch()} disabled={isLoading}><RefreshCw className="size-4" /> Refresh</Button>
          </div>
        </section>

        <DashboardFilters query={query} plants={lookups?.plants ?? []} lines={lookups?.lines ?? []} machines={lookups?.machines ?? []} shifts={lookups?.shifts ?? []} products={lookups?.products ?? []} checkers={lookups?.qa_checkers ?? []} onChange={updateQuery} onReset={() => setQuery(initialQuery())} />

        {isLoading ? <DashboardSkeleton /> : isError || !data ? (
          <div className="rounded-lg border border-red-200 bg-red-50 p-8 text-center text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">Unable to load dashboard data.</div>
        ) : <>
          <section>
            <div className="mb-3 flex items-center justify-between"><h2 className="text-base font-semibold text-slate-950 dark:text-white">{periodLabel} overview</h2><span className="text-xs text-muted-foreground">{data.date}</span></div>
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
              <KpiCard label={`${periodLabel} Production`} value={data.summary.production_pcs.toLocaleString()} icon={Factory} />
              <KpiCard label={`${periodLabel} Defects`} value={data.summary.defect_qty.toLocaleString()} icon={ShieldAlert} />
              <KpiCard label="Top Defect Category" value={data.defect_categories[0] ? `${data.defect_categories[0].name} (${data.defect_categories[0].value.toLocaleString()} PCS)` : "-"} icon={ShieldAlert} />
              <KpiCard label="Worst Machine by Defect Rate" value={data.worst_machine_by_defect_rate ? `${data.worst_machine_by_defect_rate.name} (${data.worst_machine_by_defect_rate.value}%)` : "-"} icon={Gauge} />
            </div>
          </section>

          <section>
            <div className="mb-3"><h2 className="text-base font-semibold text-slate-950 dark:text-white">Quality trends and comparisons</h2></div>
            <div className="grid min-w-0 gap-5 lg:grid-cols-2">
              <EChartsPanel title="Production Trend" option={trendOption(data.production_trend, "production_pcs")} onReportClick={drillDown} />
              <EChartsPanel title="Defect Trend" option={trendOption(data.defect_trend, "defect_qty")} onReportClick={drillDown} />
              <EChartsPanel title="Pareto Defect" option={paretoOption(data.pareto)} onReportClick={drillDown} />
              <RankedBarChart title="Change Over per Section" rows={data.change_over_sections} unit="Reports" emptyMessage="No change-over section data available for the selected filters." color="violet" onReportClick={drillDown} />
              <RankedBarChart title="Change Over per Machine" rows={data.change_over_machines} unit="Reports" emptyMessage="No change-over machine data available for the selected filters." onReportClick={drillDown} />
              <RankedBarChart title="Defect by Category" rows={data.defect_categories} unit="PCS" emptyMessage="No defect category data available for the selected filters." color="violet" onReportClick={drillDown} />
              <RankedBarChart title="Top 10 Machines by Output PCS" rows={data.top_machines} emptyMessage="No machine data available for the selected filters." onReportClick={drillDown} />
              <RankedBarChart title="Top 10 Products by Output PCS" rows={data.top_products} emptyMessage="No product data available for the selected filters." color="violet" onReportClick={drillDown} />
              <RankedBarChart title="Top 10 Defects" rows={defectRows} unit="Defects" emptyMessage="No defect data available for the selected filters." color="violet" onReportClick={drillDown} />
              <RankedBarChart title="Shift Comparison" rows={data.comparisons.shifts} emptyMessage="No shift data available for the selected filters." onReportClick={drillDown} />
              <RankedBarChart title="Machine Comparison" rows={data.comparisons.machines} emptyMessage="No machine comparison data available for the selected filters." onReportClick={drillDown} />
              <RankedBarChart title="Plant Comparison" rows={data.comparisons.plants} emptyMessage="No plant data available for the selected filters." onReportClick={drillDown} />
            </div>
          </section>
        </>}
      </div>
    </main>
  );
}

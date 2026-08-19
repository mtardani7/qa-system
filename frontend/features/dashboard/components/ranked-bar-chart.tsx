import type { DashboardRanking } from "../types";

type Props = {
  title: string;
  rows: DashboardRanking[];
  emptyMessage: string;
  unit?: string;
  isLoading?: boolean;
  color?: "sky" | "violet";
  onReportClick?: (reportId?: number) => void;
};

const numberFormatter = new Intl.NumberFormat("en-US", { maximumFractionDigits: 0 });

export function RankedBarChart({ title, rows, emptyMessage, unit = "PCS", isLoading = false, color = "sky", onReportClick }: Props) {
  const maxValue = Math.max(...rows.map((row) => row.value), 1);
  const barColor = color === "violet" ? "bg-violet-500 dark:bg-violet-400" : "bg-sky-500 dark:bg-sky-400";

  return (
    <section className="min-w-0 rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
      <div className="mb-5">
        <h2 className="text-base font-semibold text-slate-950 dark:text-white">{title}</h2>
        <p className="mt-1 text-xs text-muted-foreground">{unit === "Defects" ? "Ranked by total defect quantity for the selected filters." : "Ranked by total output for the selected filters."}</p>
      </div>

      {isLoading ? (
        <div className="space-y-4" aria-label={`Loading ${title}`}>
          {Array.from({ length: 10 }, (_, index) => <div key={index} className="grid grid-cols-[2.25rem_minmax(0,1fr)_5rem] items-center gap-3"><div className="h-4 animate-pulse rounded bg-slate-200 dark:bg-slate-800" /><div className="h-2.5 animate-pulse rounded-full bg-slate-200 dark:bg-slate-800" /><div className="h-4 animate-pulse rounded bg-slate-200 dark:bg-slate-800" /></div>)}
        </div>
      ) : rows.length === 0 ? (
        <div className="flex h-[360px] items-center justify-center rounded-md border border-dashed border-slate-200 px-6 text-center text-sm text-slate-500 dark:border-slate-700">
          {emptyMessage}
        </div>
      ) : (
        <ol className="space-y-3" aria-label={title}>
          {rows.map((row, index) => {
            const rank = index + 1;
            const topRank = rank <= 3;

            return (
              <li key={`${row.id}-${row.code}-${row.name}`}>
                <button
                  type="button"
                  className="grid w-full grid-cols-[2.25rem_minmax(0,1fr)_auto] items-center gap-x-2 gap-y-2 rounded-md py-1 text-left transition-colors hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 dark:hover:bg-slate-800/70 sm:grid-cols-[2.25rem_minmax(7rem,11rem)_minmax(0,1fr)_auto] sm:gap-x-3"
                  onClick={() => onReportClick?.(row.report_id)}
                  title={`${row.name}\nOutput: ${numberFormatter.format(row.value)} ${unit}\nRank: #${rank}`}
                >
                  <span className={`text-xs font-semibold tabular-nums ${topRank ? "text-slate-900 dark:text-white" : "text-slate-400 dark:text-slate-500"}`}>#{rank}</span>
                  <span className={`min-w-0 truncate text-sm ${topRank ? "font-medium text-slate-800 dark:text-slate-100" : "text-slate-600 dark:text-slate-300"}`} title={row.name}>{row.name}</span>
                  <span className="order-last col-start-2 col-span-2 h-2.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800 sm:order-none sm:col-start-auto sm:col-span-1">
                    <span className={`block h-full min-w-1 rounded-full ${barColor}`} style={{ width: `${Math.max((row.value / maxValue) * 100, 2)}%` }} />
                  </span>
                  <span className="whitespace-nowrap text-right text-sm font-semibold tabular-nums text-slate-900 dark:text-white">{numberFormatter.format(row.value)} <span className="text-xs font-medium text-slate-500 dark:text-slate-400">{unit}</span></span>
                </button>
              </li>
            );
          })}
        </ol>
      )}
    </section>
  );
}

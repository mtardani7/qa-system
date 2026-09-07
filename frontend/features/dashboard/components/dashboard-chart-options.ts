import type { DashboardDefect, DashboardRanking, DashboardTrend } from "../types";
import type { EChartsOption } from "echarts";

const axis = {
  axisLabel: { color: "#64748b" },
  axisLine: { lineStyle: { color: "#cbd5e1" } },
  splitLine: { lineStyle: { color: "#e2e8f0" } },
};

const compactNumber = new Intl.NumberFormat("en-US", {
  notation: "compact",
  maximumFractionDigits: 1,
});

const wholeNumber = new Intl.NumberFormat("en-US", { maximumFractionDigits: 0 });

function formatCompactNumber(value: unknown) {
  const resolvedValue = Array.isArray(value) ? value[0] : value;
  if (resolvedValue === undefined || resolvedValue === null) return "-";

  const numericValue = Number(resolvedValue);
  if (!Number.isFinite(numericValue)) return String(resolvedValue);

  return compactNumber.format(numericValue);
}

function truncateAxisLabel(value: string, maxLength = 28) {
  if (value.length <= maxLength) return value;

  return `${value.slice(0, maxLength - 1)}...`;
}

export function trendOption(rows: DashboardTrend[], metric: "production_pcs" | "defect_qty" | "yield"): EChartsOption {
  const isPercentage = metric === "yield";

  return {
    tooltip: {
      trigger: "axis",
      valueFormatter: (value) => isPercentage ? `${Number(value).toFixed(3)}%` : `${wholeNumber.format(Number(value))}${metric === "production_pcs" ? " PCS" : ""}`,
    },
    grid: { left: 56, right: 20, top: 24, bottom: 40, containLabel: true },
    xAxis: { type: "category", data: rows.map((row) => row.date), ...axis, axisLabel: { color: "#64748b", hideOverlap: true } },
    yAxis: { type: "value", ...axis, axisLabel: { color: "#64748b", formatter: isPercentage ? "{value}%" : formatCompactNumber } },
    series: [
      {
        type: "line",
        smooth: true,
        data: rows.map((row) => ({ value: row[metric], report_id: row.report_id })),
        areaStyle: { opacity: 0.12 },
        itemStyle: { color: metric === "yield" ? "#8b5cf6" : metric === "defect_qty" ? "#ef4444" : "#059669" },
      },
    ],
  };
}

export function rankingOption(rows: DashboardRanking[]): EChartsOption {
  const rankingRows = [...rows].reverse();

  return {
    tooltip: { trigger: "axis", valueFormatter: formatCompactNumber },
    grid: { left: 168, right: 24, top: 18, bottom: 34, containLabel: true },
    xAxis: {
      type: "value",
      min: 0,
      splitNumber: 4,
      ...axis,
      axisLabel: { color: "#64748b", hideOverlap: true, formatter: formatCompactNumber },
    },
    yAxis: {
      type: "category",
      data: rankingRows.map((row) => row.name),
      axisLabel: { color: "#64748b", width: 150, overflow: "truncate", formatter: truncateAxisLabel },
    },
    series: [
      {
        type: "bar",
        data: rankingRows.map((row) => ({ value: row.value, report_id: row.report_id })),
        barMaxWidth: 38,
        itemStyle: { color: "#0ea5e9" },
        label: {
          show: true,
          position: "right",
          color: "#475569",
          fontSize: 12,
          formatter: ({ value }) => formatCompactNumber(Array.isArray(value) ? value[0] : value),
        },
      },
    ],
  };
}

export function paretoOption(rows: DashboardDefect[]): EChartsOption {
  return {
    tooltip: { trigger: "axis", valueFormatter: (value) => wholeNumber.format(Number(value)) },
    legend: { data: ["Defects", "Cumulative %"] },
    grid: { left: 52, right: 52, top: 42, bottom: 82, containLabel: true },
    xAxis: { type: "category", data: rows.map((row) => row.name), axisLabel: { rotate: 28, color: "#64748b", width: 88, overflow: "truncate" } },
    yAxis: [
      { type: "value", ...axis },
      { type: "value", min: 0, max: 100, ...axis },
    ],
    series: [
      {
        name: "Defects",
        type: "bar",
        data: rows.map((row) => ({ value: row.quantity, report_id: row.report_id })),
        itemStyle: { color: "#ef4444" },
      },
      {
        name: "Cumulative %",
        type: "line",
        yAxisIndex: 1,
        data: rows.map((row) => row.cumulative_percentage ?? 0),
        itemStyle: { color: "#f59e0b" },
      },
    ],
  };
}

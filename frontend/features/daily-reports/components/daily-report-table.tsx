"use client";

import {
  Check,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  ChevronUp,
  Copy,
  ClipboardPaste,
  Edit2,
  Eye,
  FileDown,
  Lock,
  Printer,
  RefreshCw,
  Search,
  Send,
  Trash2,
  ZoomIn,
  ZoomOut,
  RotateCcw,
  X,
} from "lucide-react";
import Link from "next/link";
import { useEffect, useRef, useState } from "react";
import { toast } from "sonner";
import { Button } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import {
  useBulkDailyReportWorkflow,
  useBulkDeleteDailyReports,
  usePasteDailyReports,
  useDailyReportExport,
  useDailyReportLookups,
  useDailyReports,
  useDailyReportWorkflow,
  useDeleteDailyReport,
  useDuplicateDailyReport,
  useQueueDailyReportExport,
} from "../hooks";
import { downloadDailyReportExport, printDailyReport } from "../services";
import type { DailyReport, DailyReportStatus } from "../types";
import type { DailyReportPayload } from "../services";

type Props = { onEdit?: (report: DailyReport) => void };
type ReportPeriod = "daily" | "weekly" | "monthly" | "yearly";
type DeleteRequest = { ids: number[]; label: string };
type SortField =
  | "production_date"
  | "po_number"
  | "output_box"
  | "output_pcs"
  | "quantity_defect";
const selectClass =
  "h-10 rounded-lg border border-slate-200 bg-transparent px-3 text-sm dark:border-slate-700";
const statusLabel: Record<DailyReportStatus, string> = {
  draft: "Draft",
  submitted: "Submitted",
  reviewed: "Reviewed",
  locked: "Locked",
  cancelled: "Cancelled",
};

const dateString = (date: Date) => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

const periodDates = (period: ReportPeriod, referenceDate: Date) => {
  const from = new Date(referenceDate);
  const to = new Date(referenceDate);
  if (period === "weekly") {
    const mondayOffset = (referenceDate.getDay() + 6) % 7;
    from.setDate(referenceDate.getDate() - mondayOffset);
    to.setDate(from.getDate() + 6);
  } else if (period === "monthly") {
    from.setDate(1);
    to.setMonth(referenceDate.getMonth() + 1, 0);
  } else if (period === "yearly") {
    from.setMonth(0, 1);
    to.setMonth(11, 31);
  }
  return { production_date_from: dateString(from), production_date_to: dateString(to) };
};

export function DailyReportTable({ onEdit }: Props) {
  const [searchInput, setSearchInput] = useState("");
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const [pageSize, setPageSize] = useState(50);
  const [sort, setSort] = useState<SortField>("production_date");
  const [direction, setDirection] = useState<"asc" | "desc">("desc");
  const [exportId, setExportId] = useState<number>();
  const [selectedIds, setSelectedIds] = useState<number[]>([]);
  const [copiedReports, setCopiedReports] = useState<DailyReport[]>([]);
  const [tableZoom, setTableZoom] = useState(100);
  const [filters, setFilters] = useState<{
    period: ReportPeriod;
    plant_id?: number;
    shift_id?: number;
    machine_id?: number;
    product_id?: number;
    product_type?: "FG" | "WIP";
    production_date_from?: string;
    production_date_to?: string;
    result?: string;
    status?: DailyReportStatus;
  }>({ period: "monthly" });
  const { data: lookups } = useDailyReportLookups();
  const periodRange = periodDates(filters.period, new Date());
  const requestFilters = filters.production_date_from || filters.production_date_to
    ? filters
    : { ...filters, ...periodRange };
  const { data, isLoading, isError, isFetching, refetch } = useDailyReports({
    ...requestFilters,
    search,
    sort,
    direction,
    page,
    per_page: pageSize,
  });
  const deleteMutation = useDeleteDailyReport();
  const bulkDelete = useBulkDeleteDailyReports();
  const pasteReports = usePasteDailyReports();
  const [deleteRequest, setDeleteRequest] = useState<DeleteRequest | null>(null);
  const tableScrollRef = useRef<HTMLDivElement>(null);
  const workflow = useDailyReportWorkflow();
  const bulkWorkflow = useBulkDailyReportWorkflow();
  const duplicate = useDuplicateDailyReport();
  const queueExport = useQueueDailyReportExport();
  const exportStatus = useDailyReportExport(exportId);
  useEffect(() => {
    const timer = window.setTimeout(() => {
      setSearch(searchInput);
      setPage(1);
    }, 300);
    return () => window.clearTimeout(timer);
  }, [searchInput]);
  const setPeriod = (period: ReportPeriod) => {
    setSelectedIds([]);
    setFilters((current) => ({ ...current, period }));
    setPage(1);
  };
  const setDateFilter = (name: "production_date_from" | "production_date_to", value: string) => {
    setSelectedIds([]);
    setFilters((current) => ({ ...current, [name]: value || undefined }));
    setPage(1);
  };
  const setFilter = (name: Exclude<keyof typeof filters, "period" | "production_date_from" | "production_date_to">, value: string) => {
    setSelectedIds([]);
    setFilters((current) => ({
      ...current,
      [name]: value ? ["status", "result", "product_type"].includes(name) ? value : Number(value) : undefined,
    }));
    setPage(1);
  };
  const sortBy = (field: SortField) => {
    setSelectedIds([]);
    if (sort === field)
      setDirection((value) => (value === "asc" ? "desc" : "asc"));
    else {
      setSort(field);
      setDirection("desc");
    }
    setPage(1);
  };
  const icon = (field: SortField) =>
    sort !== field ? null : direction === "asc" ? (
      <ChevronUp className="size-3" />
    ) : (
      <ChevronDown className="size-3" />
    );
  const machines =
    lookups?.machines.filter(
      (machine) => !filters.plant_id || machine.plant_id === filters.plant_id,
    ) ?? [];
  const rows = data?.data ?? [];
  const selectedRows = rows.filter((row) => selectedIds.includes(row.id));
  const toggleSelected = (id: number) =>
    setSelectedIds((current) =>
      current.includes(id)
        ? current.filter((item) => item !== id)
        : [...current, id],
    );
  const togglePage = () =>
    setSelectedIds((current) => {
      const pageIds = rows.map((row) => row.id);
      const pageSelected = pageIds.every((id) => current.includes(id));
      return pageSelected
        ? current.filter((id) => !pageIds.includes(id))
        : [...new Set([...current, ...pageIds])];
    });
  const runBulkWorkflow = (action: "submit" | "review" | "lock") => {
    const eligibleStatus = { submit: "draft", review: "submitted", lock: "reviewed" }[action];
    const eligibleIds = selectedRows.filter((row) => row.status === eligibleStatus).map((row) => row.id);
    if (
      eligibleIds.length &&
      window.confirm(
        `Apply ${action} to ${eligibleIds.length} selected report(s)?`,
      )
    )
      bulkWorkflow.mutate(
        { ids: eligibleIds, action },
        { onSuccess: () => setSelectedIds([]) },
      );
  };
  const runBulkDelete = () => {
    const draftIds = selectedRows.filter((row) => row.status === "draft").map((row) => row.id);
    if (draftIds.length) setDeleteRequest({ ids: draftIds, label: `${draftIds.length} selected report(s)` });
  };
  const copySelectedReports = async () => {
    setCopiedReports(selectedRows);
    if (navigator.clipboard?.writeText) {
      await navigator.clipboard.writeText(JSON.stringify({ type: "qa-system-daily-reports", reports: selectedRows }));
    }
    toast.success(`${selectedRows.length} report(s) copied.`);
  };
  const pasteReportsForToday = async () => {
    try {
      let reports = copiedReports;
      if (!reports.length && navigator.clipboard?.readText) {
        const clipboard = JSON.parse(await navigator.clipboard.readText()) as { type?: string; reports?: DailyReport[] };
        if (clipboard.type === "qa-system-daily-reports" && Array.isArray(clipboard.reports)) reports = clipboard.reports;
      }
      if (!reports.length) throw new Error("Copy report terlebih dahulu sebelum Paste.");
      const productionDate = dateString(new Date());
      const payloads: DailyReportPayload[] = reports.map((report) => ({
        plant_id: report.plant.id,
        machine_id: report.machine.id,
        shift_id: report.shift.id,
        product_id: report.product.id,
        product_type: report.product_type,
        checker_id: report.checker.id,
        checker_2_id: report.checker_2?.id ?? undefined,
        po_number: report.po_number,
        output_box: report.output_box,
        production_date: productionDate,
        defects: report.defects.filter((defect) => defect.quantity !== null && defect.quantity > 0).map((defect) => ({ defect_id: defect.defect_id, quantity: defect.quantity as number, remarks: defect.remarks ?? undefined })),
        remarks: report.remarks ?? undefined,
        result: report.result ?? undefined,
        finding_range_box: report.finding_range_box ?? undefined,
      }));
      await pasteReports.mutateAsync(payloads);
      setSelectedIds([]);
      toast.success(`${payloads.length} report(s) pasted for ${productionDate}.`);
    } catch (error) {
      toast.error(error instanceof Error ? error.message : "Unable to paste daily reports.");
    }
  };
  const confirmDelete = () => {
    if (!deleteRequest) return;
    const request = deleteRequest;
    setDeleteRequest(null);
    if (request.ids.length > 1) bulkDelete.mutate(request.ids, { onSuccess: () => setSelectedIds([]) });
    else deleteMutation.mutate(request.ids[0]);
  };
  const scrollTable = (direction: "left" | "right") => {
    tableScrollRef.current?.scrollBy({ left: direction === "right" ? 640 : -640, behavior: "smooth" });
  };
  const print = async (id: number) => {
    const blob = await printDailyReport(id);
    window.open(URL.createObjectURL(blob), "_blank");
  };
  const exportReports = () =>
    queueExport.mutate(requestFilters, {
      onSuccess: (exportRecord) => setExportId(exportRecord.id),
    });
  const downloadExport = async (id: number) => {
    const blob = await downloadDailyReportExport(id);
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = `daily-reports-${id}.xlsx`;
    link.click();
    URL.revokeObjectURL(url);
  };
  return (
    <section className="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div className="flex flex-col gap-3 border-b border-slate-200 p-5 dark:border-slate-800">
        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 className="text-base font-semibold">Daily QA history</h2>
            <p className="mt-1 text-sm text-muted-foreground">
              {data?.total ?? 0} records · server-side workflow history
            </p>
          </div>
          <div className="flex items-center gap-2">
            <Button type="button" variant="outline" onClick={() => refetch()} disabled={isFetching} title="Refresh daily report table" aria-label="Refresh daily report table">
              <RefreshCw className={`size-4 ${isFetching ? "animate-spin" : ""}`} /> Refresh
            </Button>
            <Button
              type="button"
              variant="outline"
              onClick={exportReports}
              disabled={queueExport.isPending}
            >
              <FileDown className="size-4" />{" "}
              {queueExport.isPending ? "Queueing..." : "Export Excel"}
            </Button>
          </div>
        </div>
        <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-6">
          <select className={selectClass} value={filters.period} onChange={(event) => setPeriod(event.target.value as ReportPeriod)} aria-label="Report period"><option value="daily">Daily</option><option value="weekly">Weekly</option><option value="monthly">Monthly</option><option value="yearly">Yearly</option></select>
          <input type="date" className={selectClass} value={filters.production_date_from ?? ""} onChange={(event) => setDateFilter("production_date_from", event.target.value)} aria-label="Date from" />
          <input type="date" className={selectClass} value={filters.production_date_to ?? ""} min={filters.production_date_from} onChange={(event) => setDateFilter("production_date_to", event.target.value)} aria-label="Date to" />
          <label className="flex h-10 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm dark:border-slate-700 lg:col-span-2">
            <Search className="size-4 text-muted-foreground" />
            <input
              className="w-full bg-transparent outline-none"
              placeholder="Search MM, item, PO, machine..."
              value={searchInput}
              onChange={(event) => setSearchInput(event.target.value)}
            />
          </label>
          <select
            className={selectClass}
            value={filters.plant_id ?? ""}
            onChange={(event) => setFilter("plant_id", event.target.value)}
          >
            <option value="">All plants</option>
            {lookups?.plants.map((item) => (
              <option key={item.id} value={item.id}>
                {item.code} — {item.name}
              </option>
            ))}
          </select>
          <select
            className={selectClass}
            value={filters.shift_id ?? ""}
            onChange={(event) => setFilter("shift_id", event.target.value)}
          >
            <option value="">All shifts</option>
            {lookups?.shifts.map((item) => (
              <option key={item.id} value={item.id}>
                {item.name}
              </option>
            ))}
          </select>
          <select
            className={selectClass}
            value={filters.machine_id ?? ""}
            onChange={(event) => setFilter("machine_id", event.target.value)}
          >
            <option value="">All machines</option>
            {machines.map((item) => (
              <option key={item.id} value={item.id}>
                {item.code} — {item.name}
              </option>
            ))}
          </select>
          <select
            className={selectClass}
            value={filters.product_type ?? ""}
            onChange={(event) => setFilter("product_type", event.target.value)}
          >
            <option value="">All product types</option>
            <option value="FG">FG</option>
            <option value="WIP">WIP</option>
          </select>
          <select className={selectClass} value={filters.result ?? ""} onChange={(event) => setFilter("result", event.target.value)}>
            <option value="">All results</option>
            <option value="OK">OK</option>
            <option value="OK, WITH NOTED">OK, WITH NOTED</option>
            <option value="SORTIR">SORTIR</option>
            <option value="REJECT">REJECT</option>
          </select>
          <select
            className={selectClass}
            value={filters.status ?? ""}
            onChange={(event) => setFilter("status", event.target.value)}
          >
            <option value="">All statuses</option>
            {Object.entries(statusLabel).map(([value, label]) => (
              <option key={value} value={value}>
                {label}
              </option>
            ))}
          </select>
        </div>
      </div>
      {exportStatus.data?.status === "completed" && (
        <div className="border-b bg-emerald-50 px-5 py-3 text-sm text-emerald-700">
          Export ready.{" "}
          <button type="button" className="font-medium underline" onClick={() => { const id = exportStatus.data?.id; if (id) void downloadExport(id); }}>
            Download Excel
          </button>
        </div>
      )}
      {exportStatus.data?.status === "failed" && (
        <div className="border-b bg-rose-50 px-5 py-3 text-sm text-rose-700">
          Export failed: {exportStatus.data.error_message}
        </div>
      )}
      {selectedRows.length > 0 && (
        <div className="flex flex-wrap items-center gap-2 border-b border-slate-200 bg-slate-50 px-5 py-3 dark:border-slate-800 dark:bg-slate-950/40">
          <span className="mr-2 text-sm font-medium">{selectedRows.length} selected</span>
          <Button type="button" size="sm" variant="outline" onClick={() => void copySelectedReports()}><Copy className="size-4" /> Copy</Button>
          <Button type="button" size="sm" variant="outline" onClick={() => void pasteReportsForToday()} disabled={pasteReports.isPending}><ClipboardPaste className="size-4" /> {pasteReports.isPending ? "Pasting..." : "Paste today"}</Button>
          <Button type="button" size="sm" variant="outline" onClick={() => runBulkWorkflow("submit")} disabled={bulkWorkflow.isPending || !selectedRows.some((row) => row.status === "draft")}><Send className="size-4" /> Submit</Button>
          <Button type="button" size="sm" variant="outline" onClick={() => runBulkWorkflow("review")} disabled={bulkWorkflow.isPending || !selectedRows.some((row) => row.status === "submitted")}><Check className="size-4" /> Review</Button>
          <Button type="button" size="sm" variant="outline" onClick={() => runBulkWorkflow("lock")} disabled={bulkWorkflow.isPending || !selectedRows.some((row) => row.status === "reviewed")}><Lock className="size-4" /> Lock</Button>
          <Button type="button" size="sm" variant="outline" className="text-rose-600" onClick={runBulkDelete} disabled={bulkDelete.isPending || !selectedRows.some((row) => row.status === "draft")}><Trash2 className="size-4" /> Delete</Button>
        </div>
      )}
      {isLoading ? (
        <div className="space-y-3 p-5" aria-label="Loading Daily QA history">
          {Array.from({ length: 5 }, (_, index) => <Skeleton key={index} className="h-12 w-full" />)}
        </div>
      ) : isError ? (
        <p className="p-6 text-sm text-destructive">
          Unable to load Daily QA history.
        </p>
      ) : !rows.length ? (
        <p className="p-10 text-center text-sm text-muted-foreground">
          No Daily QA reports match the selected filters.
        </p>
      ) : (
        <>
          <div className="flex items-center justify-between border-b border-slate-200 px-4 py-2 dark:border-slate-800">
            <p className="text-xs text-muted-foreground">Daily report columns</p>
            <div className="flex items-center gap-1">
              <Button type="button" variant="outline" size="icon-sm" onClick={() => scrollTable("left")} aria-label="Scroll table left" title="Scroll table left"><ChevronLeft className="size-4" /></Button>
              <Button type="button" variant="outline" size="icon-sm" onClick={() => scrollTable("right")} aria-label="Scroll table right" title="Scroll table right"><ChevronRight className="size-4" /></Button>
              <span className="ml-2 min-w-12 text-center text-xs text-muted-foreground" aria-live="polite">{tableZoom}%</span>
              <Button type="button" variant="outline" size="icon-sm" onClick={() => setTableZoom((value) => Math.max(70, value - 10))} disabled={tableZoom <= 70} aria-label="Zoom out table" title="Zoom out table"><ZoomOut className="size-4" /></Button>
              <Button type="button" variant="outline" size="icon-sm" onClick={() => setTableZoom((value) => Math.min(100, value + 10))} disabled={tableZoom >= 100} aria-label="Zoom in table" title="Zoom in table"><ZoomIn className="size-4" /></Button>
              <Button type="button" variant="outline" size="icon-sm" onClick={() => setTableZoom(100)} disabled={tableZoom === 100} aria-label="Reset table zoom" title="Reset table zoom"><RotateCcw className="size-4" /></Button>
            </div>
          </div>
          <div ref={tableScrollRef} className="overflow-x-auto" onWheel={(event) => { if (event.deltaY !== 0 && event.deltaX === 0) event.currentTarget.scrollLeft += event.deltaY; }}>
            <div style={{ zoom: `${tableZoom}%` }}>
            <table className="w-full min-w-[1900px] text-left text-sm">
              <thead className="bg-slate-50 text-xs uppercase tracking-wide text-muted-foreground dark:bg-slate-950/40">
                <tr>
                  <th className="w-12 px-4 py-3"><input type="checkbox" aria-label="Select all reports on this page" checked={rows.length > 0 && rows.every((row) => selectedIds.includes(row.id))} onChange={togglePage} /></th>
                  <th className="px-4 py-3">
                    <button
                      type="button"
                      className="flex items-center gap-1"
                      onClick={() => sortBy("production_date")}
                    >
                      Production Date {icon("production_date")}
                    </button>
                  </th>
                  <th className="px-4 py-3">Shift</th>
                  <th className="px-4 py-3">Machine</th>
                  <th className="px-4 py-3">Product Type</th>
                  <th className="px-4 py-3">MM Number</th>
                  <th className="px-4 py-3">Item Name</th>
                  <th className="px-4 py-3">PO Number</th>
                  <th className="px-4 py-3 text-right">Qty / Box</th>
                  <th className="px-4 py-3 text-right">
                    <button
                      type="button"
                      className="ml-auto flex items-center gap-1"
                      onClick={() => sortBy("output_pcs")}
                    >
                      Output PCS {icon("output_pcs")}
                    </button>
                  </th>
                  <th className="px-4 py-3 text-right">
                    <button
                      type="button"
                      className="ml-auto flex items-center gap-1"
                      onClick={() => sortBy("output_box")}
                    >
                      Output Box {icon("output_box")}
                    </button>
                  </th>
                  <th className="px-4 py-3">Findings Range (Box)</th>
                  <th className="px-4 py-3 text-right">Findings Quantity (PCS)</th>
                  <th className="px-4 py-3">Defect Description</th>
                  <th className="px-4 py-3">Defect Category</th>
                  <th className="px-4 py-3">Result</th>
                  <th className="px-4 py-3">Checked by QA 1</th>
                  <th className="px-4 py-3">Checked by QA 2</th>
                  <th className="px-4 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                {rows.map((report) => (
                  <tr key={report.id} className={report.output_box === 0 ? "bg-red-50 dark:bg-red-950/30" : undefined}>
                    <td className="px-4 py-4"><input type="checkbox" aria-label={`Select report ${report.po_number}`} checked={selectedIds.includes(report.id)} onChange={() => toggleSelected(report.id)} /></td>
                    <td className="px-4 py-4 text-muted-foreground">
                      {report.production_date}
                    </td>
                    <td className="px-4 py-4">{report.shift.name}</td>
                    <td className="px-4 py-4">{report.machine.name}</td>
                    <td className="px-4 py-4">{report.product_type}</td>
                    <td className="px-4 py-4">{report.mm_number}</td>
                    <td className="px-4 py-4">
                      {report.product.name || report.product.mm_number}
                    </td>
                    <td className="px-4 py-4">{report.po_number}</td>
                    <td className="px-4 py-4 text-right">{report.qty_per_box.toLocaleString()}</td>
                    <td className="px-4 py-4 text-right font-semibold text-emerald-700">
                      {report.output_pcs.toLocaleString()}
                    </td>
                    <td className="px-4 py-4 text-right">
                      {report.output_box.toLocaleString()}
                    </td>
                    <td className="px-4 py-4">{report.finding_range_box ?? "-"}</td>
                    <td className="px-4 py-4 text-right">{report.total_defect.toLocaleString()}</td>
                    <td className="px-4 py-4">{report.defects.map((item) => item.remarks || item.defect.description || item.defect.name).filter(Boolean).join(", ") || "-"}</td>
                    <td className="px-4 py-4">{report.defects.map((item) => item.defect.category).filter(Boolean).join(", ") || "-"}</td>
                    <td className="px-4 py-4">{report.result ?? "-"}</td>
                    <td className="px-4 py-4">{report.checker.name ?? "-"}</td>
                    <td className="px-4 py-4">{report.qa_checker_2?.name ?? "-"}</td>
                    <td className="px-4 py-4">
                      <div className="flex justify-end gap-1">
                        <Link
                          href={`/daily-reports/${report.id}`}
                          className="inline-flex size-7 items-center justify-center rounded-md hover:bg-muted"
                          aria-label="View detail"
                        >
                          <Eye className="size-4" />
                        </Link>
                        {report.status === "draft" && onEdit && (
                          <Button
                            variant="ghost"
                            size="icon-sm"
                            onClick={() => onEdit(report)}
                            aria-label="Edit report"
                          >
                            <Edit2 className="size-4" />
                          </Button>
                        )}
                        <Button
                          variant="ghost"
                          size="icon-sm"
                          onClick={() => duplicate.mutate(report.id)}
                          disabled={duplicate.isPending}
                          aria-label="Duplicate report"
                        >
                          <Copy className="size-4" />
                        </Button>
                        <Button
                          variant="ghost"
                          size="icon-sm"
                          onClick={() => print(report.id)}
                          aria-label="Print report"
                        >
                          <Printer className="size-4" />
                        </Button>
                        {report.status === "draft" && (
                          <Button
                            variant="ghost"
                            size="icon-sm"
                            onClick={() =>
                              workflow.mutate({
                                id: report.id,
                                action: "submit",
                              })
                            }
                            aria-label="Submit report"
                          >
                            <Send className="size-4" />
                          </Button>
                        )}
                        {report.status === "submitted" && (
                          <>
                            <Button
                              variant="ghost"
                              size="icon-sm"
                              onClick={() =>
                                workflow.mutate({
                                  id: report.id,
                                  action: "review",
                                })
                              }
                              aria-label="Review report"
                            >
                              <Check className="size-4" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="icon-sm"
                              onClick={() =>
                                workflow.mutate({
                                  id: report.id,
                                  action: "reject",
                                })
                              }
                              aria-label="Reject report"
                            >
                              <X className="size-4" />
                            </Button>
                          </>
                        )}
                        {report.status === "reviewed" && (
                          <>
                            <Button
                              variant="ghost"
                              size="icon-sm"
                              onClick={() =>
                                workflow.mutate({
                                  id: report.id,
                                  action: "approve",
                                })
                              }
                              aria-label="Approve report"
                            >
                              <Check className="size-4" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="icon-sm"
                              onClick={() =>
                                workflow.mutate({
                                  id: report.id,
                                  action: "lock",
                                })
                              }
                              aria-label="Lock report"
                            >
                              <Lock className="size-4" />
                            </Button>
                          </>
                        )}
                        {report.status === "draft" && (
                          <Button
                            variant="ghost"
                            size="icon-sm"
                            className="text-rose-600"
                            onClick={() => setDeleteRequest({ ids: [report.id], label: "this Daily QA Report" })}
                            disabled={deleteMutation.isPending}
                            aria-label="Delete report"
                          >
                            <Trash2 className="size-4" />
                          </Button>
                        )}
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
            </div>
          </div>
          <div className="flex items-center justify-between border-t border-slate-200 px-5 py-3 dark:border-slate-800">
            <p className="text-xs text-muted-foreground">
              Page {data?.current_page ?? 1} of {data?.last_page ?? 1}
            </p>
            <div className="flex items-center gap-2">
              <select className={selectClass} value={pageSize} onChange={(event) => { setPageSize(Number(event.target.value)); setPage(1); setSelectedIds([]); }} aria-label="Rows per page">
                {[20, 50, 100].map((size) => <option key={size} value={size}>{size} / page</option>)}
              </select>
              <Button
                variant="outline"
                size="sm"
                disabled={!data || data.current_page <= 1}
                onClick={() => { setSelectedIds([]); setPage((value) => value - 1); }}
              >
                <ChevronLeft className="size-4" /> Previous
              </Button>
              <Button
                variant="outline"
                size="sm"
                disabled={!data || data.current_page >= data.last_page}
                onClick={() => { setSelectedIds([]); setPage((value) => value + 1); }}
              >
                Next <ChevronRight className="size-4" />
              </Button>
            </div>
          </div>
        </>
      )}
      {deleteRequest && (
        <div className="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="delete-daily-report-title">
          <div className="w-full max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900">
            <h2 id="delete-daily-report-title" className="text-lg font-semibold">Delete daily report?</h2>
            <p className="mt-2 text-sm text-muted-foreground">Are you sure you want to delete {deleteRequest.label}? This action cannot be undone.</p>
            <div className="mt-6 flex justify-end gap-2">
              <Button type="button" variant="outline" onClick={() => setDeleteRequest(null)} disabled={deleteMutation.isPending || bulkDelete.isPending}>Cancel</Button>
              <Button type="button" className="bg-rose-600 text-white hover:bg-rose-700" onClick={confirmDelete} disabled={deleteMutation.isPending || bulkDelete.isPending}><Trash2 className="size-4" /> Delete</Button>
            </div>
          </div>
        </div>
      )}
    </section>
  );
}

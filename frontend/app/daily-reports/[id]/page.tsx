"use client";

import Link from "next/link";
import { ArrowLeft, ClipboardCheck, Copy, Edit2, Lock, Printer } from "lucide-react";
import { useParams, useRouter } from "next/navigation";
import { Button } from "@/components/ui/button";
import { PageSkeleton } from "@/components/layouts/page-skeleton";
import { useDailyReport, useDailyReportWorkflow, useDuplicateDailyReport } from "@/features/daily-reports/hooks";

type DetailField = { label: string; value: string; wide?: boolean; status?: boolean };

export default function DailyReportDetailPage() {
  const params = useParams<{ id: string }>();
  const router = useRouter();
  const id = Number(params.id);
  const { data: report, isLoading } = useDailyReport(id);
  const workflow = useDailyReportWorkflow();
  const duplicate = useDuplicateDailyReport();

  if (isLoading) return <PageSkeleton variant="detail" />;
  if (!report) return <main className="p-8 text-sm text-destructive">Report not found.</main>;

  const print = async () => {
    const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL ?? "/api/v1"}/daily-reports/${id}/print`, { headers: { Authorization: `Bearer ${localStorage.getItem("qms_token") ?? ""}` } });
    window.open(URL.createObjectURL(await response.blob()), "_blank");
  };

  const fields: DetailField[] = [
    { label: "Plant", value: report.plant.name },
    { label: "Machine", value: report.machine.name },
    { label: "Shift", value: report.shift.name },
    { label: "Product Type", value: report.product_type },
    { label: "Defect Category", value: report.defects.map((item) => item.defect.category).filter(Boolean).join(", ") || "-" },
    { label: "Product MM Number", value: report.mm_number },
    { label: "Item Name", value: report.product.name || "-", wide: true },
    { label: "PO Number", value: report.po_number },
    { label: "QA Checker", value: report.checker.name },
    { label: "Checked by QA 2", value: report.qa_checker_2?.name ?? "-" },
    { label: "Output Box", value: report.output_box.toLocaleString() },
    { label: "Qty per Box", value: report.qty_per_box.toLocaleString() },
    { label: "Output PCS", value: report.output_pcs.toLocaleString() },
    { label: "Findings Range (Box)", value: report.finding_range_box ?? "-" },
    { label: "Findings Quantity (PCS)", value: report.total_defect.toLocaleString() },
    { label: "Remarks", value: report.remarks ?? "-", wide: true },
    { label: "Result", value: report.result ?? "-" },
    { label: "Status", value: report.status, status: true },
  ];

  return (
    <main className="min-h-screen bg-slate-50 px-4 py-6 dark:bg-slate-950 sm:px-6 lg:px-10 lg:py-8">
      <div className="mx-auto max-w-6xl">
        <div className="mb-6 mt-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p className="flex items-center gap-2 text-sm font-medium text-emerald-600"><span className="flex size-7 items-center justify-center rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300"><ClipboardCheck className="size-4" /></span> Daily QA Report</p>
            <h1 className="mt-2 text-3xl font-semibold tracking-tight">Report #{report.id}</h1>
            <p className="mt-2 text-sm text-muted-foreground">{report.production_date} <span className="px-1.5">·</span> {report.po_number}</p>
          </div>
          <div className="flex flex-wrap gap-2">
            <Link href="/daily-reports" className="inline-flex h-9 items-center gap-1.5 rounded-lg border px-3 text-sm font-medium transition-colors hover:bg-muted"><ArrowLeft className="size-4" /> History</Link>
            {report.status === "draft" && <Link href={`/daily-reports/${id}/edit`} className="inline-flex h-9 items-center gap-1.5 rounded-lg border px-3 text-sm font-medium transition-colors hover:bg-muted"><Edit2 className="size-4" /> Edit</Link>}
            <Button variant="outline" onClick={print}><Printer className="size-4" /> Print</Button>
            <Button variant="outline" onClick={() => duplicate.mutate(id, { onSuccess: (copy) => router.push(`/daily-reports/${copy.id}`) })}><Copy className="size-4" /> Duplicate</Button>
          </div>
        </div>

        <section className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
          <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            {fields.map((field) => (
              <article key={field.label} className={`min-w-0 rounded-lg border border-slate-200 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-950/30 ${field.wide ? "xl:col-span-2" : ""}`}>
                <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">{field.label}</p>
                {field.status ? (
                  <span className="mt-2 inline-flex rounded-full bg-slate-200 px-2.5 py-1 text-sm font-semibold capitalize text-slate-700 dark:bg-slate-800 dark:text-slate-200">{field.value}</span>
                ) : (
                  <p className={`mt-2 min-w-0 font-semibold text-slate-950 dark:text-white ${field.wide ? "break-words leading-6" : "truncate"}`} title={field.value}>{field.value}</p>
                )}
              </article>
            ))}
          </div>

          <div className="mt-8 border-t border-slate-200 pt-6 dark:border-slate-800">
            <div className="flex items-center justify-between gap-3"><h2 className="text-lg font-semibold">Defects</h2><span className="text-xs text-muted-foreground">{report.defects.length} recorded</span></div>
            <div className="mt-4 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
              <table className="w-full min-w-[620px] text-left text-sm">
                <thead className="bg-slate-50 text-xs uppercase tracking-wide text-muted-foreground dark:bg-slate-950/50"><tr><th className="px-4 py-3 font-medium">Defect</th><th className="px-4 py-3 font-medium">Category</th><th className="px-4 py-3 text-right font-medium">Quantity</th><th className="px-4 py-3 font-medium">Remarks</th></tr></thead>
                <tbody>{report.defects.map((item) => <tr key={item.id} className="border-t border-slate-200 dark:border-slate-800"><td className="px-4 py-3 font-medium">{item.remarks || item.defect.description || item.defect.name}</td><td className="px-4 py-3 text-muted-foreground">{item.defect.category}</td><td className="px-4 py-3 text-right font-semibold tabular-nums">{item.quantity?.toLocaleString() ?? "-"}</td><td className="px-4 py-3 text-muted-foreground">{item.remarks || "-"}</td></tr>)}</tbody>
              </table>
            </div>
          </div>

          <div className="mt-6 flex flex-wrap gap-2">
            {report.status === "draft" && <Button onClick={() => workflow.mutate({ id, action: "lock" })}><Lock className="size-4" /> Lock</Button>}
          </div>
        </section>
      </div>
    </main>
  );
}

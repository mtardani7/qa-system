"use client";

import { CheckCircle2, Download, Upload, X } from "lucide-react";
import { useEffect, useState } from "react";
import { Button } from "@/components/ui/button";
import { useDailyReportImport, useDailyReportLookups, useImportDailyReports } from "../hooks";
import { downloadDailyReportImportTemplate } from "../services";
import { SearchableSelect } from "./searchable-select";

type Props = { open: boolean; onClose: () => void };

export function DailyReportImportDialog({ open, onClose }: Props) {
  const { data } = useDailyReportLookups();
  const mutation = useImportDailyReports();
  const [plantId, setPlantId] = useState<number>();
  const [file, setFile] = useState<File>();
  const [importId, setImportId] = useState<number>();
  const [progress, setProgress] = useState(0);
  const [error, setError] = useState("");
  const status = useDailyReportImport(importId);
  const isProcessing = mutation.isPending || status.data?.status === "queued" || status.data?.status === "processing";
  const completed = status.data?.status === "completed" || status.data?.status === "completed_with_errors";

  useEffect(() => {
    if (!open) {
      setFile(undefined);
      setPlantId(undefined);
      setImportId(undefined);
      setProgress(0);
      setError("");
    }
  }, [open]);

  if (!open) return null;

  const submit = () => {
    if (!file || !plantId) return;
    setError("");
    setProgress(0);
    mutation.mutate({ file, plantId, onUploadProgress: setProgress }, {
      onSuccess: (result) => { setProgress(100); setImportId(result.id); },
      onError: (requestError: unknown) => {
        const response = requestError as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
        setError(Object.values(response.response?.data?.errors ?? {})[0]?.[0] ?? response.response?.data?.message ?? "Daily Report import failed.");
      },
    });
  };

  return <div className="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="daily-report-import-title"><div className="w-full max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900"><div className="flex items-start justify-between"><div><h2 id="daily-report-import-title" className="text-lg font-semibold">Import Daily QA Report</h2><p className="mt-1 text-sm text-muted-foreground">Use the Daily Report template. Plant is selected here because it is not stored in the workbook.</p></div><button type="button" onClick={onClose} disabled={isProcessing} aria-label="Close import dialog"><X className="size-5" /></button></div>{completed ? <div className="py-8 text-center"><CheckCircle2 className={`mx-auto size-12 ${status.data?.status === "completed" ? "text-emerald-500" : "text-amber-500"}`} /><p className="mt-3 font-semibold">{status.data?.status === "completed" ? "Daily Reports imported successfully." : "Import completed with errors."}</p><p className="mt-2 text-sm text-muted-foreground">Processed: {status.data?.processed_rows ?? 0} | Imported: {status.data?.imported_reports ?? 0} | Failed: {status.data?.failed_rows ?? 0}</p><Button type="button" className="mt-6" onClick={() => window.location.reload()}>OK</Button></div> : <><div className="mt-5 grid gap-4 sm:grid-cols-2"><SearchableSelect label="Plant" value={plantId} options={data?.plants ?? []} placeholder="Select plant" onChange={setPlantId} /><label className="block text-sm font-medium">Excel file<input type="file" accept=".xlsx,.xls" className="mt-1.5 block w-full rounded-lg border p-2 text-sm" onChange={(event) => setFile(event.target.files?.[0])} /></label></div><div className="mt-4 flex justify-end"><Button type="button" variant="outline" onClick={() => void downloadDailyReportImportTemplate()}><Download className="size-4" /> Download Template</Button></div>{file && <div className="mt-5 rounded-lg border border-slate-200 p-4 dark:border-slate-700"><p className="truncate text-sm font-medium">{file.name}</p><p className="mt-1 text-xs text-muted-foreground">{(file.size / 1024).toFixed(1)} KB</p></div>}{isProcessing && <div className="mt-5"><div className="flex justify-between text-xs text-muted-foreground"><span>{status.data?.status === "processing" ? "Processing reports..." : "Uploading..."}</span><span>{progress}%</span></div><div className="mt-2 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700"><div className="h-2 rounded-full bg-emerald-500 transition-all" style={{ width: `${progress}%` }} /></div></div>}{status.data?.status === "failed" && <p className="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">{status.data.error_message ?? "Daily Report import failed."}</p>}{error && <p className="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">{error}</p>}<div className="mt-6 flex justify-end gap-2"><Button type="button" variant="outline" onClick={onClose} disabled={isProcessing}>Cancel</Button><Button type="button" onClick={submit} disabled={!file || !plantId || isProcessing}><Upload className="size-4" />{isProcessing ? "Processing..." : "Start import"}</Button></div></>}</div></div>;
}

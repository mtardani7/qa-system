"use client";

import { Upload } from "lucide-react";
import { useState } from "react";
import { Button } from "@/components/ui/button";
import { useDailyReportImport, useDailyReportLookups, useImportDailyReports } from "../hooks";
import { SearchableSelect } from "./searchable-select";

export function DailyReportImportDialog({ open, onClose }: { open: boolean; onClose: () => void }) {
  const { data } = useDailyReportLookups();
  const mutation = useImportDailyReports();
  const [plantId, setPlantId] = useState<number>();
  const [file, setFile] = useState<File>();
  const [importId, setImportId] = useState<number>();
  const status = useDailyReportImport(importId);

  if (!open) return null;
  const submit = () => { if (file && plantId) mutation.mutate({ file, plantId }, { onSuccess: (result) => setImportId(result.id) }); };

  return <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4" role="dialog" aria-modal="true"><div className="w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900"><h2 className="text-lg font-semibold">Import Daily QA Excel</h2><p className="mt-1 text-sm text-muted-foreground">Plant dipilih karena workbook legacy tidak memiliki kolom Plant. Line tidak diperlukan.</p><div className="mt-5 space-y-4"><SearchableSelect label="Plant" value={plantId} options={data?.plants ?? []} placeholder="Select plant" onChange={setPlantId} /><label className="block text-sm font-medium">Excel file<input type="file" accept=".xlsx,.xls" className="mt-1.5 block w-full rounded-lg border p-2 text-sm" onChange={(event) => setFile(event.target.files?.[0])} /></label>{mutation.isError && <p className="text-sm text-destructive">Import could not be queued. Check the selected master data and file format.</p>}{status.data && <div className="rounded-lg border p-3 text-sm">Status: <strong>{status.data.status}</strong><br />Processed rows: {status.data.processed_rows}<br />Imported reports: {status.data.imported_reports}<br />Failed rows: {status.data.failed_rows}</div>}</div><div className="mt-6 flex justify-end gap-3"><Button type="button" variant="outline" onClick={onClose}>Close</Button><Button type="button" disabled={!file || !plantId || mutation.isPending} onClick={submit}><Upload className="size-4" />{mutation.isPending ? "Queuing..." : "Start import"}</Button></div></div></div>;
}

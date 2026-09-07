"use client";

import { CheckCircle2, Download, Upload, X } from "lucide-react";
import { useEffect, useState } from "react";
import { createPortal } from "react-dom";
import { Button } from "@/components/ui/button";
import { downloadMachineImportTemplate, importMachines } from "../services";

type Props = { open: boolean; onClose: () => void };

export function MachineImportActions() {
  const [open, setOpen] = useState(false);
  const [header, setHeader] = useState<HTMLElement | null>(null);

  useEffect(() => {
    const createButton = Array.from(document.querySelectorAll("button")).find((button) => button.textContent?.includes("Create machine"));
    const parent = createButton?.parentElement;
    if (!parent || !createButton) return;
    const mount = document.createElement("div");
    mount.className = "contents";
    parent.insertBefore(mount, createButton);
    setHeader(mount);
    return () => mount.remove();
  }, []);

  const actions = (
    <div className="flex flex-wrap gap-2">
      <Button type="button" variant="outline" onClick={() => void downloadMachineImportTemplate()}><Download className="size-4" /> Template</Button>
      <Button type="button" variant="outline" onClick={() => setOpen(true)}><Upload className="size-4" /> Import Excel</Button>
    </div>
  );

  return (
    <>
      {header ? createPortal(actions, header) : null}
      <MachineImportDialog open={open} onClose={() => setOpen(false)} />
    </>
  );
}

export function MachineImportDialog({ open, onClose }: Props) {
  const [file, setFile] = useState<File>();
  const [progress, setProgress] = useState(0);
  const [state, setState] = useState<"idle" | "uploading" | "success" | "error">("idle");
  const [error, setError] = useState("");

  if (!open) return null;

  const submit = async () => {
    if (!file) return;
    setState("uploading");
    setProgress(0);
    setError("");
    try {
      await importMachines(file, setProgress);
      setProgress(100);
      setState("success");
    } catch (requestError: unknown) {
      const response = requestError as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
      setError(Object.values(response.response?.data?.errors ?? {})[0]?.[0] ?? response.response?.data?.message ?? "Machine import failed.");
      setState("error");
    }
  };

  return (
    <div className="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="machine-import-title">
      <div className="w-full max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900">
        <div className="flex items-start justify-between">
          <div>
            <h2 id="machine-import-title" className="text-lg font-semibold">Import Machines</h2>
            <p className="mt-1 text-sm text-muted-foreground">Use: PLANT, CODE, NAME, MACHINE NUMBER, DESCRIPTION, IS ACTIVE.</p>
          </div>
          <button type="button" onClick={onClose} disabled={state === "uploading"} aria-label="Close import dialog"><X className="size-5" /></button>
        </div>
        {state === "success" ? (
          <div className="py-8 text-center">
            <CheckCircle2 className="mx-auto size-12 text-emerald-500" />
            <p className="mt-3 font-semibold">Machines imported successfully.</p>
            <Button type="button" className="mt-6" onClick={() => window.location.reload()}>OK</Button>
          </div>
        ) : (
          <>
            <div className="mt-5 flex justify-end">
              <Button type="button" variant="outline" onClick={() => void downloadMachineImportTemplate()}><Download className="size-4" /> Download Template</Button>
            </div>
            <label className="mt-5 block text-sm font-medium">Excel file
              <input type="file" accept=".xlsx,.xls" className="mt-1.5 block w-full rounded-lg border p-2 text-sm" onChange={(event) => { setFile(event.target.files?.[0]); setState("idle"); }} />
            </label>
            {file && <p className="mt-3 rounded-lg border p-3 text-sm dark:border-slate-700">{file.name} <span className="text-muted-foreground">({(file.size / 1024).toFixed(1)} KB)</span></p>}
            {state === "uploading" && <div className="mt-5"><div className="flex justify-between text-xs text-muted-foreground"><span>Uploading...</span><span>{progress}%</span></div><div className="mt-2 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700"><div className="h-2 rounded-full bg-emerald-500 transition-all" style={{ width: `${progress}%` }} /></div></div>}
            {error && <p className="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">{error}</p>}
            <div className="mt-6 flex justify-end gap-2">
              <Button type="button" variant="outline" onClick={onClose} disabled={state === "uploading"}>Cancel</Button>
              <Button type="button" onClick={() => void submit()} disabled={!file || state === "uploading"}><Upload className="size-4" />{state === "uploading" ? "Processing..." : "Start import"}</Button>
            </div>
          </>
        )}
      </div>
    </div>
  );
}

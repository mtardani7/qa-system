"use client";

import { Plus, Trash2, Upload } from "lucide-react";
import { useRef, useState } from "react";
import { Button } from "@/components/ui/button";
const Breadcrumb = () => null;
import { MasterFormDialog } from "./master-form-dialog";
import { MasterTable } from "./master-table";
import { useMasterDelete, useMasterProductImport } from "../hooks";
import type { MasterConfig, MasterRecord } from "../types";

export function MasterDataPage({ config }: { config: MasterConfig }) {
  const [open, setOpen] = useState(false);
  const [record, setRecord] = useState<MasterRecord | null>(null);
  const [toast, setToast] = useState("");
  const [deleteTarget, setDeleteTarget] = useState<MasterRecord | null>(null);
  const deleteMutation = useMasterDelete(config.module);
  const importInput = useRef<HTMLInputElement>(null);
  const importMutation = useMasterProductImport();
  const notify = (message: string) => { setToast(message); window.setTimeout(() => setToast(""), 3000); };
  const confirmDelete = () => {
    if (!deleteTarget) return;
    deleteMutation.mutate(deleteTarget.id, { onSuccess: () => { setDeleteTarget(null); notify(`${config.singular} deleted successfully.`); } });
  };
  const importProducts = (file?: File) => {
    if (!file) return;
    importMutation.mutate(file, { onSuccess: () => notify("Products imported successfully.") });
  };
  return <main className="min-h-screen bg-slate-50 px-6 py-8 lg:px-10 dark:bg-slate-950"><div className="mx-auto max-w-7xl"><Breadcrumb /><div className="mb-8 mt-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p className="text-sm font-medium text-emerald-600">Master data</p><h1 className="mt-2 text-3xl font-semibold tracking-tight">{config.title}</h1><p className="mt-2 text-sm text-muted-foreground">{config.description}</p></div><div className="flex flex-wrap gap-2">{config.module === "products" && <><input ref={importInput} type="file" accept=".xlsx,.xls" className="hidden" onChange={(event) => { importProducts(event.target.files?.[0]); event.currentTarget.value = ""; }} /><Button type="button" variant="outline" onClick={() => importInput.current?.click()} disabled={importMutation.isPending}><Upload className="size-4" />{importMutation.isPending ? "Importing..." : "Import Excel"}</Button></>}<Button onClick={() => { setRecord(null); setOpen(true); }}><Plus className="size-4" /> Create {config.singular}</Button></div></div><MasterTable config={config} onEdit={(selected) => { setRecord(selected); setOpen(true); }} onDeleteRequest={setDeleteTarget} /><MasterFormDialog config={config} open={open} record={record} onClose={() => setOpen(false)} onSaved={notify} />{deleteTarget && <div className="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/50 p-4" role="dialog" aria-modal="true" aria-labelledby="delete-master-title"><div className="w-full max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900"><h2 id="delete-master-title" className="text-lg font-semibold">Delete {config.singular}?</h2><p className="mt-2 text-sm text-muted-foreground">This action permanently deletes the selected {config.singular}. This cannot be undone.</p><div className="mt-6 flex justify-end gap-2"><Button type="button" variant="outline" onClick={() => setDeleteTarget(null)} disabled={deleteMutation.isPending}>Cancel</Button><Button type="button" variant="destructive" onClick={confirmDelete} disabled={deleteMutation.isPending}><Trash2 className="size-4" />{deleteMutation.isPending ? "Deleting..." : "Delete"}</Button></div></div></div>}{toast && <div role="status" className="fixed bottom-6 right-6 z-[60] rounded-lg bg-emerald-600 px-4 py-3 text-sm font-medium text-white shadow-lg">{toast}</div>}</div></main>;
}

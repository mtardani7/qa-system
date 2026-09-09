"use client";

import { CheckCircle2, Download, Plus, Trash2, Upload, X } from "lucide-react";
import { useRef, useState } from "react";
import { Button } from "@/components/ui/button";
const Breadcrumb = () => null;
import { MasterFormDialog } from "./master-form-dialog";
import { MasterTable } from "./master-table";
import {
  useMasterDelete,
  useMasterOptions,
  useMasterProductImport,
} from "../hooks";
import { downloadProductImportTemplate } from "../services";
import type { MasterConfig, MasterRecord } from "../types";

export function MasterDataPage({ config }: { config: MasterConfig }) {
  const [open, setOpen] = useState(false);
  const [record, setRecord] = useState<MasterRecord | null>(null);
  const [toast, setToast] = useState("");
  const [importFile, setImportFile] = useState<File | null>(null);
  const [importError, setImportError] = useState("");
  const [importProgress, setImportProgress] = useState(0);
  const [importResult, setImportResult] = useState<
    "idle" | "uploading" | "success" | "error"
  >("idle");
  const [importPlantId, setImportPlantId] = useState<number | undefined>();
  const [deleteTarget, setDeleteTarget] = useState<MasterRecord | null>(null);
  const deleteMutation = useMasterDelete(config.module);
  const importInput = useRef<HTMLInputElement>(null);
  const importMutation = useMasterProductImport();
  const plants = useMasterOptions("plants");
  const notify = (message: string) => {
    setToast(message);
    window.setTimeout(() => setToast(""), 3000);
  };
  const confirmDelete = () => {
    if (!deleteTarget) return;
    deleteMutation.mutate(deleteTarget.id, {
      onSuccess: () => {
        setDeleteTarget(null);
        notify(`${config.singular} deleted successfully.`);
      },
    });
  };
  const importProducts = (file?: File) => {
    if (!file) return;
    setImportFile(file);
    setImportError("");
    setImportProgress(0);
    setImportResult("idle");
  };
  const handlePlantChange = (event: React.ChangeEvent<HTMLSelectElement>) => {
    setImportPlantId(
      event.target.value ? Number(event.target.value) : undefined,
    );
  };
  const submitImport = () => {
    if (!importFile) return;
    setImportProgress(0);
    setImportError("");
    setImportResult("uploading");
    if (!importPlantId) {
      setImportError("Pilih plant terlebih dahulu.");
      return;
    }
    importMutation.mutate(
      {
        file: importFile,
        plantId: importPlantId,
        onUploadProgress: setImportProgress,
      },
      {
        onSuccess: () => {
          setImportProgress(100);
          setImportResult("success");
        },
        onError: (requestError: unknown) => {
          const response = requestError as {
            response?: {
              data?: { message?: string; errors?: Record<string, string[]> };
            };
          };
          const validationMessage = Object.values(
            response.response?.data?.errors ?? {},
          )[0]?.[0];
          const message =
            validationMessage ??
            response.response?.data?.message ??
            "Products import failed.";
          setImportResult("error");
          setImportError(message);
          notify(message);
        },
      },
    );
  };
  const closeImport = () => {
    if (importMutation.isPending) return;
    setImportFile(null);
    setImportError("");
    setImportResult("idle");
    setImportProgress(0);
    setImportPlantId(undefined);
  };
  const importPlantOptions = (plants.data?.data ?? []).map((plant) => (
    <option key={plant.id} value={plant.id}>
      {String(plant.code ?? "")} - {String(plant.name ?? "")}
    </option>
  ));
  return (
    <main className="min-h-screen bg-slate-50 px-6 py-8 lg:px-10 dark:bg-slate-950">
      <div className="mx-auto max-w-7xl">
        <Breadcrumb />
        <div className="mb-8 mt-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p className="text-sm font-medium text-emerald-600">Master data</p>
            <h1 className="mt-2 text-3xl font-semibold tracking-tight">
              {config.title}
            </h1>
            <p className="mt-2 text-sm text-muted-foreground">
              {config.description}
            </p>
          </div>
          <div className="flex flex-wrap gap-2">
            {config.module === "products" && (
              <>
                <select
                  aria-label="Import plant"
                  value={importPlantId ?? ""}
                  onChange={handlePlantChange}
                  className="h-10 rounded-lg border border-slate-200 bg-transparent px-3 text-sm dark:border-slate-700"
                >
                  <option value="">Select plant</option>
                  {importPlantOptions}
                </select>
                <input
                  ref={importInput}
                  type="file"
                  accept=".xlsx,.xls"
                  className="hidden"
                  onChange={(event) => {
                    importProducts(event.target.files?.[0]);
                    event.currentTarget.value = "";
                  }}
                />
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => void downloadProductImportTemplate()}
                >
                  <Download className="size-4" /> Template
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => importInput.current?.click()}
                  disabled={importMutation.isPending || !importPlantId}
                >
                  <Upload className="size-4" /> Import Excel
                </Button>
              </>
            )}
            <Button
              onClick={() => {
                setRecord(null);
                setOpen(true);
              }}
            >
              <Plus className="size-4" /> Create {config.singular}
            </Button>
          </div>
        </div>
        <MasterTable
          config={config}
          onEdit={(selected) => {
            setRecord(selected);
            setOpen(true);
          }}
          onDeleteRequest={setDeleteTarget}
        />
        <MasterFormDialog
          config={config}
          open={open}
          record={record}
          onClose={() => setOpen(false)}
          onSaved={notify}
        />
        {deleteTarget && (
          <div
            className="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-master-title"
          >
            <div className="w-full max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900">
              <h2 id="delete-master-title" className="text-lg font-semibold">
                Delete {config.singular}?
              </h2>
              <p className="mt-2 text-sm text-muted-foreground">
                This action permanently deletes the selected {config.singular}.
                This cannot be undone.
              </p>
              <div className="mt-6 flex justify-end gap-2">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => setDeleteTarget(null)}
                  disabled={deleteMutation.isPending}
                >
                  Cancel
                </Button>
                <Button
                  type="button"
                  variant="destructive"
                  onClick={confirmDelete}
                  disabled={deleteMutation.isPending}
                >
                  <Trash2 className="size-4" />
                  {deleteMutation.isPending ? "Deleting..." : "Delete"}
                </Button>
              </div>
            </div>
          </div>
        )}
        {importFile && (
          <div
            className="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="product-import-title"
          >
            <div className="w-full max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900">
              <div className="flex items-start justify-between">
                <div>
                  <h2
                    id="product-import-title"
                    className="text-lg font-semibold"
                  >
                    Import Products
                  </h2>
                  <p className="mt-1 text-sm text-muted-foreground">
                    Use the downloaded template format: NO., MM, DESCRIPTION,
                    /BOX.
                  </p>
                </div>
                <button
                  type="button"
                  onClick={closeImport}
                  disabled={importMutation.isPending}
                  aria-label="Close import dialog"
                >
                  <X className="size-5" />
                </button>
              </div>
              {importResult === "success" ? (
                <div className="py-8 text-center">
                  <CheckCircle2 className="mx-auto size-12 text-emerald-500" />
                  <p className="mt-3 font-semibold">
                    Products imported successfully.
                  </p>
                  <Button
                    type="button"
                    className="mt-6"
                    onClick={() => window.location.reload()}
                  >
                    OK
                  </Button>
                </div>
              ) : (
                <>
                  <div className="mt-6 rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                    <p className="truncate text-sm font-medium">
                      {importFile.name}
                    </p>
                    <p className="mt-1 text-xs text-muted-foreground">
                      {(importFile.size / 1024).toFixed(1)} KB
                    </p>
                  </div>
                  {importResult === "uploading" && (
                    <div className="mt-5">
                      <div className="flex justify-between text-xs text-muted-foreground">
                        <span>Uploading and processing...</span>
                        <span>{importProgress}%</span>
                      </div>
                      <div className="mt-2 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                        <div
                          className="h-2 rounded-full bg-emerald-500 transition-all"
                          style={{ width: `${importProgress}%` }}
                        />
                      </div>
                    </div>
                  )}
                  {importResult === "error" && (
                    <p className="mt-4 text-sm text-destructive">
                      Import failed. Check the notification for details.
                    </p>
                  )}
                  <div className="mt-6 flex justify-end gap-2">
                    <Button
                      type="button"
                      variant="outline"
                      onClick={closeImport}
                      disabled={importMutation.isPending}
                    >
                      Cancel
                    </Button>
                    <Button
                      type="button"
                      onClick={submitImport}
                      disabled={importMutation.isPending}
                    >
                      <Upload className="size-4" />
                      {importMutation.isPending
                        ? "Processing..."
                        : "Start import"}
                    </Button>
                  </div>
                </>
              )}
            </div>
          </div>
        )}
        {toast && (
          <div
            role="status"
            className="fixed bottom-6 right-6 z-[60] rounded-lg bg-emerald-600 px-4 py-3 text-sm font-medium text-white shadow-lg"
          >
            {toast}
          </div>
        )}
      </div>
    </main>
  );
}

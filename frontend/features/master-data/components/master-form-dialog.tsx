"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { X } from "lucide-react";
import { useEffect } from "react";
import { useForm } from "react-hook-form";
import { Button } from "@/components/ui/button";
import { useMasterCreate, useMasterOptions, useMasterUpdate } from "../hooks";
import { masterSchema, type MasterFormInput, type MasterFormValues } from "../schema";
import type { MasterConfig, MasterRecord } from "../types";

type Props = { config: MasterConfig; open: boolean; record?: MasterRecord | null; onClose: () => void; onSaved: (message: string) => void };
const inputClass = "mt-1.5 h-10 w-full rounded-lg border border-slate-200 bg-transparent px-3 text-sm outline-none focus:border-emerald-500 dark:border-slate-700";
const labelMap: Record<string, string> = { plant_id: "Plant", machine_number: "Machine Number", employee_number: "Employee Number", position: "Position", mm_number: "MM Number", name: "Item Name", qty_per_box: "Qty per Box", start_time: "Start Time", end_time: "End Time", is_active: "Active" };
const formFields: Record<MasterConfig["module"], string[]> = { plants: ["code", "name", "description", "is_active"], lines: ["plant_id", "code", "name", "description", "is_active"], machines: ["plant_id", "code", "name", "machine_number", "description", "is_active"], shifts: ["code", "name", "start_time", "end_time", "is_active"], products: ["mm_number", "name", "description", "qty_per_box", "is_active"], defects: ["code", "name", "category", "description", "is_active"], "qa-checkers": ["employee_number", "name", "position", "plant_id", "is_active"] };

export function MasterFormDialog({ config, open, record, onClose, onSaved }: Props) {
  const createMutation = useMasterCreate(config.module);
  const updateMutation = useMasterUpdate(config.module);
  const plants = useMasterOptions("plants");
  const { register, handleSubmit, reset, setError, formState: { errors } } = useForm<MasterFormInput, unknown, MasterFormValues>({ resolver: zodResolver(masterSchema(config.module)), defaultValues: { is_active: true } });
  const isSaving = createMutation.isPending || updateMutation.isPending;
  useEffect(() => { if (!open) return; if (!record) { reset({ is_active: true }); return; } const formValues = Object.fromEntries(formFields[config.module].map((field) => [field, record[field] === null ? "" : record[field]])); reset({ ...formValues, plant_id: record.plant_id as number | undefined, is_active: Boolean(record.is_active) } as MasterFormInput); }, [config.module, open, record, reset]);
  if (!open) return null;
  const submit = (values: MasterFormValues) => { const normalized = Object.fromEntries(Object.entries(values).map(([key, value]) => [key, value === "" ? undefined : value])); const payload = config.module === "products" ? { ...normalized, name: normalized.name ?? null, description: normalized.description ?? null } : normalized; const request = record ? updateMutation.mutateAsync({ id: Number(record.id), payload }) : createMutation.mutateAsync(payload); request.then(() => { onSaved(`${config.singular} ${record ? "updated" : "created"} successfully.`); onClose(); }).catch((error: { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }) => { Object.entries(error.response?.data?.errors ?? {}).forEach(([field, messages]) => { setError(field as keyof MasterFormInput, { type: "server", message: messages[0] }); }); setError("root.server", { type: "server", message: error.response?.data?.message ?? "Unable to save this record." }); }); };
  const fieldError = (field: string) => errors[field as keyof MasterFormInput]?.message?.toString();
  const renderField = (field: string) => {
    const label = labelMap[field] ?? field.replaceAll("_", " ").replace(/\b\w/g, (letter) => letter.toUpperCase());
    if (field === "is_active") return <label key={field} className="flex items-center gap-2 text-sm font-medium"><input type="checkbox" {...register("is_active")} className="size-4 accent-emerald-600" /> Active</label>;
    if (field === "description") return <label key={field} className="text-sm font-medium md:col-span-2">{label}<textarea {...register(field as keyof MasterFormInput)} rows={3} className="mt-1.5 w-full resize-none rounded-lg border border-slate-200 bg-transparent px-3 py-2 text-sm outline-none focus:border-emerald-500 dark:border-slate-700" />{fieldError(field) && <span className="text-xs text-destructive">{fieldError(field)}</span>}</label>;
    if (field === "plant_id") return <label key={field} className="text-sm font-medium">{label}<select {...register("plant_id")} className={inputClass}><option value="">Select {label.toLowerCase()}</option>{(plants.data?.data ?? []).map((item) => <option key={item.id} value={item.id}>{String(item.code ?? "")} — {String(item.name ?? "")}</option>)}</select>{fieldError(field) && <span className="text-xs text-destructive">{fieldError(field)}</span>}</label>;
    if (field === "category") return <label key={field} className="text-sm font-medium">Category<select {...register("category")} className={inputClass}><option value="">Select category</option><option value="CRITICAL">CRITICAL</option><option value="MAJOR">MAJOR</option><option value="MINOR">MINOR</option><option value="UNACCEPTABLE">UNACCEPTABLE</option><option value="-">-</option></select>{fieldError(field) && <span className="text-xs text-destructive">{fieldError(field)}</span>}</label>;
    const type = ["qty_per_box"].includes(field) ? "number" : ["start_time", "end_time"].includes(field) ? "time" : "text";
    return <label key={field} className="text-sm font-medium">{label}<input type={type} {...register(field as keyof MasterFormInput)} className={inputClass} min={type === "number" ? "1" : undefined} />{fieldError(field) && <span className="text-xs text-destructive">{fieldError(field)}</span>}</label>;
  };
  return <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-950/40 p-4" role="dialog" aria-modal="true"><div className="mx-auto my-8 w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900"><div className="flex items-start justify-between"><div><h2 className="text-lg font-semibold">{record ? `Edit ${config.singular}` : `Create ${config.singular}`}</h2><p className="mt-1 text-sm text-muted-foreground">Maintain validated master data for the QMS.</p></div><button type="button" onClick={onClose} aria-label="Close"><X className="size-5 text-muted-foreground" /></button></div><form onSubmit={handleSubmit(submit)} className="mt-6 grid gap-4 sm:grid-cols-2">{config.fields.map(renderField)}{errors.root?.server?.message && <p className="text-sm text-destructive sm:col-span-2">{errors.root.server.message}</p>}<div className="flex justify-end gap-3 pt-2 sm:col-span-2"><Button type="button" variant="outline" onClick={onClose}>Cancel</Button><Button type="submit" disabled={isSaving}>{isSaving ? "Saving..." : record ? "Save changes" : "Create"}</Button></div></form></div></div>;
}

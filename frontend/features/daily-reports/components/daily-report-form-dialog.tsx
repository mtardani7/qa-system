"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { Plus, Trash2, X } from "lucide-react";
import { useEffect, useState } from "react";
import { Controller, useFieldArray, useForm, useWatch } from "react-hook-form";
import { Button } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import { useCreateDailyReport, useDailyReportLookups, useDailyReportProducts, useUpdateDailyReport } from "../hooks";
import { dailyReportSchema, type DailyReportFormInput, type DailyReportFormValues } from "../schema";
import type { DailyReport } from "../types";
import { SearchableSelect } from "./searchable-select";
import { useAuthStore } from "@/features/auth/store";

type Props = { open: boolean; report?: DailyReport | null; onClose: () => void };
const fieldClass = "mt-1.5 h-10 w-full rounded-lg border border-slate-200 bg-transparent px-3 text-sm outline-none focus:border-emerald-500 dark:border-slate-700";
const today = () => new Date().toISOString().slice(0, 10);
const defaults = { output_box: 0, product_type: "FG" as const, defects: [{ defect_id: undefined, quantity: 1, remarks: "" }], checker_id: undefined, checker_2_id: undefined, production_date: today(), remarks: "", result: undefined, finding_range_box: "", finding_observation: "" };

export function DailyReportFormDialog({ open, report, onClose }: Props) {
  const { data: lookups, isLoading: loadingLookups } = useDailyReportLookups();
  const [productSearch, setProductSearch] = useState("");
  const { data: products = [] } = useDailyReportProducts(productSearch || report?.product.mm_number || "");
  const user = useAuthStore((state) => state.user);
  const createMutation = useCreateDailyReport();
  const updateMutation = useUpdateDailyReport();
  const { register, handleSubmit, reset, control, formState: { errors } } = useForm<DailyReportFormInput, unknown, DailyReportFormValues>({ resolver: zodResolver(dailyReportSchema), defaultValues: defaults });
  const { fields, append, remove } = useFieldArray({ control, name: "defects" });
  const plantId = Number(useWatch({ control, name: "plant_id" })) || undefined;
  const productId = Number(useWatch({ control, name: "product_id" })) || undefined;
  const outputBox = Number(useWatch({ control, name: "output_box" })) || 0;
  const defectValues = useWatch({ control, name: "defects" });
  const product = products.find((item) => item.id === productId);
  const machines = lookups?.machines.filter((item) => item.plant_id === plantId) ?? [];
  const shifts = lookups?.shifts.filter((item) => !plantId || !item.plant_id || item.plant_id === plantId) ?? [];
  const checkers = lookups?.qa_checkers.filter((item) => {
    if (!plantId) return true;
    return item.plant_id === plantId;
  }) ?? [];
  const isSaving = createMutation.isPending || updateMutation.isPending;

  useEffect(() => {
    if (!open) return;
    reset(report ? {
      plant_id: report.plant.id,
      machine_id: report.machine.id,
      shift_id: report.shift.id,
      product_id: report.product.id,
      product_type: report.product_type,
      checker_id: report.checker.id,
      checker_2_id: report.checker_2?.id ?? undefined,
      po_number: report.po_number,
      output_box: report.output_box,
      production_date: report.production_date,
      remarks: report.remarks ?? "",
      result: report.result ?? undefined,
      finding_range_box: report.finding_range_box ?? "",
      finding_observation: report.finding_observation ?? "",
      defects: report.defects.map((item) => ({ defect_id: item.defect_id, quantity: item.quantity, remarks: item.remarks ?? "" })),
    } : { ...defaults, plant_id: lookups?.plants.length === 1 ? lookups.plants[0].id : undefined });
  }, [open, report, reset, lookups, user]);

  if (!open) return null;
  const error = (name: keyof DailyReportFormInput) => errors[name]?.message?.toString();
  const submit = (values: DailyReportFormValues) => {
    const payload = { ...values, defects: values.defects.filter((defect): defect is typeof defect & { defect_id: number } => defect.defect_id !== undefined) };
    const request = report ? updateMutation.mutateAsync({ id: report.id, payload }) : createMutation.mutateAsync(payload);
    request.then(onClose);
  };

  return <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-950/40 p-4" role="dialog" aria-modal="true">
    <div className="mx-auto my-6 w-full max-w-6xl rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900">
      <div className="flex items-start justify-between"><div><h2 className="text-lg font-semibold">{report ? "Edit Daily QA Report" : "New Daily QA Report"}</h2><p className="mt-1 text-sm text-muted-foreground">Product quantity data and defect categories are read from master data.</p></div><button type="button" onClick={onClose} aria-label="Close"><X className="size-5 text-muted-foreground" /></button></div>
      {loadingLookups ? <div className="mt-6 space-y-4" aria-label="Loading master data"><Skeleton className="h-10 w-full" /><div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"><Skeleton className="h-10" /><Skeleton className="h-10" /><Skeleton className="h-10" /><Skeleton className="h-10" /></div><Skeleton className="h-32 w-full" /></div> : <form className="mt-6 space-y-6" onSubmit={handleSubmit(submit)}>
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <Controller name="plant_id" control={control} render={({ field }) => <SearchableSelect label="Plant" value={Number(field.value) || undefined} options={lookups?.plants ?? []} placeholder="Select plant" onChange={field.onChange} error={error("plant_id")} disabled={!user?.roles.includes("Super Admin") && (lookups?.plants.length ?? 0) === 1} />} />
          <Controller name="machine_id" control={control} render={({ field }) => <SearchableSelect label="Machine" value={Number(field.value) || undefined} options={machines} placeholder="Select machine" onChange={field.onChange} error={error("machine_id")} disabled={!plantId} />} />
          <Controller name="shift_id" control={control} render={({ field }) => <SearchableSelect label="Shift" value={Number(field.value) || undefined} options={shifts} placeholder="Select shift" onChange={field.onChange} error={error("shift_id")} />} />
          <label className="text-sm font-medium">Production Date<input type="date" {...register("production_date")} className={fieldClass} />{error("production_date") && <span className="text-xs text-destructive">{error("production_date")}</span>}</label>
        </div>
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
          <Controller name="product_id" control={control} render={({ field }) => <SearchableSelect label="NO. MM" value={Number(field.value) || undefined} options={products} placeholder="Search product / MM" onSearch={setProductSearch} onChange={field.onChange} error={error("product_id")} />} />
          <label className="text-sm font-medium">Product Type<select {...register("product_type")} className={fieldClass}><option value="FG">FG</option><option value="WIP">WIP</option></select>{error("product_type") && <span className="text-xs text-destructive">{error("product_type")}</span>}</label>
          <label className="text-sm font-medium">MM Number<input value={product?.mm_number ?? "—"} readOnly className={`${fieldClass} bg-slate-50 dark:bg-slate-950`} /></label>
          <label className="text-sm font-medium">Description<input value={product?.description ?? "—"} readOnly className={`${fieldClass} bg-slate-50 dark:bg-slate-950`} /></label>
          <label className="text-sm font-medium">Qty per Box<input value={product?.qty_per_box ?? "—"} readOnly className={`${fieldClass} bg-slate-50 dark:bg-slate-950`} /></label>
        </div>
        <div className="grid gap-4 sm:grid-cols-3"><label className="text-sm font-medium">PO Number<input {...register("po_number")} className={fieldClass} />{error("po_number") && <span className="text-xs text-destructive">{error("po_number")}</span>}</label><label className="text-sm font-medium">Output Box<input type="number" {...register("output_box")} className={fieldClass} min="1" />{error("output_box") && <span className="text-xs text-destructive">{error("output_box")}</span>}</label><div className="rounded-lg border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-900 dark:bg-emerald-950/30"><p className="text-xs font-medium uppercase tracking-wide text-emerald-700">Output PCS</p><p className="mt-1 text-2xl font-semibold text-emerald-800 dark:text-emerald-300">{(outputBox * (product?.qty_per_box ?? 0)).toLocaleString()}</p></div></div>
        <div className="grid gap-4 sm:grid-cols-2"><label className="text-sm font-medium">Findings Range (Box)<input {...register("finding_range_box")} className={fieldClass} placeholder="Example: 1-5" /></label><label className="text-sm font-medium">Finding During Checking<textarea {...register("finding_observation")} rows={2} className="mt-1.5 w-full resize-none rounded-lg border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-700" placeholder="QA observation (optional)" /></label></div>
        <div className="grid gap-4 sm:grid-cols-3">
          <Controller name="checker_id" control={control} render={({ field }) => <SearchableSelect label="Checked by QA 1" value={Number(field.value) || undefined} options={checkers} placeholder="Select QA checker 1" onChange={field.onChange} error={error("checker_id")} />} />
          <Controller name="checker_2_id" control={control} render={({ field }) => <SearchableSelect label="Checked by QA 2 (Optional)" value={Number(field.value) || undefined} options={checkers} placeholder="Select QA checker 2" onChange={field.onChange} error={error("checker_2_id")} />} />
          <label className="text-sm font-medium">Result / Status<select {...register("result")} className={fieldClass}><option value="">Use workflow status</option><option value="OK">OK</option><option value="OK, WITH NOTED">OK, WITH NOTED</option><option value="SORTIR">SORTIR</option><option value="REJECT">REJECT</option></select></label>
          <label className="text-sm font-medium">Remarks<textarea {...register("remarks")} rows={3} className="mt-1.5 w-full resize-none rounded-lg border border-slate-200 bg-transparent px-3 py-2 text-sm dark:border-slate-700" placeholder="Additional notes (optional)" /></label>
        </div>
        <div className="rounded-xl border border-slate-200 dark:border-slate-800"><div className="flex items-center justify-between border-b border-slate-200 p-4 dark:border-slate-800"><div><h3 className="font-medium">Defects</h3><p className="text-xs text-muted-foreground">Select from Defect Master; category is derived automatically.</p></div><Button type="button" variant="outline" size="sm" onClick={() => append({ defect_id: undefined, quantity: 1, remarks: "" })}><Plus className="size-4" /> Add Defect</Button></div><div className="space-y-3 p-4">{fields.map((field, index) => { const selectedId = Number(defectValues?.[index]?.defect_id) || undefined; const selected = lookups?.defects.find((item) => item.id === selectedId); return <div key={field.id} className="grid gap-3 md:grid-cols-[2fr_1fr_2fr_auto]"><Controller name={`defects.${index}.defect_id`} control={control} render={({ field: defectField }) => <SearchableSelect label="Defect" value={Number(defectField.value) || undefined} options={lookups?.defects ?? []} placeholder="Search defect" onChange={defectField.onChange} error={errors.defects?.[index]?.defect_id?.message?.toString()} />} /><label className="text-sm font-medium">Category<input value={selected?.category ?? "—"} readOnly className={`${fieldClass} bg-slate-50 dark:bg-slate-950`} /></label><label className="text-sm font-medium">Findings Quantity (PCS)<input type="number" {...register(`defects.${index}.quantity`)} className={fieldClass} min="1" />{errors.defects?.[index]?.quantity && <span className="text-xs text-destructive">{errors.defects[index]?.quantity?.message?.toString()}</span>}<input {...register(`defects.${index}.remarks`)} className={fieldClass} placeholder="Defect description (optional)" /></label><Button type="button" variant="ghost" size="icon-sm" className="mt-7 text-rose-600" onClick={() => remove(index)} disabled={fields.length === 1} aria-label="Remove defect"><Trash2 className="size-4" /></Button></div>; })}{errors.defects?.message && <p className="text-xs text-destructive">{errors.defects.message.toString()}</p>}</div></div>
        <div className="flex justify-end gap-3"><Button type="button" variant="outline" onClick={() => reset({ ...defaults, plant_id: lookups?.plants.length === 1 ? lookups.plants[0].id : undefined })}>Reset</Button><Button type="button" variant="outline" onClick={onClose}>Cancel</Button><Button type="submit" disabled={isSaving}>{isSaving ? "Saving..." : "Save Report"}</Button></div>
      </form>}
    </div>
  </div>;
}

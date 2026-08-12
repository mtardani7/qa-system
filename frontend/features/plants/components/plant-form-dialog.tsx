"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { X } from "lucide-react";
import { useEffect } from "react";
import { useForm } from "react-hook-form";
import { Button } from "@/components/ui/button";
import { useCreatePlant, useUpdatePlant } from "../hooks";
import { plantSchema, type PlantFormValues } from "../schema";
import type { Plant } from "../types";

type Props = { open: boolean; plant?: Plant | null; onClose: () => void };
export function PlantFormDialog({ open, plant, onClose }: Props) {
  const createMutation = useCreatePlant();
  const updateMutation = useUpdatePlant();
  const { register, handleSubmit, reset, formState: { errors } } = useForm<PlantFormValues>({ resolver: zodResolver(plantSchema), defaultValues: { code: "", name: "", description: "", is_active: true } });
  const isSaving = createMutation.isPending || updateMutation.isPending;
  useEffect(() => { reset(plant ? { code: plant.code, name: plant.name, description: plant.description ?? "", is_active: plant.is_active } : { code: "", name: "", description: "", is_active: true }); }, [plant, reset, open]);
  if (!open) return null;
  const submit = (values: PlantFormValues) => { const payload = { ...values, description: values.description || undefined }; const request = plant ? updateMutation.mutateAsync({ id: plant.id, payload }) : createMutation.mutateAsync(payload); request.then(onClose); };
  return <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4" role="dialog" aria-modal="true"><div className="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900"><div className="flex items-start justify-between"><div><h2 className="text-lg font-semibold text-slate-950 dark:text-white">{plant ? "Edit plant" : "Add plant"}</h2><p className="mt-1 text-sm text-slate-500">Keep the plant master data accurate for reporting.</p></div><button className="rounded-md p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" onClick={onClose} aria-label="Close"><X className="size-5" /></button></div><form className="mt-6 space-y-4" onSubmit={handleSubmit(submit)}><div className="grid gap-4 sm:grid-cols-2"><label className="space-y-1.5 text-sm font-medium">Code<input {...register("code")} className="mt-1.5 h-10 w-full rounded-lg border border-slate-200 bg-transparent px-3 font-mono text-sm outline-none focus:border-emerald-500 dark:border-slate-700" placeholder="P3" />{errors.code && <span className="block text-xs font-normal text-rose-600">{errors.code.message}</span>}</label><label className="space-y-1.5 text-sm font-medium">Name<input {...register("name")} className="mt-1.5 h-10 w-full rounded-lg border border-slate-200 bg-transparent px-3 text-sm outline-none focus:border-emerald-500 dark:border-slate-700" placeholder="Plant 3" />{errors.name && <span className="block text-xs font-normal text-rose-600">{errors.name.message}</span>}</label></div><label className="block space-y-1.5 text-sm font-medium">Description<textarea {...register("description")} rows={3} className="mt-1.5 w-full resize-none rounded-lg border border-slate-200 bg-transparent px-3 py-2 text-sm outline-none focus:border-emerald-500 dark:border-slate-700" placeholder="Optional description" />{errors.description && <span className="block text-xs font-normal text-rose-600">{errors.description.message}</span>}</label><label className="flex items-center gap-2 text-sm font-medium"><input type="checkbox" {...register("is_active")} className="size-4 accent-emerald-600" /> Active plant</label><div className="flex justify-end gap-3 pt-2"><Button type="button" variant="outline" onClick={onClose}>Cancel</Button><Button type="submit" disabled={isSaving}>{isSaving ? "Saving..." : plant ? "Save changes" : "Create plant"}</Button></div></form></div></div>;
}

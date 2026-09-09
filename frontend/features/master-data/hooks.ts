"use client";

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { createMasterRecord, deleteMasterRecord, getMasterRecords, importMasterProducts, updateMasterRecord } from "./services";
import type { MasterModule, MasterPayload, MasterQuery, MasterRecord } from "./types";

export const masterKeys = { all: (module: MasterModule) => ["master-data", module] as const, list: (module: MasterModule, query: MasterQuery) => ["master-data", module, query] as const };
export function useMasterRecords(module: MasterModule, query: MasterQuery) { return useQuery({ queryKey: masterKeys.list(module, query), queryFn: () => getMasterRecords(module, query) }); }
export function useMasterCreate(module: MasterModule) { const client = useQueryClient(); return useMutation({ mutationFn: (payload: MasterPayload) => createMasterRecord(module, payload), onSuccess: () => client.invalidateQueries({ queryKey: masterKeys.all(module) }) }); }
export function useMasterUpdate(module: MasterModule) { const client = useQueryClient(); return useMutation({ mutationFn: ({ id, payload }: { id: number; payload: MasterPayload }) => updateMasterRecord(module, id, payload), onSuccess: (updatedRecord) => { client.setQueriesData({ queryKey: masterKeys.all(module) }, (current: { data: MasterRecord[]; current_page: number; last_page: number; per_page: number; total: number } | undefined) => current ? { ...current, data: current.data.map((record) => record.id === updatedRecord.id ? updatedRecord : record) } : current); client.invalidateQueries({ queryKey: masterKeys.all(module) }); } }); }
export function useMasterDelete(module: MasterModule) { const client = useQueryClient(); return useMutation({ mutationFn: (id: number) => deleteMasterRecord(module, id), onSuccess: () => client.invalidateQueries({ queryKey: masterKeys.all(module) }) }); }
export function useMasterOptions(module: MasterModule) { return useQuery({ queryKey: ["master-options", module], queryFn: () => getMasterRecords(module, { per_page: 100, is_active: true }), staleTime: 60_000 }); }
export function useMasterProductImport() { const client = useQueryClient(); return useMutation({ mutationFn: ({ file, plantId, onUploadProgress }: { file: File; plantId: number; onUploadProgress?: (progress: number) => void }) => importMasterProducts(file, plantId, onUploadProgress), onSuccess: () => client.invalidateQueries({ queryKey: masterKeys.all("products") }) }); }

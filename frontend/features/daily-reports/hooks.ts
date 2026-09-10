"use client";

import { keepPreviousData, useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useAuthStore } from "@/features/auth/store";
import { bulkDeleteDailyReports, bulkLockDailyReports, createDailyReport, deleteDailyReport, duplicateDailyReport, getDailyReport, getDailyReportExport, getDailyReportImport, getDailyReportLookups, getDailyReportProducts, getDailyReports, importDailyReports, pasteDailyReports, queueDailyReportExport, updateDailyReport, workflowDailyReport, type DailyReportPayload, type DailyReportQuery } from "./services";

export const dailyReportKeys = { all: ["daily-reports"] as const, list: (query: DailyReportQuery) => ["daily-reports", "list", query] as const, lookups: (userId?: number, roles: string[] = [], plantIds: number[] = []) => ["daily-reports", "lookups", userId, roles.join(","), plantIds.join(",")] as const };
export function useDailyReports(query: DailyReportQuery) { return useQuery({ queryKey: dailyReportKeys.list(query), queryFn: () => getDailyReports(query), placeholderData: keepPreviousData, staleTime: 30_000 }); }
export function useDailyReportLookups() {
	const user = useAuthStore((state) => state.user);
	const roles = useAuthStore((state) => state.roles);
	const plantIds = user?.plant_ids ?? [];
	return useQuery({ queryKey: dailyReportKeys.lookups(user?.id, roles, plantIds), queryFn: getDailyReportLookups, staleTime: 300_000 });
}
export function useDailyReportProducts(search: string, plantId?: number) { return useQuery({ queryKey: ["daily-reports", "products", search, plantId], queryFn: () => getDailyReportProducts(search, plantId), staleTime: 300_000, enabled: Boolean(plantId) }); }
export function useCreateDailyReport() { const client = useQueryClient(); return useMutation({ mutationFn: (payload: DailyReportPayload) => createDailyReport(payload), onSuccess: () => client.invalidateQueries({ queryKey: dailyReportKeys.all }) }); }
export function usePasteDailyReports() { const client = useQueryClient(); return useMutation({ mutationFn: (payloads: DailyReportPayload[]) => pasteDailyReports(payloads), onSuccess: () => client.invalidateQueries({ queryKey: dailyReportKeys.all }) }); }
export function useUpdateDailyReport() { const client = useQueryClient(); return useMutation({ mutationFn: ({ id, payload }: { id: number; payload: DailyReportPayload }) => updateDailyReport(id, payload), onSuccess: () => client.invalidateQueries({ queryKey: dailyReportKeys.all }) }); }
export function useDeleteDailyReport() { const client = useQueryClient(); return useMutation({ mutationFn: (id: number) => deleteDailyReport(id), onSuccess: () => client.invalidateQueries({ queryKey: dailyReportKeys.all }) }); }
export function useBulkDeleteDailyReports() { const client = useQueryClient(); return useMutation({ mutationFn: (ids: number[]) => bulkDeleteDailyReports(ids), onSuccess: () => client.invalidateQueries({ queryKey: dailyReportKeys.all }) }); }
export function useBulkLockDailyReports() { const client = useQueryClient(); return useMutation({ mutationFn: ({ month, plantId }: { month: string; plantId?: number }) => bulkLockDailyReports(month, plantId), onSuccess: () => client.invalidateQueries({ queryKey: dailyReportKeys.all }) }); }
export function useDailyReport(id: number) { return useQuery({ queryKey: ["daily-reports", "detail", id], queryFn: () => getDailyReport(id), enabled: Boolean(id) }); }
export function useDailyReportWorkflow() { const client = useQueryClient(); return useMutation({ mutationFn: ({ id, action }: { id: number; action: "lock" }) => workflowDailyReport(id, action), onSuccess: () => client.invalidateQueries({ queryKey: dailyReportKeys.all }) }); }
export function useBulkDailyReportWorkflow() { const client = useQueryClient(); return useMutation({ mutationFn: ({ ids, action }: { ids: number[]; action: "lock" }) => Promise.all(ids.map((id) => workflowDailyReport(id, action))), onSuccess: () => client.invalidateQueries({ queryKey: dailyReportKeys.all }) }); }
export function useDuplicateDailyReport() { const client = useQueryClient(); return useMutation({ mutationFn: (id: number) => duplicateDailyReport(id), onSuccess: () => client.invalidateQueries({ queryKey: dailyReportKeys.all }) }); }
export function useQueueDailyReportExport() { return useMutation({ mutationFn: (filters: Record<string, number | string | boolean | undefined>) => queueDailyReportExport(filters) }); }
export function useDailyReportExport(id?: number) { return useQuery({ queryKey: ["daily-report-exports", id], queryFn: () => getDailyReportExport(id as number), enabled: Boolean(id), refetchInterval: (query) => query.state.data?.status === "completed" || query.state.data?.status === "failed" ? false : 1500 }); }
export function useImportDailyReports() { const client = useQueryClient(); return useMutation({ mutationFn: ({ file, plantId, onUploadProgress }: { file: File; plantId: number; onUploadProgress?: (progress: number) => void }) => importDailyReports(file, plantId, onUploadProgress), onSuccess: () => client.invalidateQueries({ queryKey: dailyReportKeys.all }) }); }
export function useDailyReportImport(id?: number) { return useQuery({ queryKey: ["daily-report-imports", id], queryFn: () => getDailyReportImport(id as number), enabled: Boolean(id), refetchInterval: (query) => query.state.data?.status === "completed" || query.state.data?.status === "completed_with_errors" || query.state.data?.status === "failed" ? false : 1500 }); }

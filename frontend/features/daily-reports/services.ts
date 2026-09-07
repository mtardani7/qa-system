import { apiClient } from "@/services/api-client";
import type { ApiResponse, Paginated } from "@/types/api";
import type { DailyReport, DailyReportLookups, DailyReportResult, DailyReportStatus } from "./types";

export type DailyReportQuery = { page?: number; per_page?: number; search?: string; period?: "daily" | "weekly" | "monthly" | "yearly"; plant_id?: number; machine_id?: number; shift_id?: number; product_id?: number; product_type?: "FG" | "WIP"; defect_id?: number; checker_id?: number; qa_checker_id?: number; qa_checker_2_id?: number; result?: string; status?: DailyReportStatus; output_box_zero?: boolean; production_date?: string; production_date_from?: string; production_date_to?: string; sort?: string; direction?: "asc" | "desc" };
export type DailyReportPayload = { plant_id: number; machine_id: number; shift_id: number; product_id: number; product_type: "WIP" | "FG"; checker_id: number; checker_2_id?: number; po_number: string; output_box: number; production_date: string; defects: { defect_id: number; quantity: number; remarks?: string }[]; remarks?: string; result?: DailyReportResult; finding_range_box?: string };
export type DailyReportExport = { id: number; status: "queued" | "processing" | "completed" | "failed"; file_path?: string; error_message?: string; created_at: string };
export type DailyReportImportStatus = { id: number; file_name: string; status: "queued" | "processing" | "completed" | "completed_with_errors" | "failed"; processed_rows: number; imported_reports: number; failed_rows: number; errors?: { row: number; message: string }[]; error_message?: string | null };
type DailyReportCollectionResponse = ApiResponse<Paginated<DailyReport> | DailyReport[]> & { meta?: { current_page?: number; last_page?: number; per_page?: number; total?: number } };
export async function importDailyReports(file: File, plantId: number, onUploadProgress?: (progress: number) => void) { const form = new FormData(); form.append("file", file); form.append("plant_id", String(plantId)); const { data } = await apiClient.post<ApiResponse<DailyReportImportStatus>>("/daily-reports/import", form, { onUploadProgress: (event) => { if (event.total) onUploadProgress?.(Math.round((event.loaded / event.total) * 100)); } }); return data.data; }
export async function downloadDailyReportImportTemplate() { const response = await apiClient.get("/daily-reports/import-template", { responseType: "blob" }); const url = URL.createObjectURL(response.data); const link = document.createElement("a"); link.href = url; link.download = "daily-report-import-template.xlsx"; link.click(); URL.revokeObjectURL(url); }
export async function getDailyReportImport(id: number) { const { data } = await apiClient.get<ApiResponse<DailyReportImportStatus>>(`/daily-reports/imports/${id}`); return data.data; }
export async function getDailyReports(params: DailyReportQuery = {}) { const query = { per_page: 20, ...params }; const { data } = await apiClient.get<DailyReportCollectionResponse>("/daily-reports", { params: query }); if (!Array.isArray(data.data)) return data.data; return { data: data.data, current_page: data.meta?.current_page ?? Number(query.page ?? 1), last_page: data.meta?.last_page ?? (data.data.length ? Number(query.page ?? 1) : 1), per_page: data.meta?.per_page ?? Number(query.per_page ?? 20), total: data.meta?.total ?? data.data.length }; }
export async function getDailyReportLookups() { const { data } = await apiClient.get<ApiResponse<DailyReportLookups>>("/daily-reports/lookups"); return data.data; }
export async function getDailyReportProducts(search = "") { const { data } = await apiClient.get<ApiResponse<DailyReportLookups["products"]>>("/daily-reports/lookups/products", { params: { search } }); return data.data; }
export async function createDailyReport(payload: DailyReportPayload) { const { data } = await apiClient.post<ApiResponse<DailyReport>>("/daily-reports", payload); return data.data; }
export async function pasteDailyReports(payloads: DailyReportPayload[]) {
	const reports: DailyReport[] = [];
	for (const payload of payloads) reports.push(await createDailyReport(payload));
	return reports;
}
export async function updateDailyReport(id: number, payload: DailyReportPayload) { const { data } = await apiClient.put<ApiResponse<DailyReport>>(`/daily-reports/${id}`, payload); return data.data; }
export async function deleteDailyReport(id: number) { await apiClient.delete(`/daily-reports/${id}`); }
export async function bulkDeleteDailyReports(ids: number[]) { await apiClient.delete("/daily-reports/bulk", { data: { ids } }); }
export async function bulkLockDailyReports(month: string, plantId?: number) { const { data } = await apiClient.post<ApiResponse<{ locked: number; month: string }>>("/daily-reports/bulk-lock", { month, plant_id: plantId }); return data.data; }
export async function getDailyReport(id: number) { const { data } = await apiClient.get<ApiResponse<DailyReport>>(`/daily-reports/${id}`); return data.data; }
export async function workflowDailyReport(id: number, action: "lock") { const { data } = await apiClient.post<ApiResponse<DailyReport>>(`/daily-reports/${id}/${action}`); return data.data; }
export async function duplicateDailyReport(id: number) { const { data } = await apiClient.post<ApiResponse<DailyReport>>(`/daily-reports/${id}/duplicate`); return data.data; }
export async function printDailyReport(id: number) { const { data } = await apiClient.get<Blob>(`/daily-reports/${id}/print`, { responseType: "blob" }); return data; }
export async function queueDailyReportExport(filters: Record<string, number | string | boolean | undefined>) { const { data } = await apiClient.post<ApiResponse<DailyReportExport>>("/daily-reports/export", filters); return data.data; }
export async function getDailyReportExport(id: number) { const { data } = await apiClient.get<ApiResponse<DailyReportExport>>(`/daily-reports/exports/${id}`); return data.data; }
export async function downloadDailyReportExport(id: number) { const { data } = await apiClient.get<Blob>(`/daily-reports/exports/${id}/download`, { responseType: "blob" }); return data; }
export function dailyReportExportDownloadUrl(id: number) { return `${apiClient.defaults.baseURL}/daily-reports/exports/${id}/download`; }

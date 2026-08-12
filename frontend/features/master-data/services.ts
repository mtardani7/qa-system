import { apiClient } from "@/services/api-client";
import type { ApiResponse, Paginated } from "@/types/api";
import type { MasterModule, MasterPayload, MasterQuery, MasterRecord } from "./types";

type LaravelCollectionResponse = ApiResponse<Paginated<MasterRecord> | MasterRecord[]> & { meta?: { current_page?: number; last_page?: number; per_page?: number; total?: number } };
export async function getMasterRecords(module: MasterModule, params: MasterQuery = {}) { const query = Object.fromEntries(Object.entries({ per_page: 20, ...params }).filter(([, value]) => value !== undefined && value !== "")); const response = await apiClient.get<LaravelCollectionResponse>(`/${module}`, { params: query }); const payload = response.data.data; if (Array.isArray(payload)) { const meta = response.data.meta; return { data: payload, current_page: meta?.current_page ?? Number(query.page ?? 1), last_page: meta?.last_page ?? (payload.length ? Number(query.page ?? 1) : 1), per_page: meta?.per_page ?? Number(query.per_page ?? 20), total: meta?.total ?? payload.length }; } return payload; }
export async function createMasterRecord(module: MasterModule, payload: MasterPayload) { const { data } = await apiClient.post<ApiResponse<MasterRecord>>(`/${module}`, payload); return data.data; }
export async function updateMasterRecord(module: MasterModule, id: number, payload: MasterPayload) { const { data } = await apiClient.put<ApiResponse<MasterRecord>>(`/${module}/${id}`, payload); return data.data; }
export async function deleteMasterRecord(module: MasterModule, id: number) { await apiClient.delete(`/${module}/${id}`); }
export async function importMasterProducts(file: File) { const form = new FormData(); form.append("file", file); const { data } = await apiClient.post<ApiResponse<null>>("/products/import", form, { headers: { "Content-Type": "multipart/form-data" } }); return data; }

import { apiClient } from "@/services/api-client";
import type { ApiResponse, Paginated } from "@/types/api";
import type { Plant } from "./types";
export type PlantPayload = { code: string; name: string; description?: string; is_active: boolean };
export type PlantQuery = { search?: string; page?: number; per_page?: number };
export async function getPlants(params: PlantQuery = {}): Promise<Paginated<Plant>> { const { data } = await apiClient.get<ApiResponse<Paginated<Plant>>>("/plants", { params: { per_page: 20, ...params } }); return data.data; }
export async function createPlant(payload: PlantPayload) { const { data } = await apiClient.post<ApiResponse<Plant>>("/plants", payload); return data.data; }
export async function updatePlant(id: number, payload: PlantPayload) { const { data } = await apiClient.put<ApiResponse<Plant>>(`/plants/${id}`, payload); return data.data; }
export async function deletePlant(id: number) { await apiClient.delete(`/plants/${id}`); }

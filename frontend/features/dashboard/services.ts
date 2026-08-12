import { apiClient } from "@/services/api-client";
import type { ApiResponse } from "@/types/api";
import type { DashboardData, DashboardQuery } from "./types";

export async function getDashboard(query: DashboardQuery) { const { data } = await apiClient.get<ApiResponse<DashboardData>>("/dashboard", { params: query }); return data.data; }

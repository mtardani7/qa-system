import { apiClient } from "@/services/api-client";
import type { ApiResponse } from "@/types/api";
import type { AuthResponse, AuthUser } from "./types";
export async function login(payload: { email: string; password: string; device_name?: string }) { const { data } = await apiClient.post<ApiResponse<AuthResponse>>("/auth/login", payload); return data.data; }
export async function getCurrentUser() { const { data } = await apiClient.get<ApiResponse<{ user: AuthUser; roles: string[]; permissions: string[] }>>("/auth/me"); return data.data; }
export async function logout() { await apiClient.post("/auth/logout"); }

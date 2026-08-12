export type ApiResponse<T> = { success: boolean; message: string; data: T };
export type Paginated<T> = { data: T[]; current_page: number; last_page: number; per_page: number; total: number };

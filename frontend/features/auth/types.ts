export type AuthUser = { id: number; name: string; email: string; employee_number?: string; is_qa_checker?: boolean; plant_ids?: number[]; roles: string[]; permissions: string[] };
export type AuthResponse = { token: string; user: Omit<AuthUser, "roles" | "permissions">; roles: string[]; permissions: string[] };

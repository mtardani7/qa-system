"use client";

import { useEffect, useMemo, useRef, useState } from "react";
import { ChevronLeft, ChevronRight, Search, UserPlus } from "lucide-react";
import { apiClient } from "@/services/api-client";
import { Button } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import { useAuthStore } from "@/features/auth/store";

type Plant = { id: number; code: string; name: string };
type Account = {
    id: number;
    name: string;
    employee_number: string;
    email: string;
    roles: string[];
    plants: Plant[];
    is_active: boolean;
};

type AccountForm = {
    name: string;
    employee_number: string;
    email: string;
    password: string;
    password_confirmation: string;
    role: string;
    plant_ids: number[];
    is_active: boolean;
};

type UsersManagementPageProps = {
    pageTitle?: string;
    pageDescription?: string;
};

const roles = ["Super Admin", "QA Manager", "QA Supervisor", "QA Staff", "Production", "Management"];
const initialForm: AccountForm = {
    name: "",
    employee_number: "",
    email: "",
    password: "",
    password_confirmation: "",
    role: "QA Staff",
    plant_ids: [],
    is_active: true,
};

export function UsersManagementPage({
    pageTitle = "Users",
    pageDescription = "Manage user accounts and plant access.",
}: UsersManagementPageProps) {
    const authUser = useAuthStore((state) => state.user);
    const isSuperAdmin = authUser?.roles?.includes("Super Admin") ?? false;
    const canView = isSuperAdmin;
    const canCreate = isSuperAdmin;
    const canUpdate = isSuperAdmin;
    const canResetPassword = isSuperAdmin;

    const [users, setUsers] = useState<Account[]>([]);
    const [plants, setPlants] = useState<Plant[]>([]);
    const [loading, setLoading] = useState(true);
    const [search, setSearch] = useState("");
    const [page, setPage] = useState(1);
    const [pageSize, setPageSize] = useState(20);
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1 });
    const [editing, setEditing] = useState<Account | null>(null);
    const [createOpen, setCreateOpen] = useState(false);
    const [form, setForm] = useState<AccountForm>(initialForm);
    const [error, setError] = useState("");
    const [isSaving, setIsSaving] = useState(false);
    const skipInitialSearch = useRef(true);

    const title = useMemo(() => (editing ? "Edit User" : "Create User"), [editing]);

    const loadUsers = async (keyword = search, requestedPage = page, requestedPageSize = pageSize) => {
        const response = await apiClient.get("/users", {
            params: {
                search: keyword || undefined,
                page: requestedPage,
                per_page: requestedPageSize,
            },
        });
        const payload = response.data.data;
        setUsers(payload.data ?? payload);
        setPagination({ current_page: payload.current_page ?? requestedPage, last_page: payload.last_page ?? 1 });
    };

    useEffect(() => {
        if (!canView) return;

        const refresh = async () => {
            setLoading(true);
            try {
                const [accountResponse, plantResponse] = await Promise.all([
                    apiClient.get("/users", { params: { page: 1, per_page: pageSize } }),
                    apiClient.get("/plants", { params: { per_page: 100, is_active: true } }),
                ]);
                const accountPayload = accountResponse.data.data;
                setUsers(accountPayload.data ?? accountPayload);
                setPagination({ current_page: accountPayload.current_page ?? 1, last_page: accountPayload.last_page ?? 1 });
                setPlants(plantResponse.data.data.data ?? plantResponse.data.data);
            } finally {
                setLoading(false);
            }
        };

        void refresh();
    }, [canView]);

    useEffect(() => {
        if (!canView) return;
        if (skipInitialSearch.current) {
            skipInitialSearch.current = false;
            return;
        }
        const timeout = window.setTimeout(() => {
            setPage(1);
            void loadUsers(search, 1, pageSize);
        }, 300);
        return () => window.clearTimeout(timeout);
    }, [canView, search, pageSize]);

    if (!canView) {
        return <main className="p-8 text-sm text-muted-foreground">You are not authorized to manage users.</main>;
    }

    const clearForm = () => {
        setEditing(null);
        setCreateOpen(false);
        setForm(initialForm);
        setError("");
    };

    const startCreate = () => {
        clearForm();
        setCreateOpen(true);
    };

    const startEdit = (account: Account) => {
        setCreateOpen(false);
        setEditing(account);
        setError("");
        setForm({
            name: account.name,
            employee_number: account.employee_number ?? "",
            email: account.email,
            password: "",
            password_confirmation: "",
            role: account.roles[0] ?? "QA Staff",
            plant_ids: account.plants.map((plant) => plant.id),
            is_active: Boolean(account.is_active),
        });
        document.getElementById("user-form")?.scrollIntoView({ behavior: "smooth" });
    };

    const togglePlant = (plantId: number) => {
        setForm((current) => ({
            ...current,
            plant_ids: current.plant_ids.includes(plantId)
                ? current.plant_ids.filter((id) => id !== plantId)
                : [...current.plant_ids, plantId],
        }));
    };

    const submit = async (event: React.FormEvent) => {
        event.preventDefault();
        if (!canCreate && !canUpdate) return;

        setError("");
        setIsSaving(true);
        try {
            const payload = {
                name: form.name,
                employee_number: form.employee_number,
                email: form.email,
                password: form.password || undefined,
                password_confirmation: form.password_confirmation || undefined,
                role: form.role,
                plant_ids: form.role === "Super Admin" ? [] : form.plant_ids,
                is_active: form.is_active,
            };

            if (editing) {
                await apiClient.put(`/users/${editing.id}`, payload);
            } else {
                await apiClient.post("/users", payload);
            }

            clearForm();
            await loadUsers();
        } catch (requestError: unknown) {
            const response = requestError as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
            const firstValidationError = Object.values(response.response?.data?.errors ?? {})[0]?.[0];
            setError(firstValidationError ?? response.response?.data?.message ?? "Unable to save user.");
        } finally {
            setIsSaving(false);
        }
    };

    const toggleActive = async (account: Account) => {
        if (!canUpdate) return;
        setError("");

        try {
            await apiClient.put(`/users/${account.id}`, {
                name: account.name,
                employee_number: account.employee_number,
                email: account.email,
                role: account.roles[0] ?? "QA Staff",
                plant_ids: (account.roles[0] ?? "") === "Super Admin" ? [] : account.plants.map((plant) => plant.id),
                is_active: !account.is_active,
            });
            await loadUsers();
        } catch (requestError: unknown) {
            const response = requestError as { response?: { data?: { message?: string } } };
            setError(response.response?.data?.message ?? "Unable to update status.");
        }
    };

    const resetPassword = async (account: Account) => {
        if (!canResetPassword) return;

        const password = window.prompt(`Enter new password for ${account.name}`);
        if (!password) return;
        const confirm = window.prompt(`Confirm new password for ${account.name}`);
        if (password !== confirm) {
            setError("Password confirmation does not match.");
            return;
        }

        setError("");
        try {
            await apiClient.post(`/users/${account.id}/reset-password`, {
                password,
                password_confirmation: confirm,
            });
        } catch (requestError: unknown) {
            const response = requestError as { response?: { data?: { message?: string } } };
            setError(response.response?.data?.message ?? "Unable to reset password.");
        }
    };

    return (
        <main className="min-h-screen bg-slate-50 px-6 py-8 lg:px-10 dark:bg-slate-950">
            <div className="mx-auto max-w-7xl space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-950 dark:text-white">{pageTitle}</h1>
                        <p className="mt-1 text-sm text-muted-foreground">{pageDescription}</p>
                    </div>
                    {canCreate && (
                        <Button onClick={startCreate}>
                            <UserPlus className="size-4" /> Create User
                        </Button>
                    )}
                </div>

                <section className="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div className="flex flex-col gap-3 border-b border-slate-200 p-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 className="text-base font-semibold">Users registry</h2>
                            <p className="mt-1 text-sm text-muted-foreground">Manage accounts and plant access.</p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <label className="flex h-10 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm dark:border-slate-700">
                                <Search className="size-4 text-muted-foreground" />
                                <input className="w-64 bg-transparent outline-none" placeholder="Search users..." value={search} onChange={(event) => setSearch(event.target.value)} />
                            </label>
                            <select className="h-10 rounded-lg border border-slate-200 bg-transparent px-3 text-sm dark:border-slate-700" value={pageSize} onChange={(event) => { setPageSize(Number(event.target.value)); setPage(1); }} aria-label="Rows per page">
                                {[20, 50, 100].map((size) => <option key={size} value={size}>{size} / page</option>)}
                            </select>
                        </div>
                    </div>
                    <table className="w-full min-w-[1100px] text-left text-sm">
                        <thead className="border-b bg-slate-50 text-xs uppercase tracking-wide text-muted-foreground dark:border-slate-800 dark:bg-slate-950/40">
                            <tr>
                                {[
                                    "Name",
                                    "Employee Number",
                                    "Username/Email",
                                    "Role",
                                    "Plant",
                                    "Status",
                                    "Actions",
                                ].map((heading) => (
                                    <th key={heading} className="px-4 py-3 font-medium">{heading}</th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                            {loading ? (
                                <tr>
                                    <td className="px-4 py-4" colSpan={7}><div className="space-y-3" aria-label="Loading users"><Skeleton className="h-10 w-full" /><Skeleton className="h-10 w-full" /><Skeleton className="h-10 w-full" /><Skeleton className="h-10 w-full" /></div></td>
                                </tr>
                            ) : users.length === 0 ? (
                                <tr>
                                    <td className="px-4 py-6 text-muted-foreground" colSpan={7}>No users found.</td>
                                </tr>
                            ) : users.map((account) => (
                                <tr key={account.id} className="text-slate-700 transition-colors hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800/50">
                                    <td className="px-4 py-3 font-medium text-slate-950 dark:text-white">{account.name}</td>
                                    <td className="px-4 py-3">{account.employee_number || "-"}</td>
                                    <td className="px-4 py-3">{account.email}</td>
                                    <td className="px-4 py-3">{account.roles.join(", ")}</td>
                                    <td className="px-4 py-3">{account.plants.map((plant) => plant.name).join(", ") || "All Plants"}</td>
                                    <td className="px-4 py-3">{account.is_active ? "Active" : "Inactive"}</td>
                                    <td className="px-4 py-3">
                                        <div className="flex flex-wrap gap-2">
                                            {canUpdate && <Button size="sm" variant="outline" onClick={() => startEdit(account)}>Edit</Button>}
                                            {canUpdate && <Button size="sm" variant="outline" onClick={() => void toggleActive(account)}>{account.is_active ? "Deactivate" : "Activate"}</Button>}
                                            {canResetPassword && <Button size="sm" variant="outline" onClick={() => void resetPassword(account)}>Reset Password</Button>}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    <div className="flex items-center justify-between border-t border-slate-200 px-5 py-3 dark:border-slate-800">
                        <p className="text-xs text-muted-foreground">Page {pagination.current_page} of {pagination.last_page}</p>
                        <div className="flex gap-2">
                            <Button variant="outline" size="sm" disabled={pagination.current_page <= 1 || loading} onClick={() => { const nextPage = page - 1; setPage(nextPage); void loadUsers(search, nextPage, pageSize); }}><ChevronLeft className="size-4" /> Previous</Button>
                            <Button variant="outline" size="sm" disabled={pagination.current_page >= pagination.last_page || loading} onClick={() => { const nextPage = page + 1; setPage(nextPage); void loadUsers(search, nextPage, pageSize); }}>Next <ChevronRight className="size-4" /></Button>
                        </div>
                    </div>
                </section>

                {(canCreate && createOpen || (canUpdate && editing)) && (
                    <div className={createOpen ? "fixed inset-0 z-50 overflow-y-auto bg-slate-950/40 p-4" : ""} role={createOpen ? "dialog" : undefined} aria-modal={createOpen ? true : undefined} aria-labelledby={createOpen ? "user-form-title" : undefined}>
                        <form id="user-form" onSubmit={submit} className={createOpen ? "mx-auto my-6 grid w-full max-w-4xl gap-4 rounded-xl border bg-white p-6 shadow-xl sm:grid-cols-2 lg:grid-cols-3 dark:border-slate-800 dark:bg-slate-900" : "grid gap-4 rounded-xl border bg-white p-6 sm:grid-cols-2 lg:grid-cols-3 dark:border-slate-800 dark:bg-slate-900"}>
                            <h2 id={createOpen ? "user-form-title" : undefined} className="text-lg font-semibold sm:col-span-2 lg:col-span-3">{title}</h2>

                            <label className="text-sm font-medium">Name
                                <input required value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} className="mt-1.5 h-10 w-full rounded-lg border px-3 text-sm dark:border-slate-700 dark:bg-slate-950" />
                            </label>

                            <label className="text-sm font-medium">Employee Number
                                <input required value={form.employee_number} onChange={(event) => setForm((current) => ({ ...current, employee_number: event.target.value }))} className="mt-1.5 h-10 w-full rounded-lg border px-3 text-sm dark:border-slate-700 dark:bg-slate-950" />
                            </label>

                            <label className="text-sm font-medium">Username/Email
                                <input required value={form.email} onChange={(event) => setForm((current) => ({ ...current, email: event.target.value }))} className="mt-1.5 h-10 w-full rounded-lg border px-3 text-sm dark:border-slate-700 dark:bg-slate-950" />
                            </label>

                            <label className="text-sm font-medium">Password
                                <input type="password" required={!editing} value={form.password} onChange={(event) => setForm((current) => ({ ...current, password: event.target.value }))} className="mt-1.5 h-10 w-full rounded-lg border px-3 text-sm dark:border-slate-700 dark:bg-slate-950" />
                            </label>

                            <label className="text-sm font-medium">Confirm Password
                                <input type="password" required={!editing} value={form.password_confirmation} onChange={(event) => setForm((current) => ({ ...current, password_confirmation: event.target.value }))} className="mt-1.5 h-10 w-full rounded-lg border px-3 text-sm dark:border-slate-700 dark:bg-slate-950" />
                            </label>

                            <label className="text-sm font-medium">Role
                                <select value={form.role} onChange={(event) => setForm((current) => ({ ...current, role: event.target.value }))} className="mt-1.5 h-10 w-full rounded-lg border px-3 text-sm dark:border-slate-700 dark:bg-slate-950">
                                    {roles.map((role) => <option key={role} value={role}>{role}</option>)}
                                </select>
                            </label>

                            <fieldset className="text-sm sm:col-span-2 lg:col-span-3">
                                <legend className="font-medium">Plant Access</legend>
                                <div className="mt-2 flex flex-wrap gap-3">
                                    {plants.map((plant) => (
                                        <label key={plant.id} className="flex items-center gap-2">
                                            <input type="checkbox" checked={form.plant_ids.includes(plant.id)} disabled={form.role === "Super Admin"} onChange={() => togglePlant(plant.id)} />
                                            {plant.name}
                                        </label>
                                    ))}
                                </div>
                            </fieldset>

                            <label className="flex items-center gap-2 text-sm font-medium">
                                <input type="checkbox" checked={form.is_active} onChange={(event) => setForm((current) => ({ ...current, is_active: event.target.checked }))} />
                                Active Status
                            </label>

                            {error && <p className="text-sm text-destructive sm:col-span-2 lg:col-span-3">{error}</p>}

                            <div className="flex justify-end gap-3 pt-2 sm:col-span-2 lg:col-span-3">
                                <Button type="button" variant="outline" onClick={clearForm}>Cancel</Button>
                                <Button type="submit" disabled={isSaving}>{isSaving ? "Saving..." : editing ? "Save User" : "Create User"}</Button>
                            </div>
                        </form>
                    </div>
                )}
            </div>
        </main>
    );
}

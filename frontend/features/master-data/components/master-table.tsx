"use client";
import {
  CheckCircle2,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  ChevronUp,
  CircleOff,
  Edit2,
  Search,
  Trash2,
} from "lucide-react";
import { useEffect, useState } from "react";
import { Button } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import { useMasterDelete, useMasterOptions, useMasterRecords } from "../hooks";
import type { MasterConfig, MasterRecord } from "../types";

type Props = {
  config: MasterConfig;
  onEdit: (record: MasterRecord) => void;
  onDeleteRequest: (record: MasterRecord) => void;
};
const selectClass =
  "h-10 rounded-lg border border-slate-200 bg-transparent px-3 text-sm dark:border-slate-700";
export function MasterTable({ config, onEdit, onDeleteRequest }: Props) {
  const [searchInput, setSearchInput] = useState("");
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const [pageSize, setPageSize] = useState(20);
  const [sort, setSort] = useState("id");
  const [direction, setDirection] = useState<"asc" | "desc">("desc");
  const [plantId, setPlantId] = useState<number | undefined>();
  const [category, setCategory] = useState("");
  const query = {
    search,
    page,
    per_page: pageSize,
    sort,
    direction,
    plant_id: plantId,
    category: category || undefined,
  };
  const { data, isLoading, isError } = useMasterRecords(config.module, query);
  const deleteMutation = useMasterDelete(config.module);
  const plants = useMasterOptions("plants");
  const records = data?.data ?? [];
  useEffect(() => {
    const timer = window.setTimeout(() => {
      setSearch(searchInput);
      setPage(1);
    }, 300);
    return () => window.clearTimeout(timer);
  }, [searchInput]);
  const toggleSort = (field: string) => {
    if (sort === field)
      setDirection((value) => (value === "asc" ? "desc" : "asc"));
    else {
      setSort(field);
      setDirection("asc");
    }
    setPage(1);
  };
  const remove = (record: MasterRecord) => onDeleteRequest(record);
  const value = (record: MasterRecord, key: string) => {
    const raw = record[key];
    if (key === "plant" || key === "line") {
      const relation = raw as Record<string, unknown> | null | undefined;
      return String(relation?.code ?? relation?.name ?? "—");
    }
    if (key === "is_active")
      return Boolean(raw) ? (
        <span className="inline-flex items-center gap-1 text-emerald-600">
          <CheckCircle2 className="size-4" /> Active
        </span>
      ) : (
        <span className="inline-flex items-center gap-1 text-muted-foreground">
          <CircleOff className="size-4" /> Inactive
        </span>
      );
    return raw === null || raw === undefined || raw === "" ? "—" : String(raw);
  };
  const icon = (field: string) =>
    sort !== field ? null : direction === "asc" ? (
      <ChevronUp className="size-3" />
    ) : (
      <ChevronDown className="size-3" />
    );
  return (
    <section className="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div className="flex flex-col gap-3 border-b border-slate-200 p-5 dark:border-slate-800">
        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 className="text-base font-semibold">{config.title} registry</h2>
            <p className="mt-1 text-sm text-muted-foreground">
              {data?.total ?? 0} records
            </p>
          </div>
          <div className="flex flex-wrap gap-2">
            <label className="flex h-10 items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm dark:border-slate-700">
              <Search className="size-4 text-muted-foreground" />
              <input
                className="w-52 bg-transparent outline-none"
                placeholder={`Search ${config.singular}...`}
                value={searchInput}
                onChange={(event) => setSearchInput(event.target.value)}
              />
            </label>
            {config.filter === "plant" && (
              <select
                className={selectClass}
                value={plantId ?? ""}
                onChange={(event) => {
                  setPlantId(
                    event.target.value ? Number(event.target.value) : undefined,
                  );
                  setPage(1);
                }}
              >
                <option value="">All plants</option>
                {(plants.data?.data ?? []).map((plant) => (
                  <option key={plant.id} value={plant.id}>
                    {String(plant.code)} — {String(plant.name)}
                  </option>
                ))}
              </select>
            )}
            {config.filter === "category" && (
              <select
                className={selectClass}
                value={category}
                onChange={(event) => {
                  setCategory(event.target.value);
                  setPage(1);
                }}
              >
                <option value="">All categories</option>
                <option value="CRITICAL">CRITICAL</option>
                <option value="MAJOR">MAJOR</option>
                <option value="MINOR">MINOR</option>
                <option value="UNACCEPTABLE">UNACCEPTABLE</option>
                <option value="-">-</option>
              </select>
            )}
            <select
              className={selectClass}
              value={pageSize}
              aria-label="Rows per page"
              onChange={(event) => {
                setPageSize(Number(event.target.value));
                setPage(1);
              }}
            >
              {[20, 50, 100].map((size) => (
                <option key={size} value={size}>
                  {size} / page
                </option>
              ))}
            </select>
          </div>
        </div>
      </div>
      {isLoading ? (
        <div className="space-y-3 p-5">
          {[1, 2, 3, 4].map((row) => (
            <Skeleton key={row} className="h-12 w-full" />
          ))}
        </div>
      ) : isError ? (
        <p className="p-6 text-sm text-destructive">
          Unable to load {config.title.toLowerCase()}.
        </p>
      ) : records.length === 0 ? (
        <p className="p-10 text-center text-sm text-muted-foreground">
          No records match the selected filters.
        </p>
      ) : (
        <>
          <div className="overflow-x-auto">
            <table className="w-full min-w-[860px] text-left text-sm">
              <thead className="bg-slate-50 text-xs uppercase tracking-wide text-muted-foreground dark:bg-slate-950/40">
                <tr>
                  {config.columns.map((column) => (
                    <th key={column.key} className="px-5 py-3">
                      {column.sortable ? (
                        <button
                          type="button"
                          className="flex items-center gap-1"
                          onClick={() => toggleSort(column.key)}
                        >
                          {column.label}
                          {icon(column.key)}
                        </button>
                      ) : (
                        column.label
                      )}
                    </th>
                  ))}
                  <th className="px-5 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                {records.map((record) => (
                  <tr
                    key={record.id}
                    className="hover:bg-slate-50 dark:hover:bg-slate-800/50"
                  >
                    {config.columns.map((column) => (
                      <td key={column.key} className="px-5 py-4">
                        {value(record, column.key)}
                      </td>
                    ))}
                    <td className="px-5 py-4">
                      <div className="flex justify-end gap-1">
                        <Button
                          variant="ghost"
                          size="icon-sm"
                          onClick={() => onEdit(record)}
                          aria-label={`Edit ${config.singular}`}
                        >
                          <Edit2 className="size-4" />
                        </Button>
                        <Button
                          variant="ghost"
                          size="icon-sm"
                          className="text-rose-600"
                          onClick={() => remove(record)}
                          disabled={deleteMutation.isPending}
                          aria-label={`Delete ${config.singular}`}
                        >
                          <Trash2 className="size-4" />
                        </Button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <div className="flex items-center justify-between border-t border-slate-200 px-5 py-3 dark:border-slate-800">
            <p className="text-xs text-muted-foreground">
              Page {data?.current_page ?? 1} of {data?.last_page ?? 1}
            </p>
            <div className="flex gap-2">
              <Button
                variant="outline"
                size="sm"
                disabled={(data?.current_page ?? 1) <= 1}
                onClick={() => setPage((value) => value - 1)}
              >
                <ChevronLeft className="size-4" /> Previous
              </Button>
              <Button
                variant="outline"
                size="sm"
                disabled={(data?.current_page ?? 1) >= (data?.last_page ?? 1)}
                onClick={() => setPage((value) => value + 1)}
              >
                Next <ChevronRight className="size-4" />
              </Button>
            </div>
          </div>
        </>
      )}
    </section>
  );
}

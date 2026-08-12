"use client";

import { Check, ChevronDown, Search, X } from "lucide-react";
import { useMemo, useState } from "react";
import { Button } from "@/components/ui/button";
import type { LookupOption } from "../types";

type SearchableSelectProps = {
  label: string;
  value?: number;
  options: LookupOption[];
  placeholder: string;
  onChange: (value?: number) => void;
  error?: string;
  disabled?: boolean;
  onSearch?: (value: string) => void;
};

export function SearchableSelect({ label, value, options, placeholder, onChange, error, disabled, onSearch }: SearchableSelectProps) {
  const [open, setOpen] = useState(false);
  const [search, setSearch] = useState("");
  const selected = options.find((option) => option.id === value);
  const filteredOptions = useMemo(() => {
    const term = search.trim().toLowerCase();
    if (!term) return options;
    return options.filter((option) => [option.mm_number, option.code, option.name, option.description, option.category].filter(Boolean).join(" ").toLowerCase().includes(term));
  }, [options, search]);

  return (
    <div className="space-y-2">
      <label className="text-sm font-medium">{label}</label>
      <div className="relative">
        <Button type="button" variant="outline" className="w-full justify-between font-normal" disabled={disabled} onClick={() => setOpen((current) => !current)}>
          <span className={selected ? "truncate" : "truncate text-muted-foreground"}>{selected ? `${selected.mm_number ? `${selected.mm_number} — ` : selected.code ? `${selected.code} — ` : ""}${selected.name || selected.description || "Unnamed record"}` : placeholder}</span>
          <ChevronDown className="size-4 shrink-0" />
        </Button>
        {open && (
          <div className="absolute z-20 mt-1 w-full rounded-md border bg-popover p-2 shadow-md">
            <div className="flex items-center gap-2 rounded border px-2">
              <Search className="size-4 text-muted-foreground" />
              <input autoFocus value={search} onChange={(event) => { setSearch(event.target.value); onSearch?.(event.target.value); }} placeholder="Search..." className="h-9 min-w-0 flex-1 bg-transparent text-sm outline-none" />
              {search && <button type="button" onClick={() => setSearch("")} aria-label="Clear search"><X className="size-4" /></button>}
            </div>
            <div className="mt-2 max-h-56 overflow-y-auto">
              {filteredOptions.length === 0 ? <p className="p-2 text-sm text-muted-foreground">No matching records.</p> : filteredOptions.map((option) => (
                <button key={option.id} type="button" className="flex w-full items-center justify-between rounded px-2 py-2 text-left text-sm hover:bg-accent" onClick={() => { onChange(option.id); setOpen(false); setSearch(""); }}>
                  <span className="truncate">{option.mm_number ? `${option.mm_number} — ` : option.code ? `${option.code} — ` : ""}{option.name || option.description || "Unnamed record"}</span>
                  {option.id === value && <Check className="size-4 shrink-0" />}
                </button>
              ))}
            </div>
            {value && <button type="button" className="mt-1 w-full border-t pt-2 text-left text-xs text-muted-foreground hover:text-foreground" onClick={() => { onChange(undefined); setOpen(false); }}>Clear selection</button>}
          </div>
        )}
      </div>
      {error && <p className="text-xs text-destructive">{error}</p>}
    </div>
  );
}

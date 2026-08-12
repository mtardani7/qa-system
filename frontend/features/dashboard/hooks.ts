"use client";

import { useQuery } from "@tanstack/react-query";
import { getDashboard } from "./services";
import type { DashboardQuery } from "./types";

export function useDashboard(query: DashboardQuery) { return useQuery({ queryKey: ["dashboard", query], queryFn: () => getDashboard(query), staleTime: 60_000, refetchInterval: 300_000 }); }

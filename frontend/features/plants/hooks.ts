"use client";

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { createPlant, deletePlant, getPlants, updatePlant, type PlantPayload, type PlantQuery } from "./services";

export const plantKeys = { all: ["plants"] as const, list: (query: PlantQuery) => ["plants", "list", query] as const };
export function usePlants(query: PlantQuery) { return useQuery({ queryKey: plantKeys.list(query), queryFn: () => getPlants(query) }); }
export function useCreatePlant() { const client = useQueryClient(); return useMutation({ mutationFn: (payload: PlantPayload) => createPlant(payload), onSuccess: () => client.invalidateQueries({ queryKey: plantKeys.all }) }); }
export function useUpdatePlant() { const client = useQueryClient(); return useMutation({ mutationFn: ({ id, payload }: { id: number; payload: PlantPayload }) => updatePlant(id, payload), onSuccess: () => client.invalidateQueries({ queryKey: plantKeys.all }) }); }
export function useDeletePlant() { const client = useQueryClient(); return useMutation({ mutationFn: (id: number) => deletePlant(id), onSuccess: () => client.invalidateQueries({ queryKey: plantKeys.all }) }); }

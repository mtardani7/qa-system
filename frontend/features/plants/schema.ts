import { z } from "zod";

export const plantSchema = z.object({
  code: z.string().trim().min(1, "Plant code is required.").max(30, "Plant code cannot exceed 30 characters."),
  name: z.string().trim().min(1, "Plant name is required.").max(150, "Plant name cannot exceed 150 characters."),
  description: z.string().trim().max(1000, "Description cannot exceed 1,000 characters.").optional(),
  is_active: z.boolean(),
});

export type PlantFormValues = z.infer<typeof plantSchema>;

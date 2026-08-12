import { z } from "zod";

const optionalDefectId = z.preprocess((value) => value === "" || value === undefined ? undefined : value, z.coerce.number().int().positive("Defect is required.").optional());
const optionalResult = z.preprocess((value) => value === "" || value === undefined ? undefined : value, z.enum(["OK", "OK, WITH NOTED", "SORTIR", "REJECT"]).optional());
const defectRowSchema = z.object({ defect_id: optionalDefectId, quantity: z.coerce.number().int().gt(0, "Quantity must be greater than zero."), remarks: z.string().trim().max(1000).optional() });
export const dailyReportSchema = z.object({
  plant_id: z.coerce.number().int().positive("Plant is required."),
  machine_id: z.coerce.number().int().positive("Machine is required."),
  shift_id: z.coerce.number().int().positive("Shift is required."),
  product_id: z.coerce.number().int().positive("Product is required."),
  product_type: z.enum(["FG", "WIP"], { message: "Product Type is required." }),
  checker_id: z.coerce.number().int().positive("QA Checker is required."),
  checker_2_id: z.preprocess((value) => value === "" || value === undefined ? undefined : value, z.coerce.number().int().positive("QA Checker 2 is invalid.").optional()),
  po_number: z.string().trim().min(1, "PO Number is required.").max(100),
  output_box: z.coerce.number().int().gt(0, "Output Box must be greater than zero.").max(1000000),
  defects: z.array(defectRowSchema).transform((rows) => rows.filter((row) => row.defect_id !== undefined)),
  production_date: z.string().regex(/^\d{4}-\d{2}-\d{2}$/, "Production date is required."),
  remarks: z.string().trim().max(2000).optional(),
  result: optionalResult,
  finding_range_box: z.string().trim().max(100).optional(),
  finding_observation: z.string().trim().max(2000).optional(),
}).superRefine((values, context) => {
  const ids = values.defects.map((row) => row.defect_id);
  if (new Set(ids).size !== ids.length) {
    context.addIssue({ code: "custom", path: ["defects"], message: "A defect can only be added once per report." });
  }
  if (values.checker_2_id !== undefined && values.checker_2_id === values.checker_id) {
    context.addIssue({ code: "custom", path: ["checker_2_id"], message: "QA Checker 2 must be different from QA Checker." });
  }
});

export type DailyReportFormValues = z.infer<typeof dailyReportSchema>;
export type DailyReportFormInput = z.input<typeof dailyReportSchema>;

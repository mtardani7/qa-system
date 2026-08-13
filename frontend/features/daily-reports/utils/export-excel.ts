import type { DailyReport } from "../types";

const escapeCell = (value: unknown) => String(value ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");

export function exportDailyReports(reports: DailyReport[]) {
  const headers = ["Production Date", "Shift", "Machine", "Product Type", "MM Number", "Item Name", "PO Number", "Qty / Box", "Output PCS", "Output Box", "Findings Range (Box)", "Findings Quantity (PCS)", "Defect Description", "Defect Category", "Result", "Checked by QA 1", "Checked by QA 2", "Remarks"];
  const rows = reports.map((report) => [report.production_date, report.shift.name, report.machine.name, report.product_type, report.mm_number, report.product.name || report.product.mm_number, report.po_number, report.qty_per_box, report.output_pcs, report.output_box, report.finding_range_box, report.total_defect, report.defects.map((item) => item.remarks || item.defect.description || item.defect.name).filter(Boolean).join(", "), report.defects.map((item) => item.defect.category).filter(Boolean).join(", "), report.result ?? "", report.qa_checker?.name, report.qa_checker_2?.name, report.remarks]);
  const html = `<table><thead><tr>${headers.map((header) => `<th>${escapeCell(header)}</th>`).join("")}</tr></thead><tbody>${rows.map((row) => `<tr>${row.map((cell) => `<td>${escapeCell(cell)}</td>`).join("")}</tr>`).join("")}</tbody></table>`;
  const blob = new Blob([`\ufeff${html}`], { type: "application/vnd.ms-excel;charset=utf-8" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = `daily-reports-${new Date().toISOString().slice(0, 10)}.xls`;
  link.click();
  URL.revokeObjectURL(url);
}

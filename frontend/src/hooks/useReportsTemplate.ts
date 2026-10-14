export type ReportType = "pdf" | "excel" | "csv" | "docx" | "fillable_pdf";

export type DocFeature = "fillable" | "uploadable" | "non_editable";

export type PaperSize = "A4" | "Letter" | "Legal";

export type Orientation = "portrait" | "landscape";

export type PdfDriver = "dompdf" | "snappy" | "puppeteer";

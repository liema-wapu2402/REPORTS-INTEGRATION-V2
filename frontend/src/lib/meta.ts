import { FileText, FileSpreadsheet, Table2, FileType2, Layers, Cpu, Chrome } from 'lucide-react'
import type { GeneratorKey } from '../types/report'

/** Colour + icon per generator, used consistently across the whole UI. */
export const GENERATOR_META: Record<GeneratorKey, { label: string; color: string; icon: typeof FileText }> = {
  dompdf: { label: 'DomPDF', color: '#fb7185', icon: Layers },
  snappy: { label: 'Snappy', color: '#fb923c', icon: Cpu },
  puppeteer: { label: 'Puppeteer', color: '#c084fc', icon: Chrome },
  excel: { label: 'Excel', color: '#34d399', icon: FileSpreadsheet },
  csv: { label: 'CSV', color: '#fbbf24', icon: Table2 },
  docx: { label: 'Word', color: '#60a5fa', icon: FileType2 },
}

export const TYPE_META: Record<string, { label: string; color: string }> = {
  pdf: { label: 'PDF', color: '#fb7185' },
  excel: { label: 'Excel', color: '#34d399' },
  csv: { label: 'CSV', color: '#fbbf24' },
  docx: { label: 'DOCX', color: '#60a5fa' },
  fillable_pdf: { label: 'Fillable PDF', color: '#c084fc' },
}

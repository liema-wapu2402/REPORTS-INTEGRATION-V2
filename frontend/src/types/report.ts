import type { ReportType } from '../hooks/useReportsTemplate'

export interface ReportRow {
  id: number
  type: ReportType
  driver: string | null
  title: string
  filename: string
  size_bytes: number
  created_at: string
}

export interface DashboardSummary {
  total_reports: number
  total_size_bytes: number
  by_type: Record<ReportType, number>
  by_driver: Record<'dompdf' | 'snappy' | 'puppeteer', number>
  activity: { date: string; label: string; count: number }[]
  recent: ReportRow[]
}

export type GeneratorKey = 'dompdf' | 'snappy' | 'puppeteer' | 'excel' | 'csv' | 'docx'

export interface SampleInfo {
  id: string
  generator: GeneratorKey
  format: 'pdf' | 'excel' | 'csv' | 'docx'
  name: string
  description: string
  paper: string | null
  orientation: string | null
  tags: string[]
}

export interface GeneratorInfo {
  key: GeneratorKey
  label: string
  format: SampleInfo['format']
  blurb: string
  samples: SampleInfo[]
}

export interface SamplePreview {
  sample: SampleInfo
  headings: string[]
  rows: Record<string, string | number>[]
  total_rows: number
}

export interface EngineHealth {
  key: GeneratorKey
  label: string
  ready: boolean
  fix: string | null
}

export interface SystemHealth {
  status: 'ok' | 'degraded'
  database: boolean
  engines: EngineHealth[]
  checked_at: string
}

export interface AboutInfo {
  name: string
  tagline: string
  version: string
  owner: string
  stack: { backend: string; frontend: string }
  totals: { reports_generated: number; storage_bytes: number }
}

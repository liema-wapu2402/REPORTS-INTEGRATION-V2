import axios from 'axios'

// In dev, Vite proxies /api to the Laravel backend (see vite.config.ts).
// In production, set VITE_API_URL to the deployed backend's URL.
const baseURL = import.meta.env.VITE_API_URL ?? '/api'

export const api = axios.create({ baseURL })

export interface ReportResponse {
  message: string
  path?: string
  filename?: string
  size_bytes?: number
  elapsed_ms?: number
  download_url: string
}

/** Pulls a readable message out of an axios error. */
export function errorMessage(err: unknown, fallback: string): string {
  const e = err as { response?: { data?: { message?: string; hint?: string } }; message?: string }
  const data = e.response?.data
  if (data?.message) return data.hint ? `${data.message} ${data.hint}` : data.message
  if (e.message === 'Network Error') return 'Cannot reach the backend. Start it with `php artisan serve`.'
  return fallback
}

/** Saves a generated file without navigating the SPA away. */
export function triggerDownload(url: string, filename?: string) {
  const link = document.createElement('a')
  link.href = url
  if (filename) link.download = filename
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

export function formatBytes(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / 1024 / 1024).toFixed(1)} MB`
}

/** Absolute-or-proxied URL for a stored report file. */
export function downloadUrl(filename: string): string {
  return `${baseURL}/reports/download/${encodeURIComponent(filename)}`
}

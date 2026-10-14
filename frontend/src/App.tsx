import { Routes, Route } from 'react-router-dom'
import DashboardLayout from './layouts/DashboardLayout'
import Dashboard from './pages/Dashboard'
import SamplesPage from './pages/SamplesPage'
import HistoryPage from './pages/HistoryPage'
import AboutPage from './pages/AboutPage'
import PdfReportPage from './pages/reports/PdfReportPage'
import ExcelReportPage from './pages/reports/ExcelReportPage'
import CsvReportPage from './pages/reports/CsvReportPage'
import DocxReportPage from './pages/reports/DocxReportPage'

export default function App() {
  return (
    <Routes>
      <Route element={<DashboardLayout />}>
        <Route index element={<Dashboard />} />
        <Route path="/samples" element={<SamplesPage />} />
        <Route path="/history" element={<HistoryPage />} />
        <Route path="/about" element={<AboutPage />} />
        <Route path="/reports/pdf" element={<PdfReportPage />} />
        <Route path="/reports/excel" element={<ExcelReportPage />} />
        <Route path="/reports/csv" element={<CsvReportPage />} />
        <Route path="/reports/docx" element={<DocxReportPage />} />
      </Route>
    </Routes>
  )
}

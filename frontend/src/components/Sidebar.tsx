import { useState } from 'react'
import { NavLink } from 'react-router-dom'
import {
  LayoutDashboard,
  FileText,
  FileSpreadsheet,
  Table2,
  FileType2,
  Boxes,
  History,
  Info,
  ChevronDown,
  X,
} from 'lucide-react'
import Logo from './Logo'

const REPORT_TYPES = [
  { to: '/reports/pdf', label: 'PDF', icon: FileText },
  { to: '/reports/excel', label: 'Excel', icon: FileSpreadsheet },
  { to: '/reports/csv', label: 'CSV', icon: Table2 },
  { to: '/reports/docx', label: 'Word', icon: FileType2 },
]

const link = ({ isActive }: { isActive: boolean }) =>
  `group relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 ${
    isActive
      ? 'bg-accent/10 text-accent shadow-[inset_2px_0_0_rgb(var(--accent))]'
      : 'text-mute hover:bg-line/40 hover:text-ink'
  }`

const subLink = ({ isActive }: { isActive: boolean }) =>
  `flex items-center gap-3 rounded-md px-3 py-2 text-sm transition-all duration-200 ${
    isActive ? 'text-accent' : 'text-mute hover:translate-x-1 hover:text-ink'
  }`

interface SidebarProps {
  open: boolean
  onClose: () => void
}

export default function Sidebar({ open, onClose }: SidebarProps) {
  const [reportsOpen, setReportsOpen] = useState(true)
  // On small screens, picking a destination should dismiss the drawer.
  const dismissOnMobile = () => {
    if (window.matchMedia('(max-width: 767px)').matches) onClose()
  }

  return (
    <aside
      className={`fixed inset-y-0 left-0 z-40 w-72 overflow-hidden border-r border-line bg-panel/95 backdrop-blur-xl
                  transition-all duration-300 ease-out md:sticky md:top-0 md:h-screen md:shrink-0
                  ${open ? 'translate-x-0 md:w-72' : '-translate-x-full md:w-0 md:translate-x-0 md:border-r-0'}`}
      aria-label="Main navigation"
    >
      <div className="flex h-full w-72 flex-col">
        <div className="flex items-center justify-between px-5 py-5">
          <Logo size={38} />
          <button onClick={onClose} className="btn-ghost h-8 w-8 !p-0 md:hidden" aria-label="Close menu">
            <X size={16} />
          </button>
        </div>

        <nav className="min-h-0 flex-1 space-y-1 overflow-y-auto px-3 pb-4" onClick={dismissOnMobile}>
          <NavLink to="/" end className={link}>
            <LayoutDashboard size={18} /> Dashboard
          </NavLink>
          <NavLink to="/samples" className={link}>
            <Boxes size={18} /> Sample gallery
          </NavLink>

          <div className="pt-3">
            <button
              onClick={(e) => {
                e.stopPropagation()
                setReportsOpen((o) => !o)
              }}
              className="flex w-full items-center justify-between rounded-md px-3 py-1.5 text-xs font-semibold text-mute hover:text-ink"
              aria-expanded={reportsOpen}
            >
              Custom reports
              <ChevronDown size={14} className={`transition-transform duration-300 ${reportsOpen ? 'rotate-180' : ''}`} />
            </button>
            <div className={`grid transition-all duration-300 ${reportsOpen ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'}`}>
              <div className="ml-4 min-h-0 space-y-0.5 overflow-hidden border-l border-line pl-2">
                {REPORT_TYPES.map(({ to, label, icon: Icon }) => (
                  <NavLink key={to} to={to} className={subLink}>
                    <Icon size={15} /> {label}
                  </NavLink>
                ))}
              </div>
            </div>
          </div>

          <div className="space-y-1 pt-3">
            <NavLink to="/history" className={link}>
              <History size={18} /> History
            </NavLink>
            <NavLink to="/about" className={link}>
              <Info size={18} /> About
            </NavLink>
          </div>
        </nav>

        <div className="border-t border-line px-5 py-4 text-xs text-mute">
          <span className="font-mono">v2.0</span> · AVANT GUARD
        </div>
      </div>
    </aside>
  )
}

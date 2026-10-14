import { useEffect, useState } from 'react'
import { Outlet, useLocation } from 'react-router-dom'
import { Menu } from 'lucide-react'
import Sidebar from '../components/Sidebar'
import ThemeToggle from '../components/ThemeToggle'
import StatusPill from '../components/StatusPill'
import AnimatedBackground from '../components/AnimatedBackground'
import { LogoMark } from '../components/Logo'

export default function DashboardLayout() {
  // Open by default on desktop, closed (drawer) on phones.
  const [sidebarOpen, setSidebarOpen] = useState(() => window.matchMedia('(min-width: 768px)').matches)
  const { pathname, hash } = useLocation()

  useEffect(() => {
    if (!hash) window.scrollTo({ top: 0 })
  }, [pathname, hash])

  return (
    <div className="flex min-h-screen">
      <AnimatedBackground />

      {sidebarOpen && (
        <div
          onClick={() => setSidebarOpen(false)}
          className="fixed inset-0 z-30 bg-bg/70 backdrop-blur-sm md:hidden"
          aria-hidden
        />
      )}

      <Sidebar open={sidebarOpen} onClose={() => setSidebarOpen(false)} />

      <div className="flex min-w-0 flex-1 flex-col">
        <header className="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-line bg-bg/70 px-4 py-3 backdrop-blur-xl sm:px-6">
          <div className="flex items-center gap-3">
            <button
              onClick={() => setSidebarOpen((v) => !v)}
              className="btn-ghost h-9 w-9 !p-0"
              aria-label="Toggle navigation"
              aria-expanded={sidebarOpen}
            >
              <Menu size={18} />
            </button>
            {!sidebarOpen && <LogoMark size={28} />}
          </div>
          <div className="flex items-center gap-2">
            <StatusPill />
            <ThemeToggle />
          </div>
        </header>

        <main className="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
          <Outlet />
        </main>
      </div>
    </div>
  )
}

import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { api } from '../lib/api'
import type { SystemHealth } from '../types/report'

/** Header pill showing whether the backend and its engines are reachable. */
export default function StatusPill() {
  const [state, setState] = useState<'loading' | 'ok' | 'degraded' | 'offline'>('loading')
  const [ready, setReady] = useState<[number, number]>([0, 0])

  useEffect(() => {
    let alive = true
    const check = () =>
      api
        .get<SystemHealth>('/system/health')
        .then((r) => {
          if (!alive) return
          setState(r.data.status)
          setReady([r.data.engines.filter((e) => e.ready).length, r.data.engines.length])
        })
        .catch(() => alive && setState('offline'))
    check()
    const t = setInterval(check, 30000)
    return () => {
      alive = false
      clearInterval(t)
    }
  }, [])

  const tone = { loading: 'bg-mute', ok: 'bg-ok', degraded: 'bg-warn', offline: 'bg-bad' }[state]
  const text = {
    loading: 'Checking…',
    ok: `${ready[0]}/${ready[1]} engines ready`,
    degraded: `${ready[0]}/${ready[1]} engines ready`,
    offline: 'Backend offline',
  }[state]

  return (
    <Link to="/about#status" className="chip hover:border-accent/50 hover:text-ink" title="Open system status">
      <span className="relative flex h-2 w-2">
        {state !== 'offline' && <span className={`absolute inline-flex h-full w-full animate-ring rounded-full ${tone}`} />}
        <span className={`relative inline-flex h-2 w-2 rounded-full ${tone}`} />
      </span>
      <span className="hidden sm:inline">{text}</span>
    </Link>
  )
}

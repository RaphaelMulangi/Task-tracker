import { useState } from 'react'
import { useAuth } from '../context/AuthContext'
import * as authApi from '../api/auth'

export default function Profile() {
  const { user, updateUser } = useAuth()
  const [form, setForm] = useState({
    name: user?.name || '',
    email: user?.email || '',
    whatsapp_number: user?.whatsapp_number || '',
  })
  const [status, setStatus] = useState('')
  const [error, setError] = useState('')
  const [busy, setBusy] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setStatus('')
    setBusy(true)
    try {
      const updated = await authApi.updateProfile(form)
      updateUser(updated)
      setStatus('Profile updated')
    } catch (err) {
      setError(err.response?.data?.message || 'Failed to update profile')
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="max-w-md">
      <h1 className="mb-6 text-xl font-semibold text-slate-900">Profile & notifications</h1>
      <div className="rounded-lg border border-slate-200 bg-white p-6">
        {status && <div className="mb-4 rounded-md bg-green-50 px-3 py-2 text-sm text-green-700">{status}</div>}
        {error && <div className="mb-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{error}</div>}
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="mb-1 block text-sm font-medium text-slate-700">Name</label>
            <input
              className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
              value={form.name}
              onChange={(e) => setForm({ ...form, name: e.target.value })}
            />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium text-slate-700">Email</label>
            <input
              type="email"
              className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
              value={form.email}
              onChange={(e) => setForm({ ...form, email: e.target.value })}
            />
            <p className="mt-1 text-xs text-slate-400">Reminders and overdue alerts are emailed here.</p>
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium text-slate-700">WhatsApp number</label>
            <input
              type="tel"
              placeholder="+260..."
              className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
              value={form.whatsapp_number}
              onChange={(e) => setForm({ ...form, whatsapp_number: e.target.value })}
            />
            <p className="mt-1 text-xs text-slate-400">
              WhatsApp reminders are coming soon — this number will be used once that's enabled.
            </p>
          </div>
          <button
            type="submit"
            disabled={busy}
            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
          >
            {busy ? 'Saving...' : 'Save changes'}
          </button>
        </form>
      </div>
    </div>
  )
}

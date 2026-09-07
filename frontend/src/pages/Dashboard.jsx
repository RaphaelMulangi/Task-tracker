import { useEffect, useState } from 'react'
import * as tasksApi from '../api/tasks'
import * as usersApi from '../api/users'
import TaskItem from '../components/TaskItem'
import TaskForm from '../components/TaskForm'
import { useAuth } from '../context/AuthContext'

const filters = [
  { key: 'all', label: 'All' },
  { key: 'pending', label: 'Pending' },
  { key: 'completed', label: 'Completed' },
  { key: 'overdue', label: 'Overdue' },
]

export default function Dashboard() {
  const { user } = useAuth()
  const isAdmin = user?.role === 'admin'
  const [tasks, setTasks] = useState([])
  const [users, setUsers] = useState([])
  const [filter, setFilter] = useState('all')
  const [loading, setLoading] = useState(true)
  const [showForm, setShowForm] = useState(false)
  const [editingTask, setEditingTask] = useState(null)

  const load = async (status = filter) => {
    setLoading(true)
    try {
      const data = await tasksApi.listTasks(status)
      setTasks(data)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    load(filter)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [filter])

  useEffect(() => {
    if (isAdmin) {
      usersApi.listUsers().then(setUsers)
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isAdmin])

  const handleCreate = async (payload) => {
    await tasksApi.createTask(payload)
    setShowForm(false)
    load()
  }

  const handleUpdate = async (payload) => {
    await tasksApi.updateTask(editingTask.id, payload)
    setEditingTask(null)
    load()
  }

  const handleToggle = async (task) => {
    await tasksApi.toggleTaskComplete(task.id)
    load()
  }

  const handleDelete = async (task) => {
    if (!confirm(`Delete "${task.title}"?`)) return
    await tasksApi.deleteTask(task.id)
    load()
  }

  return (
    <div>
      <div className="mb-6 flex items-center justify-between">
        <h1 className="text-xl font-semibold text-slate-900">{isAdmin ? 'All tasks' : 'Your tasks'}</h1>
        {isAdmin && (
          <button
            onClick={() => setShowForm(true)}
            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
          >
            + New task
          </button>
        )}
      </div>

      <div className="mb-4 flex gap-1">
        {filters.map((f) => (
          <button
            key={f.key}
            onClick={() => setFilter(f.key)}
            className={`rounded-md px-3 py-1.5 text-sm font-medium ${
              filter === f.key ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100'
            }`}
          >
            {f.label}
          </button>
        ))}
      </div>

      {loading ? (
        <p className="text-sm text-slate-400">Loading...</p>
      ) : tasks.length === 0 ? (
        <p className="rounded-lg border border-dashed border-slate-300 py-10 text-center text-sm text-slate-400">
          No tasks here yet.
        </p>
      ) : (
        <div className="space-y-2">
          {tasks.map((task) => (
            <TaskItem
              key={task.id}
              task={task}
              isAdmin={isAdmin}
              onToggle={handleToggle}
              onEdit={setEditingTask}
              onDelete={handleDelete}
            />
          ))}
        </div>
      )}

      {showForm && <TaskForm users={users} onSubmit={handleCreate} onCancel={() => setShowForm(false)} />}
      {editingTask && (
        <TaskForm
          initialTask={editingTask}
          users={users}
          onSubmit={handleUpdate}
          onCancel={() => setEditingTask(null)}
        />
      )}
    </div>
  )
}

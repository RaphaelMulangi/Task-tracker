import { useEffect, useMemo, useState } from 'react'
import { Calendar as BigCalendar, dateFnsLocalizer } from 'react-big-calendar'
import { format, parse, startOfWeek, getDay } from 'date-fns'
import { enUS } from 'date-fns/locale'
import 'react-big-calendar/lib/css/react-big-calendar.css'
import * as tasksApi from '../api/tasks'
import TaskForm from '../components/TaskForm'

const locales = { 'en-US': enUS }

const localizer = dateFnsLocalizer({
  format,
  parse,
  startOfWeek: () => startOfWeek(new Date(), { locale: enUS }),
  getDay,
  locales,
})

const statusColors = {
  pending: '#6366f1',
  completed: '#16a34a',
  overdue: '#dc2626',
}

export default function CalendarPage() {
  const [tasks, setTasks] = useState([])
  const [selectedTask, setSelectedTask] = useState(null)

  const load = () => tasksApi.listTasks('all').then(setTasks)

  useEffect(() => {
    load()
  }, [])

  const events = useMemo(
    () =>
      tasks
        .filter((t) => t.due_date)
        .map((t) => ({
          id: t.id,
          title: t.title,
          start: new Date(t.due_date),
          end: new Date(t.due_date),
          allDay: false,
          resource: t,
        })),
    [tasks]
  )

  const eventPropGetter = (event) => ({
    style: {
      backgroundColor: statusColors[event.resource.computed_status] || statusColors.pending,
      borderRadius: '4px',
      border: 'none',
      fontSize: '0.75rem',
    },
  })

  const handleUpdate = async (payload) => {
    await tasksApi.updateTask(selectedTask.id, payload)
    setSelectedTask(null)
    load()
  }

  return (
    <div>
      <h1 className="mb-6 text-xl font-semibold text-slate-900">Calendar</h1>
      <div className="flex gap-4 mb-4 text-xs text-slate-500">
        <span className="flex items-center gap-1"><span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: statusColors.pending }} /> Pending</span>
        <span className="flex items-center gap-1"><span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: statusColors.completed }} /> Completed</span>
        <span className="flex items-center gap-1"><span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: statusColors.overdue }} /> Overdue</span>
      </div>
      <div className="rounded-lg border border-slate-200 bg-white p-4">
        <BigCalendar
          localizer={localizer}
          events={events}
          startAccessor="start"
          endAccessor="end"
          style={{ height: 650 }}
          eventPropGetter={eventPropGetter}
          onSelectEvent={(event) => setSelectedTask(event.resource)}
        />
      </div>
      {selectedTask && (
        <TaskForm
          initialTask={selectedTask}
          onSubmit={handleUpdate}
          onCancel={() => setSelectedTask(null)}
        />
      )}
    </div>
  )
}

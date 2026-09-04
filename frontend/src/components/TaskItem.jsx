const statusStyles = {
  pending: 'bg-slate-100 text-slate-600',
  completed: 'bg-green-100 text-green-700',
  overdue: 'bg-red-100 text-red-700',
}

const priorityStyles = {
  low: 'bg-slate-50 text-slate-500',
  medium: 'bg-amber-50 text-amber-700',
  high: 'bg-rose-50 text-rose-700',
}

export default function TaskItem({ task, onToggle, onEdit, onDelete }) {
  return (
    <div className="flex items-start gap-3 rounded-lg border border-slate-200 bg-white p-4">
      <input
        type="checkbox"
        checked={task.status === 'completed'}
        onChange={() => onToggle(task)}
        className="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
      />
      <div className="min-w-0 flex-1">
        <div className="flex items-center gap-2">
          <p className={`truncate font-medium ${task.status === 'completed' ? 'text-slate-400 line-through' : 'text-slate-900'}`}>
            {task.title}
          </p>
          <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${statusStyles[task.computed_status]}`}>
            {task.computed_status}
          </span>
          <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${priorityStyles[task.priority]}`}>
            {task.priority}
          </span>
        </div>
        {task.description && <p className="mt-1 text-sm text-slate-500">{task.description}</p>}
        {task.due_date && (
          <p className="mt-1 text-xs text-slate-400">
            Due {new Date(task.due_date).toLocaleString()}
          </p>
        )}
      </div>
      <div className="flex shrink-0 gap-1">
        <button onClick={() => onEdit(task)} className="rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className="h-4 w-4">
            <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 000-3.712zM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 00-1.32 2.214l-.8 2.685a.75.75 0 00.933.933l2.685-.8a5.25 5.25 0 002.214-1.32l8.4-8.4z" />
          </svg>
        </button>
        <button onClick={() => onDelete(task)} className="rounded-md p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" className="h-4 w-4">
            <path fillRule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 013.878.512.75.75 0 11-.256 1.478l-.209-.035-1.005 13.07a3 3 0 01-2.991 2.77H8.084a3 3 0 01-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 01-.256-1.478A48.567 48.567 0 017.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 013.369 0c1.603.051 2.815 1.387 2.815 2.951zm-6.136-1.452a51.196 51.196 0 013.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 00-6 0v-.113c0-.794.609-1.428 1.364-1.452zm-.355 5.945a.75.75 0 10-1.5.058l.347 9a.75.75 0 101.499-.058l-.346-9zm5.48.058a.75.75 0 10-1.498-.058l-.347 9a.75.75 0 001.5.058l.345-9z" clipRule="evenodd" />
          </svg>
        </button>
      </div>
    </div>
  )
}

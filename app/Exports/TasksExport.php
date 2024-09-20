<?php

namespace App\Exports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TasksExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Obtener las tareas con los sprints relacionados usando la tabla pivote
        return Task::with('sprints')->get()->map(function ($task) {
            return [
                'name' => $task->name,
                'description' => $task->description,
                // Traducir el estado al español
                'status' => $this->translateStatus($task->status),
                // Traducir la prioridad al español
                'priority' => $this->translatePriority($task->priority),
                // Obtener el nombre del primer sprint al que pertenece la tarea
                'sprint' => $task->sprints->pluck('name')->first(),
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'Descripción',
            'Estado',
            'Prioridad',
            'Sprint'
        ];
    }

    // Función para traducir el estado al español
    private function translateStatus($status)
    {
        switch ($status) {
            case 'to do':
                return 'Por Hacer';
            case 'in progress':
                return 'En Proceso';
            case 'done':
                return 'Completada';
            default:
                return $status;
        }
    }

    // Función para traducir la prioridad al español
    private function translatePriority($priority)
    {
        switch ($priority) {
            case 'high':
                return 'Alta';
            case 'medium':
                return 'Media';
            case 'low':
                return 'Baja';
            default:
                return $priority;
        }
    }
}

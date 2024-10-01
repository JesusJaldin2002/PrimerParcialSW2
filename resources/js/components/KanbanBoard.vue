<template>
        <div class="board-wrapper">
            <div
                v-for="(column, index) in columns"
                :key="index"
                :data-column-index="index"
                :id="column.id"
                class="board-column"
            >
                <div class="column-header">
                    <h2>{{ column.displayName }}</h2>
                </div>

                <draggable
                    v-model="column.tasks"
                    group="tasks"
                    class="task-list"
                    @start="onDragStart"
                    @end="onDragEnd"
                    :itemKey="(task) => task.id"
                >
                    <template #item="{ element }">
                        <div class="task-card">
                            <div class="task-card-header">
                                <div class="task-card-title">
                                    {{ element.name }}
                                </div>
                                <button @click="confirmDeleteTask(element.id)" class="btn-delete">&times;</button>
                            </div>
                            <div class="task-card-description">
                                {{ element.description }}
                            </div>
                        </div>
                    </template>
                </draggable>

                <div class="add-task">
                    <input
                        v-model="newTask"
                        @keyup.enter="addTask(index)"
                        class="add-task-input"
                        placeholder="Añadir una tarea..."
                    />
                </div>
            </div>
        </div>
</template>



<script src="./kanbanBoard.js"></script>

<style scoped>

.task-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    border-bottom: 1px solid #d0d9e1;
}
.trello-board {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    background-color: #e0e0e0 ;
    border-radius: 12px;
    padding: 40px 20px;
    height: auto;
}

.board-wrapper {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    gap: 20px;
    max-width: 1200px;
    width: auto;
    background-color: #a8a7a7 ;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow-x: auto;
    height: auto;

}

.board-column {
    background-color: #f5f7fa;
    border-radius: 10px;
    width: 280px;
    display: flex;
    flex-direction: column;
    min-height: 200px;
    max-height: min-content;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease-in-out;
}

.board-column:hover {
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.16), 0 3px 6px rgba(0, 0, 0, 0.23);
    transform: scale(1.02);
}

.column-header {
    padding: 16px;
    font-weight: 600;
    font-size: 16px;
    color: #172b4d;
    border-bottom: 1px solid #dfe1e6;
}

.task-list {
    flex-grow: 1;
    overflow-y: auto;
    padding: 8px;
    max-height: 400px;
}

.task-card {
    background-color: #f7ebeb;
    border-radius: 4px;
    box-shadow: 0 1px 0 rgba(9, 30, 66, 0.25);
    cursor: pointer;
    margin-bottom: 8px;
    padding: 10px 12px;
    max-width: 100%;
    position: relative;
    transition: background-color 0.2s ease;
}

.task-card:hover {
    transform: scale(1.01);
    background-color: #ebe0e0;

}

.task-card-title {
    font-size: 14px;
    font-weight: 500;
    line-height: 20px;
    color: #172b4d;
}

.task-card-description {
    font-size: 12px;
    color: #5e6c84;
    margin-top: 4px;
}

.add-task {
    padding: 8px;
}

.add-task-input {
    width: 100%;
    border: none;
    background-color: rgba(255, 255, 255, 0.6);
    border-radius: 4px;
    padding: 8px 12px;
    font-size: 14px;
    color: #172b4d;
    transition: background-color 0.2s ease, box-shadow 0.2s ease;
}

.add-task-input:focus {
    background-color: #fff;
    outline: none;
    box-shadow: 0 0 6px rgba(0, 121, 191, 0.5);
}

.dragging .task-list {
    background-color: #e3e9ee;
    transition: background-color 0.2s ease;
}

.dragging .task-list:hover {
    background-color: #d0d9e1;
}

/* Estilos para la barra de desplazamiento */
.task-list::-webkit-scrollbar {
    width: 8px;
}

.task-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.task-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.task-list::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Media query para pantallas más pequeñas */
@media (max-width: 1024px) {
    .board-wrapper {
        flex-wrap: nowrap;
        justify-content: flex-start;
        padding: 20px;
    }

    .board-column {
        flex-shrink: 0;
    }
}

.btn-delete {
    background: none; /* Sin fondo */
    border: none;
    font-size: 20px;
    font-weight: bold;
    color: #e74c3c; /* Color rojo para la "X" */
    cursor: pointer;
    transition: color 0.3s ease;
}

.btn-delete:hover {
    color: #c0392b; /* Cambiar el color de la "X" al pasar el mouse */
}
</style>

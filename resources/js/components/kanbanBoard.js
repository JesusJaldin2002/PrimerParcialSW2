import axios from "axios";
import draggable from "vuedraggable";

export default {
    components: {
        draggable,
    },
    props: {
        projectId: {
            type: Number,
            required: true,
        },
        sprintId: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            socket: null, // Aquí almacenaremos la conexión del socket
            newTask: "",
            columns: [
                { name: "to do", displayName: "Por Hacer", tasks: [] },
                { name: "in progress", displayName: "En Proceso", tasks: [] },
                { name: "done", displayName: "Completado", tasks: [] },
            ],
            isDragging: false,
        };
    },
    methods: {
        addTask(columnIndex) {
            if (this.newTask.trim()) {
                this.columns[columnIndex].tasks.push({ name: this.newTask });
                this.newTask = "";
            }
        },
        onDragStart() {
            this.isDragging = true;
            document.body.classList.add("dragging");
        },
        onDragEnd(event) {
            this.isDragging = false;
            document.body.classList.remove("dragging");

            const idmovedTask = event.item._underlying_vm_.id;
            const columnIndex = event.to
                .closest("[data-column-index]")
                .getAttribute("data-column-index");
            const newStatus = this.columns[columnIndex].name; // Solo usa 'name' para actualizar el estado

            axios
                .put(`/projects/tasks/${idmovedTask}/update-status`, {
                    status: newStatus,
                })
                .then((response) => {
                    console.log("Estado de la tarea actualizado");
                    // Emitimos un evento de actualización de tarea
                    this.socket.emit('task-updated', { taskId: idmovedTask, newStatus });
                })
                .catch((error) => {
                    console.error("Error al actualizar el estado:", error);
                    console.error("Detalles del error:", error.response);
                });
        },
        fetchSprintTasks() {
            axios
                .get(`/projects/${this.projectId}/sprints/${this.sprintId}/tasks`)
                .then((response) => {
                    const tasks = response.data;
                    tasks.forEach((task) => {
                        if (task.status === "to do") {
                            this.columns[0].tasks.push(task);
                        } else if (task.status === "in progress") {
                            this.columns[1].tasks.push(task);
                        } else if (task.status === "done") {
                            this.columns[2].tasks.push(task);
                        }
                    });
                })
                .catch((error) => {
                    console.error("Error fetching tasks:", error);
                });
        },
        confirmDeleteTask(taskId) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: "No podrás revertir esta acción",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
            }).then((result) => {
                if (result.isConfirmed) {
                    this.deleteTask(taskId);
                }
            });
        },
        deleteTask(taskId) {
            axios
                .delete(`/projects/tasks/${taskId}/delete`)
                .then(() => {
                    console.log("Tarea eliminada con éxito");
                    this.columns.forEach((column) => {
                        column.tasks = column.tasks.filter((task) => task.id !== taskId);
                    });
                    this.socket.emit('task-deleted', taskId);
                })
                .catch((error) => {
                    console.error("Error al eliminar la tarea:", error);
                });
        },
        // Este método actualizará las tareas cuando lleguen los eventos del servidor
        handleTaskUpdated(data) {
            const { taskId, newStatus } = data;
            this.columns.forEach((column) => {
                column.tasks = column.tasks.filter(task => task.id !== taskId);
            });
            const columnIndex = this.columns.findIndex(column => column.name === newStatus);
            axios.get(`/projects/tasks/${taskId}`)
                .then((response) => {
                    this.columns[columnIndex].tasks.push(response.data);
                });
        },
        handleTaskDeleted(taskId) {
            this.columns.forEach((column) => {
                column.tasks = column.tasks.filter(task => task.id !== taskId);
            });
        }
    },
    mounted() {
        this.fetchSprintTasks();

        this.socket = io("http://localhost:4444"); // Conéctate al servidor de sockets

        // Emitir un evento cuando el usuario se conecte
        this.socket.emit("user-connected", {
            projectId: this.projectId,
            sprintId: this.sprintId,
        });

        // Escuchar desconexiones
        this.socket.on("disconnect", () => {
            console.log("El usuario se ha desconectado");
        });

        // Escuchar cuando el usuario se esté desconectando
        this.socket.on("disconnecting", () => {
            console.log("El usuario se está desconectando...");
        });

        // Escuchar eventos del servidor cuando una tarea sea actualizada o eliminada
        this.socket.on('task-updated', this.handleTaskUpdated);
        this.socket.on('task-deleted', this.handleTaskDeleted);
    },
};

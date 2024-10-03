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
            sprints: [],
            socket: null,
            newTask: {
                name: "",
                description: "",
                priority: "",
            },
            isModalOpen: false,
            columnIndexForNewTask: null,
            columns: [
                { name: "to do", displayName: "Por Hacer", tasks: [] },
                { name: "in progress", displayName: "En Proceso", tasks: [] },
                { name: "done", displayName: "Completado", tasks: [] },
            ],
            isDragging: false,
            handledTasks: new Set(), // Para evitar procesar la tarea dos veces
        };
    },
    methods: {
        getSprintNameById(sprintId) {
            const sprint = this.sprints.find(s => s.id === sprintId);
            return sprint ? sprint.name : 'Sprint Desconocido';  // Devuelve el nombre o un valor por defecto
        },
        
        // Método para cargar los sprints desde la API
        loadSprints() {
            axios.get(`/projects/${this.projectId}/sprints`)
                .then(response => {
                    this.sprints = response.data;  // Asignamos los sprints a la propiedad
                })
                .catch(error => {
                    console.error("Error al cargar los sprints:", error);
                });
        },
        openModal(columnIndex) {
            this.isModalOpen = true;
            this.columnIndexForNewTask = columnIndex;
        },
        closeModal() {
            this.isModalOpen = false;
            this.newTask = {
                name: "",
                description: "",
                priority: "",
            };
        },
        saveTask() {
            if (
                this.newTask.name &&
                this.newTask.description &&
                this.newTask.priority
            ) {
                const status = this.columns[this.columnIndexForNewTask].name; // Estado según la columna
                const sprintId = this.sprintId; // Sprint actual

                axios
                    .post(
                        `/projects/${this.projectId}/sprints/${sprintId}/tasks/create`,
                        {
                            name: this.newTask.name,
                            description: this.newTask.description,
                            priority: this.newTask.priority,
                            status: status, // Estado se determina por la columna donde se crea
                        }
                    )
                    .then((response) => {
                        const createdTask = response.data;
                        this.columns[this.columnIndexForNewTask].tasks.push(
                            createdTask
                        );
                        this.closeModal();
                        // Emitir el evento `task-modal-created`
                        this.socket.emit("task-modal-created", {
                            taskId: createdTask.id,
                            name: createdTask.name,
                            description: createdTask.description,
                            status: createdTask.status,
                            priority: createdTask.priority,
                            projectId: this.projectId,
                            sprintId: sprintId,
                            sprintName: this.getSprintNameById(sprintId)
                        });

                        this.socket.emit("task-add-kanban", {
                            taskId: createdTask.id,
                            name: createdTask.name,
                            description: createdTask.description,
                            status: createdTask.status,
                            priority: createdTask.priority,
                            projectId: this.projectId,
                            sprintId: sprintId,
                            sprintName: this.getSprintNameById(sprintId),
                        });
                    })
                    .catch((error) => {
                        console.error("Error al crear la tarea:", error);
                    });
            } else {
                alert("Por favor, completa todos los campos.");
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
            const newStatus = this.columns[columnIndex].name;
            const sprintId = this.sprintId;

            axios
                .put(`/projects/tasks/${idmovedTask}/update-status`, {
                    status: newStatus,
                    sprintId: sprintId,
                })
                .then(() => {
                    console.log("Estado de la tarea actualizado correctamente");
                    this.socket.emit("task-updated", {
                        taskId: idmovedTask,
                        newStatus,
                        sprintId,
                    });
                })
                .catch((error) => {
                    console.error("Error al actualizar el estado:", error);
                });
        },
        fetchSprintTasks() {
            axios
                .get(
                    `/projects/${this.projectId}/sprints/${this.sprintId}/tasks`
                )
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
                        column.tasks = column.tasks.filter(
                            (task) => task.id !== taskId
                        );
                    });
                    this.socket.emit("task-deleted", taskId);
                })
                .catch((error) => {
                    console.error("Error al eliminar la tarea:", error);
                });
        },
        handleTaskAdded(data) {
            const { taskId, name, status, description, projectId, sprintId } =
                data;

            if (projectId === this.projectId && sprintId === this.sprintId) {
                const columnIndex = this.columns.findIndex(
                    (column) => column.name === status
                );

                const existingTask = this.columns[columnIndex].tasks.find(
                    (task) => task.id === taskId
                );

                if (!existingTask) {
                    this.columns[columnIndex].tasks.push({
                        id: taskId,
                        name: name,
                        description: description,
                        status: status,
                    });
                }
            }
        },
        handleTaskUpdated(data) {
            const { taskId, newStatus, sprintId } = data;

            if (sprintId !== this.sprintId) {
                return;
            }

            this.columns.forEach((column) => {
                column.tasks = column.tasks.filter(
                    (task) => task.id !== taskId
                );
            });

            const columnIndex = this.columns.findIndex(
                (column) => column.name === newStatus
            );

            axios.get(`/projects/tasks/${taskId}`).then((response) => {
                this.columns[columnIndex].tasks.push(response.data);
            });
        },
        handleTaskDeleted(taskId) {
            this.columns.forEach((column) => {
                column.tasks = column.tasks.filter(
                    (task) => task.id !== taskId
                );
            });
        },
        handleTaskBacklogUpdated(data) {
            const { taskId, name, status, description, sprintId } = data;

            // Si la tarea ya fue manejada por task-sprint-changed, ignorarla
            if (this.handledTasks.has(taskId)) {
                console.log(
                    `Ignorando task-backlog-updated para la tarea ${taskId}`
                );
                this.handledTasks.delete(taskId);
                return;
            }

            if (sprintId !== this.sprintId) {
                return;
            }

            this.columns.forEach((column) => {
                column.tasks = column.tasks.filter(
                    (task) => task.id !== taskId
                );
            });

            const columnIndex = this.columns.findIndex(
                (column) => column.name === status
            );

            if (columnIndex !== -1) {
                this.columns[columnIndex].tasks.push({
                    id: taskId,
                    name: name,
                    description: description,
                    status: status,
                });
            }
        },
        handleTaskBacklogDeleted(taskId) {
            this.columns.forEach((column) => {
                column.tasks = column.tasks.filter(
                    (task) => task.id !== taskId
                );
            });
        },
        handleSprintDeleted(data) {
            const { sprintId } = data;

            // Agregar un log para asegurarse de que 'data' contiene lo que esperamos
            console.log("Datos recibidos en sprint-deleted:", data);

            // Verificar si el sprint eliminado es el sprint en el que estamos trabajando
            if (sprintId === this.sprintId) {
                Swal.fire({
                    title: "Sprint eliminado",
                    text: "El sprint ha sido eliminado. Serás redirigido al proyecto.",
                    icon: "warning",
                    confirmButtonText: "Aceptar",
                }).then(() => {
                    window.location.href = `/projects/show/${this.projectId}`;
                });
            } else {
                // Si no es el sprint actual, solo mostramos un log y no hacemos nada
                console.log(
                    `Sprint ${sprintId} eliminado, pero no es el sprint actual.`
                );
            }
        },
        handleTaskSprintChanged(data) {
            const { taskId, oldSprintId, newSprintId } = data;

            console.log(
                `Recibido task-sprint-changed: taskId=${taskId}, oldSprintId=${oldSprintId}, newSprintId=${newSprintId}`
            );

            if (oldSprintId === this.sprintId) {
                this.columns.forEach((column, index) => {
                    this.columns[index].tasks = column.tasks.filter(
                        (task) => task.id !== taskId
                    );
                });

                console.log(`Tarea ${taskId} eliminada del sprint anterior.`);
            }

            if (newSprintId === this.sprintId) {
                axios.get(`/projects/tasks/${taskId}`).then((response) => {
                    const task = response.data;
                    const columnIndex = this.columns.findIndex(
                        (column) => column.name === task.status
                    );

                    if (columnIndex !== -1) {
                        const taskExists = this.columns[columnIndex].tasks.some(
                            (t) => t.id === taskId
                        );
                        if (!taskExists) {
                            this.columns[columnIndex].tasks.push(task);

                            // Marcar la tarea como gestionada por task-sprint-changed
                            this.handledTasks.add(taskId);

                            this.$nextTick(() => {
                                this.$forceUpdate();
                            });
                        } else {
                            console.log(
                                `La tarea ${taskId} ya existe en la columna ${this.columns[columnIndex].name}`
                            );
                        }
                    }
                });
            }
        },
    },
    mounted() {
        this.loadSprints(); 
        this.fetchSprintTasks();

        this.socket = io("http://3.80.234.179:80");

        this.socket.emit("user-connected", {
            projectId: this.projectId,
            sprintId: this.sprintId,
        });

        this.socket.on("disconnect", () => {
            console.log("El usuario se ha desconectado");
        });

        this.socket.on("task-add-kanban", (data) => {
            console.log("Evento task-add-kanban recibido:", data);
            this.handleTaskAdded(data); // Reutilizamos el método `handleTaskAdded` para agregar la tarea en el Kanban
        });

        this.socket.on("task-added", this.handleTaskAdded);
        this.socket.on("task-backlog-updated", this.handleTaskBacklogUpdated);
        this.socket.on("task-backlog-deleted", this.handleTaskBacklogDeleted);
        this.socket.on("sprint-deleted", this.handleSprintDeleted);
        this.socket.on("task-updated", this.handleTaskUpdated);
        this.socket.on("task-deleted", this.handleTaskDeleted);
        this.socket.on("task-sprint-changed", (data) => {
            console.log("Evento task-sprint-changed recibido:", data);
            this.handleTaskSprintChanged(data);
        });
    },
};

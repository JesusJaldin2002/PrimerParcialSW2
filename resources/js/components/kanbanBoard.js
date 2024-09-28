import axios from 'axios';
import draggable from 'vuedraggable';

export default {
  components: {
    draggable,
  },
  props: {
    projectId: {
      type: Number,
      required: true
    },
    sprintId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      newTask: '',
      columns: [
        { name: 'to do', tasks: [] },
        { name: 'in progress', tasks: [] },
        { name: 'done', tasks: [] },
      ],
    };
  },
  methods: {
    addTask(columnIndex) {
      if (this.newTask.trim()) {
        this.columns[columnIndex].tasks.push({ name: this.newTask });
        this.newTask = '';
      }
    },
    onDrop(event) {
    const idmovedTask =event.item._underlying_vm_.id ;
   
    const columnIndex=event.to.closest('[data-column-index]').getAttribute('data-column-index');
  
    const newStatus = this.columns[columnIndex].name.toLowerCase();

    
    
    // Hacemos una solicitud para actualizar el estado de la tarea en la BD
    axios.put(`/projects/tasks/${idmovedTask}/update-status`, {
      status: newStatus,
    })
    .then(response => {
      console.log('Estado de la tarea actualizado');
    })
    .catch(error => {
      console.error("Error al actualizar el estado:", error);
      console.error("Detalles del error:", error.response);
    });
  },
    fetchSprintTasks() {
      // Hacemos la solicitud al servidor usando projectId y sprintId
      axios.get(`/projects/${this.projectId}/sprints/${this.sprintId}/tasks`)
        .then(response => {
          const tasks = response.data;
          
          
          // Repartimos las tareas en las columnas según el estado
          tasks.forEach(task => {
            if (task.status === 'to do') {
              this.columns[0].tasks.push(task);
            } else if (task.status === 'in progress') {
              this.columns[1].tasks.push(task);
            } else if (task.status === 'done') {
              this.columns[2].tasks.push(task);
            }
          });
        })
        .catch(error => {
          console.error("Error fetching tasks:", error);
        });
    }
  },
  mounted() {
    this.fetchSprintTasks();
  }
};
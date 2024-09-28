<template>
  <div class="container text-center">
    <div class="row row-cols-2 row-cols-lg-3 g-2 g-lg-3">
      <div :data-column-index='index' :id=column.id v-for="(column, index) in columns" :key="index" class="w-full md:w-1/3 border-primary bg-gray-100 p-4 rounded-lg">
        <div class="card border-primary mb-4">
          <h2 class="text-lg font-bold mb-4">{{ column.name }}</h2>
        </div>
        
        <draggable
          v-model="column.tasks"
          group="tasks"
          class="space-y-2 mb-4"
          @end="onDrop"
        >
          <template #item="{ element }">
            <div class="bg-white p-4 mb-4">
              {{element.name}}
              {{ element.description }}
            </div>
          </template>
        </draggable>

        <div class="mt-4">
          <input
            v-model="newTask"
            @keyup.enter="addTask(index)"
            class="w-full p-2 border rounded-lg"
            placeholder="Añadir tarea..."
          />
        </div>
      </div>
    </div>
  </div>
</template>
<script src="./kanbanBoard.js"></script>

<style scoped>
/* Estilos personalizados opcionales */
</style>

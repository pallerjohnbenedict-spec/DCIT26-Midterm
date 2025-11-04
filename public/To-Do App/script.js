const taskInput = document.getElementById("taskInput");
const taskDate = document.getElementById("taskDate");
const addTaskBtn = document.getElementById("addTaskBtn");
const taskList = document.getElementById("taskList");
const clearAllBtn = document.getElementById("clearAllBtn");

let tasks = JSON.parse(localStorage.getItem("tasks")) || [];

function saveTasks() {
  localStorage.setItem("tasks", JSON.stringify(tasks));
}

function renderTasks() {
  taskList.innerHTML = "";

  tasks.forEach((task, index) => {
    const li = document.createElement("li");
    if (task.completed) li.classList.add("completed");

    const info = document.createElement("div");
    info.classList.add("task-info");
    info.innerHTML = `<span>${task.text}</span>`;
    if (task.date)
      info.innerHTML += `<span class="task-date">${new Date(
        task.date
      ).toLocaleString()}</span>`;

    const buttons = document.createElement("div");
    buttons.classList.add("buttons");

    const completeBtn = document.createElement("button");
    completeBtn.textContent = "✔";
    completeBtn.onclick = () => toggleComplete(index);

    const editBtn = document.createElement("button");
    editBtn.textContent = "✏️";
    editBtn.classList.add("edit");
    editBtn.onclick = () => editTask(index);

    const deleteBtn = document.createElement("button");
    deleteBtn.textContent = "🗑️";
    deleteBtn.classList.add("delete");
    deleteBtn.onclick = () => deleteTask(index);

    buttons.append(completeBtn, editBtn, deleteBtn);

    li.append(info, buttons);
    taskList.appendChild(li);
  });
}

function addTask() {
  const text = taskInput.value.trim();
  const date = taskDate.value;

  if (!text) return alert("Please enter a task!");

  tasks.push({ text, date, completed: false });
  taskInput.value = "";
  taskDate.value = "";
  saveTasks();
  renderTasks();
}

function editTask(index) {
  const newText = prompt("Edit your task:", tasks[index].text);
  if (newText !== null) {
    tasks[index].text = newText.trim() || tasks[index].text;
    saveTasks();
    renderTasks();
  }
}

function deleteTask(index) {
  tasks.splice(index, 1);
  saveTasks();
  renderTasks();
}

function toggleComplete(index) {
  tasks[index].completed = !tasks[index].completed;
  saveTasks();
  renderTasks();
}

function clearAll() {
  if (confirm("Clear all tasks?")) {
    tasks = [];
    saveTasks();
    renderTasks();
  }
}

addTaskBtn.addEventListener("click", addTask);
clearAllBtn.addEventListener("click", clearAll);

window.addEventListener("DOMContentLoaded", renderTasks);

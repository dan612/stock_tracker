docReady(function() {
  let update = document.getElementById('update');
  update.addEventListener("click", (e) => {
    updateData(e);
  })
});

function updateData(event) {
  event.preventDefault();
  alert("hi");
  let progressBar = document.getElementById('progressbar');
  progressBar.classList.remove("hidden");
}

function docReady(fn) {
  // see if DOM is already available
  if (document.readyState === "complete" || document.readyState === "interactive") {
      // call on next available tick
      setTimeout(fn, 1);
  } else {
      document.addEventListener("DOMContentLoaded", fn);
  }
}
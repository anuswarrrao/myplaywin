$(document).ready(function () {
  /*=============== MARQUEE ===============*/
  $(".marquee").marquee({
    duration: 15000,
    gap: 50,
    delayBeforeStart: 0,
    direction: "left",
    duplicated: true,
  });

  /*=============== LATEST RESULTS and PREVIOUS RESULTS ===============*/
  const latestToggle = document.getElementById('latest-toggle');
  const previousToggle = document.getElementById('previous-toggle');
  const latestResults = document.getElementById('latest-results');
  const previousResults = document.getElementById('previous-results');

  // Ensure the elements exist before attaching event listeners
  if (latestToggle && previousToggle && latestResults && previousResults) {
    latestToggle.addEventListener('click', () => {
      latestResults.classList.remove('d-none');
      previousResults.classList.add('d-none');
    });

    previousToggle.addEventListener('click', () => {
      latestResults.classList.add('d-none');
      previousResults.classList.remove('d-none');
    });
  }
});
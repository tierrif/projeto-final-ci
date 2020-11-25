window.onload = function () {
  const submitBtn = document.getElementById('insert-btn');
  if (!submitBtn) return;

  submitBtn.onclick = function () {
    const form = document.getElementById('detailForm');
    if (form) form.submit()
  }
}
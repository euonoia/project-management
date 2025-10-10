let ws = new WebSocket('ws://localhost:8080');
function reloadTable() {
  fetch(window.location.pathname + window.location.search)
    .then(res => res.text())
    .then(html => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const newTable = doc.querySelector('table');
      if (newTable) {
        const oldTable = document.querySelector('table');
        if (oldTable) oldTable.parentNode.replaceChild(newTable, oldTable);
      }
    });
}
ws.onmessage = reloadTable;
setInterval(reloadTable, 1000);

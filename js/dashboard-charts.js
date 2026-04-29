(function () {
  function groupKey(dateString, range) {
    const d = new Date(dateString + 'T00:00:00');
    if (range === 'day') return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    if (range === 'week') {
      const first = new Date(d);
      first.setDate(d.getDate() - d.getDay());
      return 'Week of ' + first.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    }
    if (range === 'year') return String(d.getFullYear());
    return d.toLocaleDateString(undefined, { month: 'short', year: 'numeric' });
  }

  function showChartFallback(canvas, message) {
    if (!canvas) return;
    const fallback = document.createElement('p');
    fallback.className = 'text-muted small mb-0';
    fallback.textContent = message;
    canvas.replaceWith(fallback);
  }

  window.initClientBookingChart = function initClientBookingChart(rows) {
    const rangeSelect = document.getElementById('clientChartRange');
    const statusSelect = document.getElementById('clientChartStatus');
    const canvas = document.getElementById('clientBookingsChart');
    if (!rangeSelect || !statusSelect || !canvas) return;
    if (typeof Chart === 'undefined') {
      showChartFallback(canvas, 'Chart.js did not load. Check your internet connection or bundle Chart.js locally.');
      return;
    }

    const chart = new Chart(canvas, {
      type: 'bar',
      data: { labels: [], datasets: [{ label: 'Bookings', data: [] }] },
      options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    function payload() {
      const range = rangeSelect.value;
      const status = statusSelect.value;
      const grouped = new Map();
      rows.filter(row => status === 'all' || row.status === status).forEach(row => {
        const key = groupKey(row.date, range);
        grouped.set(key, (grouped.get(key) || 0) + 1);
      });
      return { labels: [...grouped.keys()], values: [...grouped.values()] };
    }

    function render() {
      const p = payload();
      chart.data.labels = p.labels;
      chart.data.datasets[0].data = p.values;
      chart.update();
    }

    rangeSelect.addEventListener('change', render);
    statusSelect.addEventListener('change', render);
    render();
  };

  window.initAdminDashboardChart = function initAdminDashboardChart(rows) {
    const metricSelect = document.getElementById('adminChartMetric');
    const rangeSelect = document.getElementById('adminChartRange');
    const parkSelect = document.getElementById('adminChartPark');
    const canvas = document.getElementById('adminBookingsChart');
    if (!metricSelect || !rangeSelect || !parkSelect || !canvas) return;
    if (typeof Chart === 'undefined') {
      showChartFallback(canvas, 'Chart.js did not load. Check your internet connection or bundle Chart.js locally.');
      return;
    }

    const labels = {
      bookings: 'Bookings',
      events: 'Events',
      attendance: 'Attendance',
      donations: 'Donation dollars'
    };

    const chart = new Chart(canvas, {
      type: 'bar',
      data: { labels: [], datasets: [{ label: labels[metricSelect.value] || 'Metric', data: [] }] },
      options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    function payload() {
      const metric = metricSelect.value;
      const range = rangeSelect.value;
      const park = parkSelect.value;
      const grouped = new Map();
      rows
        .filter(row => row.metric === metric)
        .filter(row => park === 'all' || row.park === park)
        .forEach(row => {
          const key = groupKey(row.date, range);
          grouped.set(key, (grouped.get(key) || 0) + Number(row.value || 0));
        });
      return { labels: [...grouped.keys()], values: [...grouped.values()] };
    }

    function render() {
      const p = payload();
      chart.data.labels = p.labels;
      chart.data.datasets[0].label = labels[metricSelect.value] || 'Metric';
      chart.data.datasets[0].data = p.values;
      chart.update();
    }

    metricSelect.addEventListener('change', render);
    rangeSelect.addEventListener('change', render);
    parkSelect.addEventListener('change', render);
    render();
  };

  window.initReservationPaymentForms = function initReservationPaymentForms() {
    document.querySelectorAll('.reservation-pay-form').forEach(form => {
      const method = form.querySelector('.reservation-method');
      const fields = form.querySelector('.reservation-card-fields');
      if (!method || !fields) return;
      function toggle() { fields.style.display = method.value === 'card' ? '' : 'none'; }
      method.addEventListener('change', toggle);
      toggle();
    });
  };
}());

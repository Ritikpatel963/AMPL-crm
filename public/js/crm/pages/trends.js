const labels = ['Apr 20 - Apr 26', 'Apr 27 - May 3', 'May 4 - May 10', 'May 11 - May 17', 'May 18 - May 24'];

const commonOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        font: { family: 'DM Sans', size: 11 },
        padding: 16,
        usePointStyle: true,
        pointStyleWidth: 10,
        boxHeight: 8,
      }
    },
    tooltip: {
      backgroundColor: '#1a1d2e',
      titleFont: { family: 'DM Sans', size: 12 },
      bodyFont:  { family: 'DM Sans', size: 12 },
      padding: 10,
      cornerRadius: 8,
    }
  },
  scales: {
    x: {
      ticks: {
        font: { family: 'DM Sans', size: 10 },
        maxRotation: 45,
        minRotation: 45,
        color: '#6b7280',
      },
      grid: { display: false },
      border: { color: '#e3e6ef' }
    },
    y: {
      ticks: { font: { family: 'DM Sans', size: 10 }, color: '#6b7280' },
      grid: { color: '#f0f2f7' },
      border: { display: false }
    }
  }
};

// Chart 1 — Total Calls vs Calls Connected (grouped bar)
new Chart(document.getElementById('chart1'), {
  type: 'bar',
  data: {
    labels,
    datasets: [
      {
        label: 'Total Calls',
        data: [11200, 12800, 14600, 15300, 8871],
        backgroundColor: '#294FCF',
        borderRadius: 4,
        barPercentage: 0.45,
        categoryPercentage: 0.75,
      },
      {
        label: 'Total Calls Connected',
        data: [4800, 4950, 5200, 5300, 992],
        backgroundColor: '#8EFA50',
        borderRadius: 4,
        barPercentage: 0.45,
        categoryPercentage: 0.75,
      }
    ]
  },
  options: { ...commonOptions }
});

// Chart 2 — Total Call Duration (single bar)
new Chart(document.getElementById('chart2'), {
  type: 'bar',
  data: {
    labels,
    datasets: [
      {
        label: 'Total Call Time',
        data: [4700, 5100, 5800, 6100, 2592],
        backgroundColor: '#294FCF',
        borderRadius: 4,
        barPercentage: 0.5,
        categoryPercentage: 0.7,
      }
    ]
  },
  options: { ...commonOptions }
});

// Chart 4 — Leads Added (single bar)
new Chart(document.getElementById('chart4'), {
  type: 'bar',
  data: {
    labels,
    datasets: [
      {
        label: 'Total Leads Added',
        data: [0, 38000, 80, 50, 30],
        backgroundColor: '#294FCF',
        borderRadius: 4,
        barPercentage: 0.5,
        categoryPercentage: 0.7,
      }
    ]
  },
  options: {
    ...commonOptions,
    scales: {
      ...commonOptions.scales,
      y: {
        ...commonOptions.scales.y,
        ticks: {
          ...commonOptions.scales.y.ticks,
          callback: v => v >= 1000 ? (v/1000) + 'k' : v
        }
      }
    }
  }
});

// Chart 5 — Lead Sources (spline/line)
new Chart(document.getElementById('chart5'), {
  type: 'line',
  data: {
    labels,
    datasets: [
      {
        label: 'File Upload',
        data: [3200, 47800, 0, 0, 0],
        borderColor: '#294FCF',
        backgroundColor: 'rgba(41,79,207,0.08)',
        pointBackgroundColor: '#294FCF',
        pointRadius: 4,
        tension: 0.4,
        fill: false,
      },
      {
        label: 'Walk in Lead',
        data: [120, 115, 130, 125, 118],
        borderColor: '#8EFA50',
        backgroundColor: 'rgba(142,250,80,0.08)',
        pointBackgroundColor: '#8EFA50',
        pointStyle: 'rectRot',
        pointRadius: 5,
        tension: 0.4,
        fill: false,
      }
    ]
  },
  options: {
    ...commonOptions,
    scales: {
      ...commonOptions.scales,
      y: {
        ...commonOptions.scales.y,
        ticks: {
          ...commonOptions.scales.y.ticks,
          callback: v => v >= 1000 ? (v/1000) + 'k' : v
        }
      }
    }
  }
});

// Switch button toggle (visual only)
document.querySelectorAll('.switch-wrapper').forEach(wrapper => {
  wrapper.querySelectorAll('.switch-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      wrapper.querySelectorAll('.switch-btn').forEach(b => b.classList.remove('selected'));
      btn.classList.add('selected');
    });
  });
});

document.addEventListener('DOMContentLoaded', function () {
    const page = document.querySelector('.login-report-page');
    if (!page) return;

    const loginEndpoint = page.dataset.reportLoginEndpoint;
    const hourlyEndpoint = page.dataset.reportHourlyEndpoint;
    const dayEndpoint = page.dataset.reportDayEndpoint;

    const loginTableBody = document.querySelector('#loginReportTable tbody');
    let hourlyChartInstance = null;
    let dayChartInstance = null;

    const commonChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
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
                ticks: { font: { family: 'DM Sans', size: 10 }, color: '#6b7280' },
                grid: { display: false },
                border: { color: '#e3e6ef' }
            },
            y: {
                beginAtZero: true,
                ticks: { font: { family: 'DM Sans', size: 10 }, color: '#6b7280' },
                grid: { color: '#f0f2f7' },
                border: { display: false }
            }
        }
    };

    function fetchLoginReport(params) {
        if (!loginEndpoint) return;
        const url = new URL('/api/calling-crm/' + loginEndpoint, window.location.origin);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status) {
                    renderLoginTable(result.data.data || result.data);
                }
            })
            .catch(error => console.error('Error fetching login report:', error));
    }

    function renderLoginTable(rows) {
        if (!loginTableBody) return;
        loginTableBody.innerHTML = '';
        if (!rows || rows.length === 0) {
            loginTableBody.innerHTML = '<tr><td colspan="5" class="text-center">No data available.</td></tr>';
            return;
        }

        rows.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row.user ? row.user.name : '--'}</td>
                <td>${row.ip_address || '--'}</td>
                <td><span title="${row.user_agent || ''}">${(row.user_agent || '--').substring(0, 30)}...</span></td>
                <td>${formatDate(row.logged_in_at)}</td>
                <td>${formatDate(row.logged_out_at)}</td>
            `;
            loginTableBody.appendChild(tr);
        });
    }

    function fetchHourlyReport(params) {
        if (!hourlyEndpoint) return;
        const url = new URL('/api/calling-crm/' + hourlyEndpoint, window.location.origin);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status) {
                    renderHourlyChart(result.data);
                }
            })
            .catch(error => console.error('Error fetching hourly report:', error));
    }

    function renderHourlyChart(data) {
        const ctx = document.getElementById('hourlyChart');
        if (!ctx) return;

        const labels = data.map(row => row.hour + ':00');
        const totalCalls = data.map(row => row.total_calls);
        const connectedCalls = data.map(row => row.connected_calls);

        if (hourlyChartInstance) {
            hourlyChartInstance.destroy();
        }

        hourlyChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Calls',
                        data: totalCalls,
                        backgroundColor: '#294FCF',
                        borderRadius: 4,
                    },
                    {
                        label: 'Connected Calls',
                        data: connectedCalls,
                        backgroundColor: '#8EFA50',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                ...commonChartOptions,
                plugins: {
                    ...commonChartOptions.plugins,
                    legend: {
                        display: true,
                        position: 'bottom',
                    }
                }
            }
        });
    }

    function fetchDayReport(params) {
        if (!dayEndpoint) return;
        const url = new URL('/api/calling-crm/' + dayEndpoint, window.location.origin);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status) {
                    renderDayChart(result.data);
                }
            })
            .catch(error => console.error('Error fetching day report:', error));
    }

    function renderDayChart(data) {
        const ctx = document.getElementById('dayChart');
        if (!ctx) return;

        const labels = data.map(row => row.date);
        const totalCalls = data.map(row => row.total_calls);
        const connectedCalls = data.map(row => row.connected_calls);

        if (dayChartInstance) {
            dayChartInstance.destroy();
        }

        dayChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Calls',
                        data: totalCalls,
                        borderColor: '#294FCF',
                        backgroundColor: 'rgba(41,79,207,0.08)',
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: 'Connected Calls',
                        data: connectedCalls,
                        borderColor: '#8EFA50',
                        backgroundColor: 'rgba(142,250,80,0.08)',
                        tension: 0.4,
                        fill: true,
                    }
                ]
            },
            options: {
                ...commonChartOptions,
                plugins: {
                    ...commonChartOptions.plugins,
                    legend: {
                        display: true,
                        position: 'bottom',
                    }
                }
            }
        });
    }

    function formatDate(dateString) {
        if (!dateString) return '--';
        const date = new Date(dateString);
        return date.toLocaleString();
    }

    function applyFilters() {
        const dateRangeEl = document.querySelector('input[name="login_report_date_range"]:checked');
        let fromDate = '';
        let toDate = '';

        if (dateRangeEl) {
            const range = dateRangeEl.value;
            const today = new Date();
            if (range === 'today') {
                fromDate = today.toISOString().split('T')[0];
                toDate = fromDate;
            } else if (range === 'yesterday') {
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                fromDate = yesterday.toISOString().split('T')[0];
                toDate = fromDate;
            } else if (range === 'last7') {
                const last7 = new Date(today);
                last7.setDate(last7.getDate() - 7);
                fromDate = last7.toISOString().split('T')[0];
                toDate = today.toISOString().split('T')[0];
            } else if (range === 'last30') {
                const last30 = new Date(today);
                last30.setDate(last30.getDate() - 30);
                fromDate = last30.toISOString().split('T')[0];
                toDate = today.toISOString().split('T')[0];
            } else if (range === 'month') {
                fromDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                toDate = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
            } else if (range === 'custom') {
                fromDate = document.querySelector('[data-custom-date-from]').value;
                toDate = document.querySelector('[data-custom-date-to]').value;
            }
        }

        const params = {
            from: fromDate,
            to: toDate
        };

        fetchLoginReport(params);
        fetchHourlyReport(params);
        fetchDayReport(params);
    }

    // Initial Load
    applyFilters();

    // Event Listeners for Filters
    const applyDateBtn = document.querySelector('[data-date-apply]');
    if (applyDateBtn) {
        applyDateBtn.addEventListener('click', applyFilters);
    }

    // Handle custom date range visibility
    document.querySelectorAll('input[name="login_report_date_range"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const customRange = document.querySelector('[data-custom-date-range]');
            if (this.value === 'custom') {
                customRange.removeAttribute('hidden');
            } else {
                customRange.setAttribute('hidden', '');
            }
        });
    });

    const filterToggles = document.querySelectorAll('[data-filter-toggle]');
    filterToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const parent = this.closest('.report-filter');
            parent.classList.toggle('open');
        });
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.report-filter')) {
            document.querySelectorAll('.report-filter.open').forEach(el => el.classList.remove('open'));
        }
    });

});

document.addEventListener('DOMContentLoaded', function () {
    const page = document.querySelector('.calling-crm-user-report[data-report-endpoint="reports/follow-ups"]');
    if (!page) return;

    const endpoint = page.dataset.reportEndpoint;
    const tableBody = document.querySelector('#followUpReportTable tbody');
    const paginationInfo = document.querySelector('[data-pagination-info]');
    const paginationButtons = document.querySelector('[data-pagination-buttons]');

    let currentPage = 1;

    function fetchReport(pageNumber = 1) {
        if (!endpoint) return;
        const url = new URL('/api/calling-crm/' + endpoint, window.location.origin);
        
        const status = document.querySelector('input[name="follow_up_status"]:checked')?.value || '';
        
        url.searchParams.append('page', pageNumber);
        if (status) {
            url.searchParams.append('status', status);
        }

        // Users and Campaigns would typically be gathered from the multi-select dropdowns here
        // For simplicity, we just pass the basics unless those are fully implemented with the global JS

        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status) {
                    renderTable(result.data.data || result.data);
                    renderPagination(result.data);
                }
            })
            .catch(error => console.error('Error fetching follow-up report:', error));
    }

    function renderTable(rows) {
        if (!tableBody) return;
        tableBody.innerHTML = '';
        if (!rows || rows.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center" style="padding: 20px;">No follow-ups found.</td></tr>';
            return;
        }

        rows.forEach(row => {
            const tr = document.createElement('tr');
            const statusClass = row.status === 'completed' ? 'text-green-600' : (row.status === 'missed' ? 'text-red-600' : 'text-yellow-600');
            
            tr.innerHTML = `
                <td>${row.lead ? row.lead.name : '--'}</td>
                <td>${row.user ? row.user.name : '--'}</td>
                <td>${row.campaign ? row.campaign.name : '--'}</td>
                <td>${formatDate(row.scheduled_at)}</td>
                <td class="${statusClass} font-medium" style="text-transform: capitalize;">${row.status || '--'}</td>
                <td><span title="${row.note || ''}">${(row.note || '--').substring(0, 30)}${row.note && row.note.length > 30 ? '...' : ''}</span></td>
            `;
            tableBody.appendChild(tr);
        });
    }

    function renderPagination(data) {
        if (!paginationInfo || !paginationButtons || !data.total) {
            if (paginationInfo) paginationInfo.innerHTML = '';
            if (paginationButtons) paginationButtons.innerHTML = '';
            return;
        }

        paginationInfo.innerHTML = `Showing ${data.from || 0} to ${data.to || 0} of ${data.total} entries`;

        let buttonsHtml = '';
        if (data.prev_page_url) {
            buttonsHtml += `<button type="button" class="btn btn-sm btn-outline" data-page="${data.current_page - 1}">Previous</button>`;
        }
        if (data.next_page_url) {
            buttonsHtml += `<button type="button" class="btn btn-sm btn-outline" data-page="${data.current_page + 1}" style="margin-left: 10px;">Next</button>`;
        }
        
        paginationButtons.innerHTML = buttonsHtml;

        paginationButtons.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', function() {
                currentPage = parseInt(this.dataset.page);
                fetchReport(currentPage);
            });
        });
    }

    function formatDate(dateString) {
        if (!dateString) return '--';
        const date = new Date(dateString);
        return date.toLocaleString();
    }

    function applyFilters() {
        currentPage = 1;
        fetchReport(currentPage);
    }

    // Initial Load
    applyFilters();

    // Event Listeners for Filters
    const applyStatusBtn = document.querySelector('[data-status-apply]');
    if (applyStatusBtn) {
        applyStatusBtn.addEventListener('click', function() {
            const label = document.querySelector('[data-status-label]');
            const checked = document.querySelector('input[name="follow_up_status"]:checked');
            if (label && checked) {
                label.textContent = checked.nextElementSibling.textContent;
            }
            applyFilters();
            this.closest('.report-filter').classList.remove('open');
        });
    }

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

document.addEventListener('DOMContentLoaded', function () {
    const page = document.querySelector('.calling-crm-user-report[data-report-endpoint="reports/campaign"]');
    if (!page) return;

    const endpoint = page.dataset.reportEndpoint;
    const tableBody = document.querySelector('#campaignReportTable tbody');
    const paginationInfo = document.querySelector('[data-pagination-info]');
    const paginationButtons = document.querySelector('[data-pagination-buttons]');

    let currentPage = 1;

    function fetchReport(pageNumber = 1) {
        if (!endpoint) return;
        const url = new URL('/api/calling-crm/' + endpoint, window.location.origin);
        
        const pipelineId = document.querySelector('input[name="campaign_pipeline_id"]')?.value;
        
        url.searchParams.append('page', pageNumber);
        if (pipelineId) {
            url.searchParams.append('pipeline_id', pipelineId);
        }

        fetch(url)
            .then(response => response.json())
            .then(result => {
                if (result.status) {
                    renderTable(result.data.data || result.data);
                    renderPagination(result.data);
                }
            })
            .catch(error => console.error('Error fetching campaign report:', error));
    }

    function renderTable(rows) {
        if (!tableBody) return;
        tableBody.innerHTML = '';
        if (!rows || rows.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center" style="padding: 20px;">No campaigns found.</td></tr>';
            return;
        }

        rows.forEach(row => {
            const tr = document.createElement('tr');
            const statusClass = row.status === 'active' ? 'text-green-600' : 'text-gray-500';
            
            tr.innerHTML = `
                <td class="font-medium">${row.name || '--'}</td>
                <td class="${statusClass}" style="text-transform: capitalize;">${row.status || '--'}</td>
                <td>${formatDate(row.created_at)}</td>
                <td>${row.total_leads || 0}</td>
                <td class="text-green-600">${row.converted_leads || 0}</td>
                <td class="text-red-600">${row.lost_leads || 0}</td>
                <td>${row.total_calls || 0}</td>
                <td class="text-green-600">${row.connected_calls || 0}</td>
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
        return date.toLocaleDateString();
    }

    function applyFilters() {
        currentPage = 1;
        fetchReport(currentPage);
    }

    // Initial Load
    applyFilters();

    // Event Listeners for Filters
    const applyPipelineBtn = document.querySelector('[data-pipeline-apply]');
    if (applyPipelineBtn) {
        applyPipelineBtn.addEventListener('click', function() {
            const label = document.querySelector('[data-pipeline-label]');
            const pipelineId = document.querySelector('input[name="campaign_pipeline_id"]').value;
            if (label) {
                label.textContent = pipelineId ? \`Pipeline \${pipelineId}\` : 'All Pipelines';
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

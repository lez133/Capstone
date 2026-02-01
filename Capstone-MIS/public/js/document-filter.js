document.addEventListener('DOMContentLoaded', function () {
    // PDF Viewer
    var pdfModalEl = document.getElementById('pdfViewerModal');
    var pdfModal = new bootstrap.Modal(pdfModalEl);
    var iframe = document.getElementById('pdfViewerIframe');
    var titleEl = document.getElementById('pdfViewerTitle');
    var downloadLink = document.getElementById('pdfDownloadLink');

    document.querySelectorAll('.view-pdf-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var url = btn.getAttribute('data-url');
            var downloadUrl = btn.getAttribute('data-download-url') || url;
            var filename = btn.getAttribute('data-filename') || 'document.pdf';

            iframe.src = url + (url.includes('?') ? '&' : '?') + 'inline=1&cb=' + Date.now();
            titleEl.textContent = filename;
            downloadLink.href = downloadUrl;
            pdfModal.show();
        });
    });

    pdfModalEl.addEventListener('hidden.bs.modal', function () {
        iframe.src = '';
    });

    // Search and filter
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('documentsTable');
    const filterSortSelect = document.getElementById('filterSortSelect');
    const barangayFilterSelect = document.getElementById('barangayFilterSelect');

    // Store original rows for reliable filtering
    const originalRows = Array.from(table.tBodies[0].rows);

    function applyFilters() {
        const search = searchInput.value.toLowerCase();
        const sortValue = filterSortSelect.value;
        const barangayValue = barangayFilterSelect.value;

        // Start from all original rows
        let rows = originalRows.slice();

        // Filter by barangay if selected
        if (barangayValue) {
            rows = rows.filter(row => {
                const brgy = row.cells[3].textContent.trim();
                return brgy === barangayValue;
            });
        }

        // Filter by search
        if (search) {
            rows = rows.filter(row => row.textContent.toLowerCase().includes(search));
        }

        // Filter/Sort by status or other criteria
        switch (sortValue) {
            case 'alphabetical':
                rows.sort((a, b) => a.cells[1].textContent.trim().localeCompare(b.cells[1].textContent.trim()));
                break;
            case 'recent':
                rows.sort((a, b) => new Date(b.cells[6].textContent.trim()) - new Date(a.cells[6].textContent.trim()));
                break;
            case 'last':
                rows.sort((a, b) => new Date(a.cells[6].textContent.trim()) - new Date(b.cells[6].textContent.trim()));
                break;
            case 'barangay':
                rows.sort((a, b) => a.cells[3].textContent.trim().localeCompare(b.cells[3].textContent.trim()));
                break;
            case 'pending':
                rows = rows.filter(row => row.cells[5].textContent.includes('Pending'));
                break;
            case 'validated':
                rows = rows.filter(row => row.cells[5].textContent.includes('Validated'));
                break;
            default:
                // No additional sort/filter
        }

        // Remove all rows
        while (table.tBodies[0].firstChild) {
            table.tBodies[0].removeChild(table.tBodies[0].firstChild);
        }
        // Add filtered/sorted rows
        rows.forEach(row => table.tBodies[0].appendChild(row));
    }

    searchInput.addEventListener('keyup', applyFilters);
    filterSortSelect.addEventListener('change', applyFilters);
    if (barangayFilterSelect) {
        barangayFilterSelect.addEventListener('change', applyFilters);
    }

    // Sortable columns
    document.querySelectorAll('.sortable').forEach(function(header) {
        header.addEventListener('click', function() {
            const sortKey = header.getAttribute('data-sort');
            const rows = Array.from(table.tBodies[0].rows);
            const asc = !header.classList.contains('asc');
            rows.sort((a, b) => {
                let aText = a.querySelector(`[data-key="${sortKey}"]`)?.textContent || a.cells[header.cellIndex].textContent;
                let bText = b.querySelector(`[data-key="${sortKey}"]`)?.textContent || b.cells[header.cellIndex].textContent;
                return asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
            });
            rows.forEach(row => table.tBodies[0].appendChild(row));
            document.querySelectorAll('.sortable').forEach(h => h.classList.remove('asc', 'desc'));
            header.classList.add(asc ? 'asc' : 'desc');
        });
    });
});

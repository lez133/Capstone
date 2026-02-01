document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchBarangay');
    const cardView = document.getElementById('cardView');
    const listView = document.getElementById('listView');
    const toggleButton = document.getElementById('toggleView');

    // snapshot server-rendered HTML so we can restore it when search is cleared
    const serverCardHtml = cardView ? cardView.innerHTML : '';
    const serverListHtml = listView ? listView.innerHTML : '';

    // safe selector route (set in blade)
    const selector = (typeof selectorRoute !== 'undefined') ? selectorRoute : ((typeof viewRoute !== 'undefined') ? viewRoute : null);

    // masterData holds the dataset currently represented (initial server data if available)
    let masterData = (typeof initialBarangays !== 'undefined' && Array.isArray(initialBarangays) && initialBarangays.length) ? initialBarangays : null;
    let currentData = null; // the data currently rendered (from search or master)
    let lastQuery = '';

    function setInitialView() {
        if (!cardView || !listView) return;
        if (window.innerWidth < 768) {
            cardView.style.display = 'flex';
            listView.style.display = 'none';
        } else {
            cardView.style.display = 'flex';
            listView.style.display = 'none';
        }
    }
    setInitialView();
    window.addEventListener('resize', setInitialView);

    // Toggle between Card and List View
    if (toggleButton) {
        toggleButton.addEventListener('click', function () {
            const isCardVisible = cardView.style.display === 'flex';
            if (isCardVisible) {
                cardView.style.display = 'none';
                listView.style.display = 'block';
                toggleButton.innerHTML = '<i class="fa fa-th"></i> Card View';
                // ensure list has content after toggle
                if ((!listView.querySelector('tbody') || listView.querySelector('tbody').children.length === 0) && (currentData || masterData)) {
                    renderListView(currentData || masterData);
                }
            } else {
                cardView.style.display = 'flex';
                listView.style.display = 'none';
                toggleButton.innerHTML = '<i class="fa fa-list"></i> List View';
                // ensure card has content after toggle
                if ((!cardView || cardView.children.length === 0) && (currentData || masterData)) {
                    renderCardView(currentData || masterData);
                }
            }
        });
    }

    // Render helpers
    function renderCardView(data) {
        if (!cardView) return;
        if (!data || data.length === 0) {
            // if there is no active search (lastQuery empty), restore server HTML
            if (!lastQuery) {
                cardView.innerHTML = serverCardHtml;
            } else {
                cardView.innerHTML = '<p class="text-muted">No barangays found.</p>';
            }
            return;
        }
        cardView.innerHTML = '';
        data.forEach(barangay => {
            const encryptedId = barangay.encrypted_id;
            const counts = barangay.distribution_counts || {};
            const link = (selector) ? `${selector}?barangay_id=${encodeURIComponent(encryptedId)}` : '#';
            const card = document.createElement('div');
            card.className = 'barangay-card';
            card.innerHTML = `
                <div class="card shadow-sm p-3 rounded-lg mb-3">
                    <h5 class="fw-bold mb-1">${barangay.barangay_name}</h5>
                    <div class="mb-2">
                        <span class="badge bg-info me-1">Upcoming: ${counts.upcoming ?? 0}</span>
                        <span class="badge bg-warning text-dark me-1">Ongoing: ${counts.ongoing ?? 0}</span>
                        <span class="badge bg-secondary me-1">Completed: ${counts.completed ?? 0}</span>
                        <span class="badge bg-primary">Total: ${counts.total ?? 0}</span>
                    </div>
                    <p class="text-muted small mb-2">View schedules for this barangay.</p>
                    <a href="${link}" class="btn btn-primary btn-sm">Select Barangay</a>
                </div>
            `;
            cardView.appendChild(card);
        });
    }

    function renderListView(data) {
        if (!listView) return;
        const tbody = listView.querySelector('tbody');
        if (!tbody) return;
        if (!data || data.length === 0) {
            if (!lastQuery) {
                tbody.innerHTML = serverListHtml ? serverListHtml.match(/<tbody[^>]*>([\s\S]*)<\/tbody>/i)?.[1] ?? serverListHtml : `<tr><td colspan="4" class="text-center text-muted">No barangays found.</td></tr>`;
            } else {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">No barangays found.</td></tr>`;
            }
            return;
        }
        tbody.innerHTML = '';
        data.forEach((barangay, index) => {
            const encryptedId = barangay.encrypted_id;
            const counts = barangay.distribution_counts || {};
            const link = (selector) ? `${selector}?barangay_id=${encodeURIComponent(encryptedId)}` : '#';
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${barangay.barangay_name}</td>
                <td><small>U: ${counts.upcoming ?? 0} / O: ${counts.ongoing ?? 0} / C: ${counts.completed ?? 0}</small></td>
                <td><a href="${link}" class="btn btn-primary btn-sm">View Schedules</a></td>
            `;
            tbody.appendChild(row);
        });
    }

    // Live Search Function
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function () {
            const query = searchInput.value.trim();
            lastQuery = query;
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (!query) {
                    // restore master/server content
                    currentData = null;
                    if (masterData) {
                        renderCardView(masterData);
                        renderListView(masterData);
                    } else {
                        // restore server HTML snapshots
                        renderCardView(null);
                        renderListView(null);
                    }
                    return;
                }

                fetch(`${searchRoute}?search=${encodeURIComponent(query)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    // Expect array of results. Merge counts from masterData when possible to preserve badges.
                    if (Array.isArray(data) && data.length) {
                        // if masterData exists, enrich results with distribution_counts when matching by encrypted_id or name
                        if (masterData) {
                            data = data.map(item => {
                                const match = masterData.find(b => b.encrypted_id === item.encrypted_id || b.barangay_name === item.barangay_name);
                                if (match) item.distribution_counts = match.distribution_counts;
                                return item;
                            });
                        }
                        currentData = data;
                        renderCardView(data);
                        renderListView(data);
                    } else {
                        currentData = [];
                        renderCardView([]);
                        renderListView([]);
                    }
                })
                .catch(error => {
                    console.error('Error fetching barangays:', error);
                    // keep server-rendered content on error
                });
            }, 250);
        });
    }

    // Initial load: render initialBarangays if defined, otherwise keep server-rendered DOM
    if (masterData && Array.isArray(masterData) && masterData.length) {
        renderCardView(masterData);
        renderListView(masterData);
    }
});

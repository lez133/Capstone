document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchBarangay');
    const cardView = document.getElementById('cardView');
    const listView = document.getElementById('listView');
    const toggleButton = document.getElementById('toggleView');

    // Responsive: Show card view by default on mobile, list view on desktop
    function setInitialView() {
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
    toggleButton.addEventListener('click', function () {
        const isCardVisible = cardView.style.display === 'flex';
        if (isCardVisible) {
            cardView.style.display = 'none';
            listView.style.display = 'block';
            toggleButton.innerHTML = '<i class="fa fa-th"></i> Card View';
        } else {
            cardView.style.display = 'flex';
            listView.style.display = 'none';
            toggleButton.innerHTML = '<i class="fa fa-list"></i> List View';
        }
    });

    // Live Search Function
    searchInput.addEventListener('input', function () {
        const query = searchInput.value.trim();

        fetch(`${searchRoute}?search=${encodeURIComponent(query)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            // enrich results with counts from initialBarangays if available
            const enriched = (data || []).map(item => {
                // try match by encrypted_id first, fallback to name
                let match = (typeof initialBarangays !== 'undefined') ?
                    initialBarangays.find(b => b.encrypted_id === item.encrypted_id || b.barangay_name === item.barangay_name)
                    : null;
                return {
                    barangay_name: item.barangay_name,
                    encrypted_id: item.encrypted_id,
                    counts: match ? match.counts : {
                        total_senior_registered: 0,
                        total_pwd_registered: 0,
                        flagged_senior: 0,
                        flagged_pwd: 0,
                        unregistered_senior: 0,
                        unregistered_pwd: 0
                    },
                    id: match ? match.id : null
                };
            });

            // if user cleared search, show initialBarangays (keeps full data)
            if (!query && typeof initialBarangays !== 'undefined') {
                renderCardView(initialBarangays);
                renderListView(initialBarangays);
            } else {
                renderCardView(enriched);
                renderListView(enriched);
            }
        })
        .catch(error => {
            console.error('Error fetching barangays:', error);
            // keep current view intact on error
        });
    });

    // Render Card View
    function renderCardView(data) {
        cardView.innerHTML = '';
        if (!data || data.length === 0) {
            cardView.innerHTML = '<p class="text-muted">No barangays found.</p>';
            return;
        }

        data.forEach(barangay => {
            const encryptedId = barangay.encrypted_id;
            const counts = barangay.counts || {};
            const card = document.createElement('div');
            card.className = 'barangay-card';
            card.innerHTML = `
                <div class="card shadow-sm p-3 rounded-lg mb-3">
                    <h5 class="fw-bold mb-1">${barangay.barangay_name}</h5>
                    <div class="mb-2">
                        <span class="badge bg-primary me-1">Seniors (specialized): ${counts.total_senior_registered ?? 0}</span>
                        <span class="badge bg-success me-1">PWDs (specialized): ${counts.total_pwd_registered ?? 0}</span>
                    </div>
                    <div class="small text-muted mb-3">
                        <div>Senior verified: ${counts.verified_senior ?? 0} | Unverified: ${counts.unverified_senior ?? 0} (Total: ${counts.total_senior ?? 0})</div>
                        <div>PWD verified: ${counts.verified_pwd ?? 0} | Unverified: ${counts.unverified_pwd ?? 0} (Total: ${counts.total_pwd ?? 0})</div>
                    </div>
                    <p class="text-muted small mb-2">View beneficiaries for this barangay.</p>
                    <a href="${viewRoute.replace('__ID__', encryptedId)}" class="btn btn-primary btn-sm">
                        View Beneficiaries
                    </a>
                </div>
            `;
            cardView.appendChild(card);
        });
    }

    // Render List View
    function renderListView(data) {
        const tbody = listView.querySelector('tbody');
        tbody.innerHTML = '';

        if (!data || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">No barangays found.</td></tr>`;
            return;
        }

        data.forEach((barangay, index) => {
            const encryptedId = barangay.encrypted_id;
            const counts = barangay.counts || {};
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${barangay.barangay_name}</td>
                <td>
                    <div>
                        <small>Seniors total (main): ${counts.total_senior ?? 0} | Verified: ${counts.verified_senior ?? 0}</small><br>
                        <small>PWDs total (main): ${counts.total_pwd ?? 0} | Verified: ${counts.verified_pwd ?? 0}</small>
                    </div>
                    <div class="mt-1 small text-muted">
                        <small>Sr unverified: ${counts.unverified_senior ?? 0}</small><br>
                        <small>PWD unverified: ${counts.unverified_pwd ?? 0}</small>
                    </div>
                </td>
                <td>
                    <a href="${viewRoute.replace('__ID__', encryptedId)}" class="btn btn-primary btn-sm">
                        View Beneficiaries
                    </a>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    // Initial load: render all barangays if available
    if (typeof initialBarangays !== 'undefined') {
        renderCardView(initialBarangays);
        renderListView(initialBarangays);
    }
});

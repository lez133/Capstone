document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchBarangay');
    const cardView = document.getElementById('cardView');
    const listView = document.getElementById('listView');
    const toggleButton = document.getElementById('toggleView');

    if (!cardView || !listView || !searchInput || !toggleButton) return;

    // parse initial barangays (comes from data attribute in blade)
    let barangaysData = [];
    try {
        barangaysData = JSON.parse(cardView.dataset.barangays || '[]');
    } catch (e) {
        console.error('Failed to parse barangays JSON', e);
        barangaysData = [];
    }

    const selector = (typeof selectorRoute !== 'undefined') ? selectorRoute : '/';

    // initial view
    function setInitialView() {
        cardView.style.display = 'flex';
        listView.style.display = 'none';
        toggleButton.innerHTML = '<i class="fa fa-list"></i> List View';
    }
    setInitialView();
    window.addEventListener('resize', setInitialView);

    toggleButton.addEventListener('click', function () {
        const isCardVisible = cardView.style.display !== 'none' && cardView.style.display !== '';
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

    function debounce(fn, delay = 200) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    const onSearch = debounce(function () {
        const q = searchInput.value.trim().toLowerCase();
        if (!q) {
            renderCardView(barangaysData);
            renderListView(barangaysData);
            return;
        }
        const filtered = barangaysData.filter(b => {
            const name = (b.barangay_name || '').toString().toLowerCase();
            const id = (b.id || '').toString();
            return name.includes(q) || id.includes(q);
        });
        renderCardView(filtered);
        renderListView(filtered);
    }, 200);

    searchInput.addEventListener('input', onSearch);

    function renderCardView(data) {
        cardView.innerHTML = '';
        if (!data || data.length === 0) {
            cardView.innerHTML = '<p class="text-muted">No barangays found.</p>';
            return;
        }
        data.forEach(b => {
            const enc = b.encrypted_id || b.id;
            const el = document.createElement('div');
            el.className = 'barangay-card';
            el.innerHTML = `
                <h5>${escapeHtml(b.barangay_name)}</h5>
                <p class="text-muted small mb-2">View beneficiaries for this barangay.</p>
                <a href="${selector}?barangay_id=${encodeURIComponent(enc)}" class="btn btn-primary btn-sm">Select Barangay</a>
            `;
            cardView.appendChild(el);
        });
    }

    function renderListView(data) {
        const tbody = listView.querySelector('tbody');
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!data || data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="3" class="text-center text-muted">No barangays found.</td></tr>`;
            return;
        }
        data.forEach((b, i) => {
            const enc = b.encrypted_id || b.id;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${i+1}</td>
                <td>${escapeHtml(b.barangay_name)}</td>
                <td><a href="${selector}?barangay_id=${encodeURIComponent(enc)}" class="btn btn-sm btn-primary">Select Barangay</a></td>
            `;
            tbody.appendChild(row);
        });
    }

    renderCardView(barangaysData);
    renderListView(barangaysData);

    function escapeHtml(s){ return (s||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
});

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchBarangay');
    const cardView = document.getElementById('cardView');
    const listView = document.getElementById('listView');
    const toggleButton = document.getElementById('toggleView');

    // Toggle between card view and list view
    toggleButton.addEventListener('click', function () {
        if (cardView.style.display === 'none') {
            cardView.style.display = 'flex';
            listView.style.display = 'none';
            toggleButton.innerHTML = '<i class="fa fa-th"></i> Toggle View';
        } else {
            cardView.style.display = 'none';
            listView.style.display = 'block';
            toggleButton.innerHTML = '<i class="fa fa-list"></i> Toggle View';
        }
    });

    // Default view
    cardView.style.display = 'flex';
    listView.style.display = 'none';

    // Live search functionality
    searchInput.addEventListener('input', function () {
        const query = searchInput.value;

        // Send AJAX request to search barangays
        fetch(`${searchRoute}?search=${query}`)
            .then(response => response.json())
            .then(data => {
                // Update card view
                cardView.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(barangay => {
                        const card = document.createElement('div');
                        card.className = 'barangay-card';
                        card.innerHTML = `
                            <h5>${barangay.barangay_name}</h5>
                            <p>View beneficiaries for this barangay.</p>
                            <a href="${searchRoute}?barangay=${barangay.id}">View Beneficiaries</a>
                        `;
                        cardView.appendChild(card);
                    });
                } else {
                    cardView.innerHTML = '<p class="text-muted">No barangays found.</p>';
                }

                // Update list view
                const listViewTable = listView.querySelector('tbody');
                listViewTable.innerHTML = '';
                if (data.length > 0) {
                    data.forEach((barangay, index) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${index + 1}</td>
                            <td>${barangay.barangay_name}</td>
                            <td>
                                <a href="${searchRoute}?barangay=${barangay.id}" class="btn btn-primary btn-sm">View Beneficiaries</a>
                            </td>
                        `;
                        listViewTable.appendChild(row);
                    });
                } else {
                    listViewTable.innerHTML = '<tr><td colspan="3" class="text-muted">No barangays found.</td></tr>';
                }
            })
            .catch(error => console.error('Error fetching barangays:', error));
    });
});

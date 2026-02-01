document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchBeneficiaries');
    const filterRadios = document.querySelectorAll('input[name="beneficiary_type"]');
    const items = document.querySelectorAll('.beneficiary-item');

    function filterBeneficiaries() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const selectedType = document.querySelector('input[name="beneficiary_type"]:checked').value;

        items.forEach(item => {
            const name = item.querySelector('h6')?.textContent.toLowerCase() || '';

            const rawType = item.getAttribute('data-type').toLowerCase();
            const type = rawType.replace(/\s+/g, ''); // normalize "senior citizen" → "seniorcitizen"

            const textContent = item.textContent.toLowerCase();
            const matchesSearch = textContent.includes(searchTerm);

            let matchesType = false;
            if (selectedType === 'all') matchesType = true;
            else if (selectedType === 'pwd') matchesType = (type === 'pwd');
            else if (selectedType === 'seniorcitizen') matchesType = (type === 'seniorcitizen');

            item.style.display = (matchesSearch && matchesType) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterBeneficiaries);
    filterRadios.forEach(radio => radio.addEventListener('change', filterBeneficiaries));

    filterBeneficiaries(); // initial load
});

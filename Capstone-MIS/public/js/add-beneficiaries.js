document.getElementById('birthday')?.addEventListener('change', function () {
    const birthday = new Date(this.value);
    const today = new Date();
    let age = today.getFullYear() - birthday.getFullYear();
    const monthDiff = today.getMonth() - birthday.getMonth();
    const dayDiff = today.getDate() - birthday.getDate();

    if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
        age--;
    }

    document.getElementById('age').value = age >= 0 ? age : '';
});

// Import functionality via AJAX
document.getElementById('importForm')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const progressMessage = document.getElementById('progressMessage');

    progressContainer.style.display = 'block';
    progressBar.style.width = '0%';
    progressMessage.textContent = 'Uploading and processing data...';

    fetch(this.getAttribute('action') || importRoute, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        progressBar.style.width = '100%';
        if (data.success) {
            progressMessage.textContent = `Import completed. Total rows: ${data.totalRows}, Inserted rows: ${data.insertedRows}.`;
            if (data.errors.length > 0) {
                progressMessage.textContent += ` Errors: ${data.errors.map(e => `Row ${e.row}: ${e.error}`).join(', ')}`;
            }
        } else {
            progressMessage.textContent = 'Import failed. Please check the file and try again.';
        }
    })
    .catch(error => {
        progressBar.style.width = '100%';
        progressMessage.textContent = 'An error occurred during the import process.';
        console.error(error);
    });
});

function submitAllForms() {
    const formData = new FormData();
    // Get all inputs from each form
    new FormData(document.getElementById('form3')).forEach((v, k) => formData.append(k, v));

    fetch('../api/create_organization.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        console.log(data);
        window.location.href = 'new-manage_users.php';
    })
    .catch(err => {
        console.error(err);
        alert("Submission failed.");
    });
}
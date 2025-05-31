function submitAllForms() {
    const formData = new FormData();

    // Get all inputs from each form
    new FormData(document.getElementById('form1')).forEach((v, k) => formData.append(k, v));
    new FormData(document.getElementById('form2')).forEach((v, k) => formData.append(k, v));
    new FormData(document.getElementById('form3')).forEach((v, k) => formData.append(k, v));

    fetch('../api/create_org_admin.php', {
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
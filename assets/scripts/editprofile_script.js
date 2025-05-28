document.getElementById("profile_pic").addEventListener("change", function (event) {
    const file = event.target.files[0];
    if (file) {
        const preview = document.getElementById("profile-preview");
        preview.src = URL.createObjectURL(file);
    }
});
document.addEventListener("DOMContentLoaded", function() {
    const joinButtons = document.querySelectorAll(".join-btn");
    const searchInput = document.getElementById("search");

    joinButtons.forEach(button => {
        button.addEventListener("click", function() {
            alert("Thank you for your interest! Someone will contact you soon.");
        });
    });


    searchInput.addEventListener("input", function() {
        const searchTerm = searchInput.value.toLowerCase();
        const orgCards = document.querySelectorAll(".org-card");
        
        orgCards.forEach(card => {
            const orgName = card.querySelector("h3").textContent.toLowerCase();
            if (orgName.includes(searchTerm)) {
                card.style.display = "block";
            } else {
                card.style.display = "none";
            }
        });
    });
});
    document.addEventListener("DOMContentLoaded", function() {
        const currentPage = window.location.pathname.split("/").pop(); 
        const navLinks = document.querySelectorAll(".nav-links a");

        navLinks.forEach(link => {
            if (link.getAttribute("href") === currentPage) {
                link.classList.add("active"); 
            }
        });
    });
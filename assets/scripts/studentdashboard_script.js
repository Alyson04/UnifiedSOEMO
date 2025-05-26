document.addEventListener("DOMContentLoaded", function() {
    const joinButtons = document.querySelectorAll(".join-btn");
    const searchInput = document.getElementById("search");

    joinButtons.forEach(button => {
        button.addEventListener("click", function() {
            alert("Thank you for your interest! Someone will contact you soon.");
        });
    });


    // searchInput.addEventListener("input", function() {
    //     const searchTerm = searchInput.value.toLowerCase();
    //     const orgCards = document.querySelectorAll(".org-card");
        
    //     orgCards.forEach(card => {
    //         const orgName = card.querySelector("h3").textContent.toLowerCase();
    //         if (orgName.includes(searchTerm)) {
    //             card.style.display = "block";
    //         } else {
    //             card.style.display = "none";
    //         }
    //     });
    // });
});
    document.addEventListener("DOMContentLoaded", function() {
        const currentPage = window.location.pathname.split("/").pop(); 
        const navLinks = document.querySelectorAll(".nav-list a");

        navLinks.forEach(link => {
            if (link.getAttribute("href") === currentPage) {
                link.classList.add("active"); 
            }
        });
    });
    const tutorialSteps = [
        { text: "Welcome to our site! Let's take a quick tour.", image: "step1.jpg" },
        { text: "This site has amazing features to explore.", image: "step2.jpg" },
        { text: "Click on menus to navigate easily.", image: "step3.jpg" },
        { text: "Check out our latest updates here.", image: "step4.jpg" },
        { text: "Congratulations! You're all set!", image: "step5.jpg" }
    ];
    let step = 0;

    function fadePopup(callback) {
        const popup = document.getElementById("popupBox");
        popup.classList.remove("show");
        setTimeout(() => {
            callback();
            popup.classList.add("show");
        }, 300);
    }

    function showTutorial(forceShow = false) {
        const seen = localStorage.getItem("tutorialSeen");
        if (forceShow || !seen || seen === "false") {
            document.getElementById("tutorial").style.display = "flex";
            fadePopup(updateTutorial);
        }
    }

    function reopenTutorial() {
        localStorage.setItem("tutorialSeen", "false");
        step = 0;
        showTutorial(true);
    }

    function updateTutorial() {
        const stepData = tutorialSteps[step];
        document.getElementById("tutorial-text").textContent = stepData.text;
        document.getElementById("tutorial-image").src = stepData.image;
        document.querySelector(".prev").disabled = step === 0;
        document.querySelector(".next").textContent = step === tutorialSteps.length - 1 ? "Finish" : "Next";
    }

    function neverShowAgain() {
        fetch('../api/set_tutorial_seen.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeTutorial();
            } else {
                alert('Failed to update preference.');
            }
        });
    }

    function nextStep() {
        if (step < tutorialSteps.length - 1) {
            step++;
            fadePopup(updateTutorial);
        } else {
            closeTutorial();
        }
    }

    function prevStep() {
        if (step > 0) {
            step--;
            fadePopup(updateTutorial);
        }
    }

    function closeTutorial() {
        const popup = document.getElementById("popupBox");
        popup.classList.remove("show");
        setTimeout(() => {
            document.getElementById("tutorial").style.display = "none";
            localStorage.setItem("tutorialSeen", "true");
        }, 300);
    }

    document.addEventListener("DOMContentLoaded", () => {
        localStorage.setItem("tutorialSeen", "false"); // 👈 Reset tutorial automatically for testing | Comment this line if you want actual testing
        setTimeout(() => showTutorial(), 100);
    });

    
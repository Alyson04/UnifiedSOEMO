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
     { 
        text: "Welcome to our platform! We're excited to have you. Let’s begin your tour by exploring the dashboard for students.", 
        image: "../assets/pictures/Tutorial1.png" 
    },
    { 
        text: "This platform offers amazing features to explore. You can connect with student organizations, join events, and much more!", 
        image: "../assets/pictures/Tutorial2.png" 
    },
    { 
        text: "Stay updated with your favorite organizations. You’ll never miss out on new opportunities or important announcements!", 
        image: "../assets/pictures/Tutorial3.png" 
    },
    { 
        text: "Explore and navigate through various events that match your interests. Our site is designed to keep you engaged!", 
        image: "../assets/pictures/Tutorial4.png" 
    },
    { 
        text: "Want to know more about us? Learn more about our mission, values, and how we bring students together.", 
        image: "../assets/pictures/Tutorial5.png" 
    },
    { 
        text: "Check out our 'About Us' section for more detailed information on how this platform can help you make the most out of your student life.", 
        image: "../assets/pictures/Tutorial6.png" 
    },
    { 
        text: "Ready to explore everything? Click through to see all that’s in store, or skip this tour and dive right in.", 
        image: "../assets/pictures/Tutorial7.png" 
    }
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

    

  let inactivityTime = function () {
    let timer;

    function logout() {
      // Redirect to logout or call a logout endpoint
      window.location.href = '../api/logout.php'; // adjust this to your logout route
    }

    function resetTimer() {
      clearTimeout(timer);
      timer = setTimeout(logout, 5 * 60 * 1000); // 5 minutes
    }

    // Events that reset the timer
    window.onload = resetTimer;
    document.onmousemove = resetTimer;
    document.onkeypress = resetTimer;
    document.onscroll = resetTimer;
    document.onclick = resetTimer;
  };

  inactivityTime();

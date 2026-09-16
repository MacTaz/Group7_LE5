// Session Countdown Timer for Forms / Protected Pages
document.addEventListener('DOMContentLoaded', function() {
    const timerElem = document.getElementById('sessionTimer');
    const badgeElem = document.getElementById('sessionBadge');
    if (!timerElem) return;

    // Default to value in data attribute or 15 minutes (900s)
    let timeLeft = parseInt(timerElem.getAttribute('data-seconds'), 10) || 900;
    const redirectUrl = timerElem.getAttribute('data-redirect') || '../login.php?expired=1';

    function renderTime() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerElem.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

        if (timeLeft <= 120 && badgeElem) {
            badgeElem.classList.add('warning');
        }

        if (timeLeft <= 0) {
            clearInterval(interval);
            timerElem.textContent = '0:00 (Expired)';
            alert('Your session has timed out due to inactivity.');
            window.location.href = redirectUrl;
        }
        timeLeft--;
    }

    renderTime();
    const interval = setInterval(renderTime, 1000);
});

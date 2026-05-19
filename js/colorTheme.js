// Helper: get cookie by name
function getCookie(name) {
    const cookies = document.cookie.split("; ");
    for (let cookie of cookies) {
        const [key, value] = cookie.split("=");
        if (key === name) return decodeURIComponent(value);
    }
    return null;
}

// Helper: set cookie
function setCookie(name, value, days = 30) {
    const expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/`;
}

// Helper: toggle theme
function toggleTheme() {
    let currentTheme = getCookie("colorTheme") || "darkTheme";
    let newTheme = currentTheme === "darkTheme" ? "lightTheme" : "darkTheme";
    
    setCookie("colorTheme", newTheme);
    applyTheme(newTheme);
    updateThemeIcon(newTheme);
}

// Helper: apply theme to body
function applyTheme(theme) {
    if (theme === "lightTheme") {
        document.body.classList.add("lightTheme");
        document.body.classList.remove("darkTheme");
    } else {
        document.body.classList.add("darkTheme");
        document.body.classList.remove("lightTheme");
    }
}

// Helper: update theme icon
function updateThemeIcon(theme) {
    const themeIcon = document.querySelector('.theme-icon');
    if (themeIcon) {
        themeIcon.textContent = theme === "darkTheme" ? "🌙" : "☀️";
    }
}

// Main logic
function handleUserCookie() {
    let colorTheme = getCookie("colorTheme");

    // If cookie doesn't exist → create default
    if (!colorTheme) {
        colorTheme = "darkTheme";
        setCookie("colorTheme", colorTheme);
    }

    // Apply theme
    applyTheme(colorTheme);
    updateThemeIcon(colorTheme);
}

// Event listener for theme toggle button
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
});

// Run it
handleUserCookie();

let themeSwitch = document.getElementsByClassName("colorTheme")[0];

themeSwitch.onclick = function() {
    let colorTheme = getCookie("colorTheme");

    // Do different things based on value
    switch (colorTheme) {
        case "lightTheme": 
            document.body.classList.add("darkTheme")
            document.body.classList.remove("lightTheme")
            setCookie("colorTheme", "darkTheme");
            break;

        case "darkTheme":
            document.body.classList.add("lightTheme")
            document.body.classList.remove("darkTheme")
            setCookie("colorTheme", "lightTheme");
            break;
    }
}
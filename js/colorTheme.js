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

// Main logic
function handleUserCookie() {
    let colorTheme = getCookie("colorTheme");

    // If cookie doesn't exist → create default
    if (!colorTheme) {
        colorTheme = "darkTheme";
        setCookie("colorTheme", colorTheme);
    }

    // Do different things based on value
    switch (colorTheme) {
        case "darkTheme":
            document.body.classList.add("darkTheme")
            document.body.classList.remove("lightTheme")
            break;

        case "lightTheme":
            document.body.classList.add("lightTheme")
            document.body.classList.remove("darkTheme")
            break;

        default:
            document.body.classList.add("darkTheme")
            document.body.classList.remove("lightTheme")
    }
}

// Run it
handleUserCookie();
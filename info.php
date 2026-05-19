<?php
$pageTitle = 'Information';
include_once __DIR__ . '/includes/header.php';
?>

<section class="section">
    <h1 data-i18n="info.title">Information & Contacts</h1>
    <div class="info-grid">
        <div class="info-card">
            <h2 data-i18n="info.hours.title">Opening Hours</h2>
            <p data-i18n="info.hours.weekdays">Monday - Thursday: 12:00 PM - 11:00 PM</p>
            <p data-i18n="info.hours.weekend">Friday - Sunday: 10:00 AM - 12:00 AM</p>
        </div>
        <div class="info-card">
            <h2 data-i18n="info.address.title">Address</h2>
            <p>123 Cinema Avenue</p>
            <p>Movie City, CA 90210</p>
        </div>
        <div class="info-card">
            <h2 data-i18n="info.contact.title">Contact</h2>
            <p>Email: support@cineview.com</p>
            <p>Phone: +1 (555) 123-4567</p>
        </div>
    </div>

    <div class="section">
        <h2 data-i18n="info.about.title">About CineView</h2>
        <p data-i18n="info.about.text">CineView is your go-to destination for the latest films, comfortable seating, and premium bar & cafeteria options. Our website lets you explore showtimes, current movies, and special menu features all in one place.</p>
    </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php';

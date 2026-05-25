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
            <p><span data-i18n="info.contact.email">Email:</span> support@cineview.com</p>
            <p><span data-i18n="info.contact.phone">Phone:</span> +1 (555) 123-4567</p>

        </div>

        <h2 data-i18n="info.about.title">About Grace</h2>
        <p class="infoText" data-i18n="info.about.text1">Grace is a cinema space for current films, comfortable screenings, and easy access to schedules, tickets, and visitor information.</p>
        <p class="infoText" data-i18n="info.about.text2">Use the site to browse movies, check showtimes, explore the bar menu, and manage your profile in one place.</p>
    </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php';

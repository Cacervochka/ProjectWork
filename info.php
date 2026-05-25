<?php
$pageTitle = 'Information';
include_once __DIR__ . '/includes/header.php';
?>

<section class="section aboutUsSection">
    <div class="sectionContent">
        <h2 data-i18n="info.title">Information & Contacts</h2>
        <div class="infoCardContainer">
            <div class="infoCard">
                <h3 data-i18n="info.hours.title">Opening Hours</h3>
                <p data-i18n="info.hours.weekdays">Monday-Thursday</p>
                <p>12 PM - 11 PM</p>
                <p data-i18n="info.hours.weekend">Friday - Sunday</p>
                <p>10 AM - 12 AM</p>
            </div>
            <div class="infoCard">
                <h3 data-i18n="info.address.title">Address</h3>
                <p>123 Cinema Avenue</p>
                <p>Movie City, CA 90210</p>
            </div>
            <div class="infoCard">
                <h3 data-i18n="info.contact.title">Contact</h3>
                <p data-i18n="info.contact.email">Email</p>
                <p>support@grace.com</p>
                <p data-i18n="info.contact.phone">Phone</p>
                <p>+1 (555) 123-4567</p>
            </div>
        </div>

        <h2 data-i18n="info.about.title">About Grace</h2>
        <p class="infoText" data-i18n="info.about.text1">Grace is a cinema space for current films, comfortable screenings, and easy access to schedules, tickets, and visitor information.</p>
        <p class="infoText" data-i18n="info.about.text2">Use the site to browse movies, check showtimes, explore the bar menu, and manage your profile in one place.</p>
    </div>

</section>

<?php include_once __DIR__ . '/includes/footer.php';

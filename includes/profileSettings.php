<div class="profile-nav">
    <button><a href="profile.php?viewSection=1" data-i18n="profile.nav.tickets">Tickets</a></button>
    <button><a href="profile.php?viewSection=2" data-i18n="profile.nav.reviews">Reviews</a></button>
    <button class="active"><a href="profile.php?viewSection=3" data-i18n="profile.nav.settings">Settings</a></button>
</div>

<div class="profileSubSection settingsSection">
    <h3 data-i18n="profile.nav.settings">Settings</h3>
    <p><span data-i18n="profile.email">Email:</span> <span><?= htmlspecialchars($user['email']) ?></span></p>
    <p><span data-i18n="profile.name">Name:</span> <span><?= htmlspecialchars($user['name']) ?></span></p>
    <span data-i18n="profile.settings.note">* We recommend using real name for smoother person identification, in case your email address becomes unavailable for any reason</span>
    <a href="profile.php?logout=1" class="button" data-i18n="profile.logout">Log Out</a>
</div>

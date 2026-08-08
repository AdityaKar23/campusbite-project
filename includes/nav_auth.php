<?php
/**
 * Auth-related navbar items (include inside navbar-nav)
 */
$user = getCurrentUser();

if (!$user) {
    return;
}
?>
<li class="nav-item">
    <a class="nav-link" href="profile.php"><i class="bi bi-person"></i> Profile</a>
</li>
<li class="nav-item">
    <span class="nav-link">
        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['full_name']) ?>
    </span>
</li>
<li class="nav-item">
    <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</li>

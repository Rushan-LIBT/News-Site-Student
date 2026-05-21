<?php
require_once __DIR__ . '/../includes/functions.php';
$page_title = 'About Us';
$active_nav = 'about';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="section-title">About NewsLanka</h1>

            <p class="lead">NewsLanka is Sri Lanka's premier digital news portal, delivering breaking news, in-depth analysis, and stories that matter to readers across the island and around the world.</p>

            <h3>Our Mission</h3>
            <p>To provide accurate, timely, and unbiased news coverage on topics that shape our nation — from politics and business to technology, sports, and culture. We believe an informed citizenry is the foundation of a strong democracy.</p>

            <h3>What We Cover</h3>
            <ul>
                <li><strong>Politics</strong> — Parliamentary affairs, elections, policy analysis</li>
                <li><strong>Sri Lankan Local News</strong> — Stories from every corner of the island</li>
                <li><strong>Technology</strong> — Tech trends, startups, AI, and digital transformation</li>
                <li><strong>Business</strong> — Economy, markets, entrepreneurship</li>
                <li><strong>Sports</strong> — Cricket, football, and athletic achievements</li>
                <li><strong>Entertainment</strong> — Cinema, music, and culture</li>
            </ul>

            <h3>Our Values</h3>
            <div class="row g-4 my-3">
                <div class="col-md-4 text-center">
                    <i class="fas fa-check-circle fa-3x text-accent mb-3"></i>
                    <h5>Accuracy</h5>
                    <p class="small text-muted">Every story is fact-checked and verified before publication.</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-balance-scale fa-3x text-accent mb-3"></i>
                    <h5>Impartiality</h5>
                    <p class="small text-muted">We present multiple perspectives without political bias.</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-bolt fa-3x text-accent mb-3"></i>
                    <h5>Speed</h5>
                    <p class="small text-muted">Breaking news delivered as it happens, around the clock.</p>
                </div>
            </div>

            <h3>Get In Touch</h3>
            <p>Have a story tip, feedback, or partnership inquiry? We'd love to hear from you. Visit our <a href="contact.php">Contact page</a> or reach us via email at <a href="mailto:info@newslanka.lk">info@newslanka.lk</a>.</p>

            <div class="alert alert-light border mt-4">
                <i class="fas fa-info-circle text-accent"></i>
                <strong>Academic Note:</strong> This website was developed as part of the <em>SE102.3 — Web Based Application Development</em> module assignment, demonstrating full-stack development with PHP, MySQL, Bootstrap, and modern web standards.
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>U-LITS - Uganda Livestock Identification & Traceability System</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">
    <meta name="description" content="Uganda Livestock Identification & Traceability System - Ensuring quality through traceability from farm to fork.">
    <meta name="keywords" content="livestock, uganda, traceability, cattle, farming, MAAIF, animal identification">
    <meta name="theme-color" content="#6a3a00">
    <meta property="og:type" content="website">
    <meta property="og:title" content="U-LITS - Uganda Livestock Identification & Traceability System">
    <meta property="og:description" content="Ensuring quality through traceability from farm to fork across Uganda.">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">
    <meta property="og:url" content="{{ url('/') }}">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>

@include('landing.partials.header')

{{-- ===== HERO ===== --}}
<section class="hero" id="home">
    <div class="container">
        <div class="hero-content">
            <div class="hero-tagline">Ensuring Quality through Traceability</div>
            <h1>Uganda Livestock Identification & Traceability System</h1>
            <p class="hero-text">
                A comprehensive digital platform for livestock identification, registration,
                and traceability across Uganda. From farm to fork, U-LITS ensures
                transparency, food safety, and disease control.
            </p>
            <div class="hero-buttons">
                <a href="#registers" class="btn btn-white btn-lg">Explore the System</a>
                <a href="#contact" class="btn btn-outline btn-lg" style="color:#fff;border-color:rgba(255,255,255,0.5);">Contact Us</a>
            </div>
            <div class="store-badges">
                <a href="https://play.google.com/store/apps/details?id=com.ulits" target="_blank" rel="noopener" class="store-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M3.609 1.814L13.792 12 3.61 22.186a.996.996 0 01-.61-.92V2.734a1 1 0 01.609-.92zm10.89 10.893l2.302 2.302-10.937 6.333 8.635-8.635zm3.199-3.199l2.807 1.626a1 1 0 010 1.732l-2.807 1.626L15.206 12l2.492-2.492zM5.864 2.658L16.8 8.99l-2.302 2.302-8.635-8.635z"/></svg>
                    <div class="store-badge-text">
                        <small>Get it on</small>
                        <span>Google Play</span>
                    </div>
                </a>
                <a href="https://apps.apple.com/app/ulits" target="_blank" rel="noopener" class="store-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                    <div class="store-badge-text">
                        <small>Download on the</small>
                        <span>App Store</span>
                    </div>
                </a>
            </div>
        </div>
        <div class="hero-image">
            <img src="{{ asset('images/register-lhr.jpg') }}" alt="U-LITS Mobile Application">
        </div>
    </div>
</section>

{{-- ===== ABOUT ===== --}}
<section class="about section" id="about">
    <div class="container">
        <div class="section-header">
            <span class="section-label">About U-LITS</span>
            <h2>What is U-LITS?</h2>
            <p>A national platform for livestock identification and traceability in Uganda</p>
        </div>
        <div class="about-content">
            <div class="about-text">
                <h3>Transforming Uganda's Livestock Sector</h3>
                <p>
                    The Uganda Livestock Identification and Traceability System (U-LITS) is a
                    comprehensive digital platform developed to modernize and streamline the management
                    of livestock data across Uganda. It provides a unified system for tracking animals
                    from birth through their entire lifecycle, encompassing health events, production
                    records, movements, and market transactions.
                </p>
                <p>
                    U-LITS supports the Ministry of Agriculture, Animal Industry and Fisheries (MAAIF)
                    in implementing livestock policies, disease surveillance, and ensuring food safety
                    standards. By digitizing livestock records, U-LITS empowers farmers, veterinary
                    officers, and government bodies with accurate, real-time data for better
                    decision-making.
                </p>
                <p>
                    The system operates through both a mobile application for field-level data
                    collection and a web portal for administration, reporting, and analytics.
                </p>
                <div class="about-stats">
                    <div class="stat-item animate-on-scroll">
                        <div class="stat-number">20,000+</div>
                        <div class="stat-label">Animals Tracked</div>
                    </div>
                    <div class="stat-item animate-on-scroll">
                        <div class="stat-number">63,000+</div>
                        <div class="stat-label">Events Recorded</div>
                    </div>
                    <div class="stat-item animate-on-scroll">
                        <div class="stat-number">100+</div>
                        <div class="stat-label">Districts Covered</div>
                    </div>
                </div>
            </div>
            <div class="about-image animate-on-scroll">
                <img src="{{ asset('images/register-ldr.jpg') }}" alt="U-LITS Animal Registration">
            </div>
        </div>
    </div>
</section>

{{-- ===== 4 REGISTERS (VERTICAL TREE) ===== --}}
<section class="registers section" id="registers">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Core Framework</span>
            <h2>The Four Regulatory Registers</h2>
            <p>The structured foundation of Uganda's livestock information system, each register building upon the previous</p>
        </div>

        <div class="register-tree">
            {{-- Register 1: LHR --}}
            <div class="register-item animate-on-scroll">
                <div class="register-node">1</div>
                <div class="register-card">
                    <div class="register-card-header">
                        <span class="register-badge">LHR</span>
                        <h3>Livestock Holdings Register</h3>
                    </div>
                    <div class="register-card-body">
                        <div class="register-card-text">
                            <p>
                                The foundational register. Every livestock-keeping establishment in
                                Uganda is registered with a unique Livestock Holding Code (LHC).
                                This includes farm location via GPS, owner details, administrative
                                region (District, Sub-county, Parish), farm size, and the species
                                managed on the premises.
                            </p>
                            <div class="register-tags">
                                <span class="register-tag">GPS Mapping</span>
                                <span class="register-tag">Holding Codes</span>
                                <span class="register-tag">Ownership</span>
                                <span class="register-tag">Farm Profiles</span>
                                <span class="register-tag">Multi-Species</span>
                            </div>
                        </div>
                        <img src="{{ asset('images/register-lhr.jpg') }}" alt="Livestock Holdings Register" class="register-image">
                    </div>
                </div>
            </div>

            {{-- Register 2: LDR --}}
            <div class="register-item animate-on-scroll">
                <div class="register-node">2</div>
                <div class="register-card">
                    <div class="register-card-header">
                        <span class="register-badge">LDR</span>
                        <h3>Livestock Data Register</h3>
                    </div>
                    <div class="register-card-body">
                        <div class="register-card-text">
                            <p>
                                Individual animal records within registered holdings. Each animal receives
                                a unique Electronic ID (E-ID) and Visual ID (V-ID). The register captures
                                species, breed, sex, date of birth, colour markings, physical description,
                                photos, and current health status. This is the backbone of individual
                                animal traceability.
                            </p>
                            <div class="register-tags">
                                <span class="register-tag">E-ID Tags</span>
                                <span class="register-tag">V-ID Tags</span>
                                <span class="register-tag">Breed Data</span>
                                <span class="register-tag">Photos</span>
                                <span class="register-tag">Health Status</span>
                            </div>
                        </div>
                        <img src="{{ asset('images/register-ldr.jpg') }}" alt="Livestock Data Register" class="register-image">
                    </div>
                </div>
            </div>

            {{-- Register 3: LER --}}
            <div class="register-item animate-on-scroll">
                <div class="register-node">3</div>
                <div class="register-card">
                    <div class="register-card-header">
                        <span class="register-badge">LER</span>
                        <h3>Livestock Events Register</h3>
                    </div>
                    <div class="register-card-body">
                        <div class="register-card-text">
                            <p>
                                Records all significant events throughout an animal's lifetime. This
                                includes vaccination campaigns, disease incidents, treatments administered,
                                daily milk production, weight measurements, reproduction events (births,
                                breeding), roll-call verification, and any disease test results. Enables
                                complete health and production history tracking.
                            </p>
                            <div class="register-tags">
                                <span class="register-tag">Vaccinations</span>
                                <span class="register-tag">Treatments</span>
                                <span class="register-tag">Milk Production</span>
                                <span class="register-tag">Weight Monitoring</span>
                                <span class="register-tag">Roll Calls</span>
                            </div>
                        </div>
                        <img src="{{ asset('images/register-ler.jpg') }}" alt="Livestock Events Register" class="register-image">
                    </div>
                </div>
            </div>

            {{-- Register 4: LMR --}}
            <div class="register-item animate-on-scroll">
                <div class="register-node">4</div>
                <div class="register-card">
                    <div class="register-card-header">
                        <span class="register-badge">LMR</span>
                        <h3>Livestock Movement Register</h3>
                    </div>
                    <div class="register-card-body">
                        <div class="register-card-text">
                            <p>
                                Tracks all movements of animals between holdings, to markets, and to
                                slaughter facilities. Each movement is documented with origin, destination,
                                date, purpose (sale, breeding, slaughter), and transport details. This
                                register is essential for disease outbreak tracing, market transparency,
                                and regulatory compliance.
                            </p>
                            <div class="register-tags">
                                <span class="register-tag">Movement Permits</span>
                                <span class="register-tag">Market Sales</span>
                                <span class="register-tag">Slaughter Records</span>
                                <span class="register-tag">Disease Tracing</span>
                                <span class="register-tag">Compliance</span>
                            </div>
                        </div>
                        <img src="{{ asset('images/register-lmr.jpg') }}" alt="Livestock Movement Register" class="register-image">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== FARM MANAGEMENT / SERVICES ===== --}}
<section class="services section" id="services">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Farm Management</span>
            <h2>Comprehensive Farm Tools</h2>
            <p>Modules built into U-LITS to support every aspect of livestock farm management</p>
        </div>
        <div class="services-grid">
            {{-- Marketplace --}}
            <div class="service-card animate-on-scroll">
                <div class="service-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                </div>
                <h3>Marketplace</h3>
                <p>Buy and sell livestock with full traceability. Each animal's complete history, health records, and certifications are available to buyers, building trust and fair pricing.</p>
            </div>

            {{-- Milk Production --}}
            <div class="service-card animate-on-scroll">
                <div class="service-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <h3>Milk Production</h3>
                <p>Track daily milk yields per animal. Monitor production trends, identify high and low performers, and optimize feeding strategies based on real production data.</p>
            </div>

            {{-- Treatment --}}
            <div class="service-card animate-on-scroll">
                <div class="service-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <h3>Treatment & Health</h3>
                <p>Record vaccinations, disease incidents, drug treatments, and vet visits. Maintain complete health histories and receive alerts for upcoming vaccination schedules.</p>
            </div>

            {{-- Weight Monitoring --}}
            <div class="service-card animate-on-scroll">
                <div class="service-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                </div>
                <h3>Weight Monitoring</h3>
                <p>Log periodic weight measurements to track growth rates and body condition. Identify animals ready for market and optimize feeding programs for maximum returns.</p>
            </div>

            {{-- Photos & Documentation --}}
            <div class="service-card animate-on-scroll">
                <div class="service-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3>Photos & Records</h3>
                <p>Capture and store animal photos for visual identification. Generate QR codes, barcodes, and PDF labels for packaging records with full traceability information.</p>
            </div>

            {{-- Farm Workers --}}
            <div class="service-card animate-on-scroll">
                <div class="service-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h3>Farm Workers</h3>
                <p>Manage farm staff, assign roles (farmer, herder, milker), and track worker activities. Ensure accountability and efficient task distribution across the farm.</p>
            </div>
        </div>
    </div>
</section>

{{-- ===== WEB PORTAL ===== --}}
<section class="portal section" id="portal">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Administration</span>
            <h2>U-LITS Web Portal</h2>
            <p>A powerful web-based administration and analytics platform</p>
        </div>
        <div class="portal-content">
            <div class="portal-text">
                <h3>Central Command for Livestock Data</h3>
                <p>
                    The U-LITS Web Portal provides administrators, veterinary officers, and
                    government officials with a comprehensive dashboard for managing all livestock
                    data collected through the mobile application.
                </p>
                <div class="portal-features">
                    <div class="portal-feature">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Real-time dashboards with farm analytics and KPIs</span>
                    </div>
                    <div class="portal-feature">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>User and role management (Admin, DVO, SCVO, Farmer)</span>
                    </div>
                    <div class="portal-feature">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Packaging and labelling system with QR codes and barcodes</span>
                    </div>
                    <div class="portal-feature">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Disease surveillance and outbreak mapping</span>
                    </div>
                    <div class="portal-feature">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>Export-ready reporting for MAAIF compliance</span>
                    </div>
                </div>
                <a href="{{ url('/admin') }}" class="btn btn-primary">Access Web Portal</a>
            </div>
            <div class="portal-image animate-on-scroll">
                <img src="{{ asset('images/phone-mockup.png') }}" alt="U-LITS Web Portal">
            </div>
        </div>
    </div>
</section>

{{-- ===== HOW IT WORKS ===== --}}
<section class="how-it-works section" id="how-it-works">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Process</span>
            <h2>How It Works</h2>
            <p>A simple, structured process for complete livestock traceability</p>
        </div>
        <div class="steps-grid">
            <div class="step-item animate-on-scroll">
                <div class="step-number">1</div>
                <h3>Register Farm</h3>
                <p>Register the livestock holding with GPS location, owner details, and receive a unique Holding Code.</p>
            </div>
            <div class="step-item animate-on-scroll">
                <div class="step-number">2</div>
                <h3>Tag Animals</h3>
                <p>Each animal is tagged with an electronic ID (E-ID) and visual ID (V-ID) and registered in the system.</p>
            </div>
            <div class="step-item animate-on-scroll">
                <div class="step-number">3</div>
                <h3>Record Events</h3>
                <p>Log health events, production data, vaccinations, and movements as they occur through the mobile app.</p>
            </div>
            <div class="step-item animate-on-scroll">
                <div class="step-number">4</div>
                <h3>Trace & Report</h3>
                <p>Access complete animal histories, generate reports, and ensure compliance with national standards.</p>
            </div>
        </div>
    </div>
</section>

{{-- ===== FAQ ===== --}}
<section class="faq section" id="faq">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Support</span>
            <h2>Frequently Asked Questions</h2>
            <p>Common questions about the U-LITS platform</p>
        </div>
        <div class="faq-list">
            <div class="faq-item">
                <div class="faq-question">
                    <span>What is U-LITS and who is it for?</span>
                    <div class="faq-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                    </div>
                </div>
                <div class="faq-answer">
                    <div class="faq-answer-inner">
                        U-LITS (Uganda Livestock Identification and Traceability System) is a national digital
                        platform for tracking livestock from birth to market. It is designed for farmers,
                        veterinary officers (DVO, SCVO), traders, butcheries, and government regulators
                        under MAAIF.
                    </div>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>How does the animal identification work?</span>
                    <div class="faq-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                    </div>
                </div>
                <div class="faq-answer">
                    <div class="faq-answer-inner">
                        Each animal is assigned a unique Electronic ID (E-ID) through an ear tag and a
                        corresponding Visual ID (V-ID). These IDs are linked to the animal's complete
                        profile including breed, health history, production records, and movement history
                        in the U-LITS database.
                    </div>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>Does U-LITS work offline?</span>
                    <div class="faq-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                    </div>
                </div>
                <div class="faq-answer">
                    <div class="faq-answer-inner">
                        Yes. The mobile application is built with offline-first technology. All data is
                        stored locally on the device and automatically synchronized with the central server
                        when an internet connection becomes available. This ensures uninterrupted field
                        operations even in areas with limited connectivity.
                    </div>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>What species does U-LITS support?</span>
                    <div class="faq-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                    </div>
                </div>
                <div class="faq-answer">
                    <div class="faq-answer-inner">
                        U-LITS supports multiple livestock species including cattle, goats, sheep,
                        pigs, and poultry. The system is flexible enough to accommodate different
                        breeds and production types within each species category.
                    </div>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>How does U-LITS help with disease control?</span>
                    <div class="faq-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                    </div>
                </div>
                <div class="faq-answer">
                    <div class="faq-answer-inner">
                        U-LITS records all animal movements, vaccination campaigns, and disease incidents.
                        In the event of a disease outbreak, authorities can quickly trace which animals
                        were in contact, where they moved, and which farms may be affected. This enables
                        rapid containment and reduces the spread of diseases like FMD (Foot and Mouth Disease).
                    </div>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>How can I get started with U-LITS?</span>
                    <div class="faq-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                    </div>
                </div>
                <div class="faq-answer">
                    <div class="faq-answer-inner">
                        Download the U-LITS mobile app from Google Play Store or Apple App Store.
                        Register your farm through your local District Veterinary Officer (DVO) or
                        Sub-County Veterinary Officer (SCVO). Once registered, you can begin tagging
                        animals and recording data immediately.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== PARTNERS ===== --}}
<section class="partners" id="partners">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Partners</span>
            <h2>Our Partners</h2>
        </div>
        <div class="partners-row">
            <a href="https://agriculture.go.ug" target="_blank" rel="noopener" class="partner-link">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Ministry of Agriculture (MAAIF)
            </a>
            <a href="https://gou.go.ug" target="_blank" rel="noopener" class="partner-link">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                Government of Uganda
            </a>
        </div>
    </div>
</section>

{{-- ===== CTA / DOWNLOAD ===== --}}
<section class="cta-section" id="download">
    <div class="container">
        <h2>Get Started with U-LITS Today</h2>
        <p>Download the mobile app and join Uganda's modern livestock traceability system.</p>
        <div class="cta-buttons">
            <a href="https://play.google.com/store/apps/details?id=com.ulits" target="_blank" rel="noopener" class="cta-store">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M3.609 1.814L13.792 12 3.61 22.186a.996.996 0 01-.61-.92V2.734a1 1 0 01.609-.92zm10.89 10.893l2.302 2.302-10.937 6.333 8.635-8.635zm3.199-3.199l2.807 1.626a1 1 0 010 1.732l-2.807 1.626L15.206 12l2.492-2.492zM5.864 2.658L16.8 8.99l-2.302 2.302-8.635-8.635z"/></svg>
                <div class="cta-store-text">
                    <small>Get it on</small>
                    <span>Google Play</span>
                </div>
            </a>
            <a href="https://apps.apple.com/app/ulits" target="_blank" rel="noopener" class="cta-store">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                <div class="cta-store-text">
                    <small>Download on the</small>
                    <span>App Store</span>
                </div>
            </a>
        </div>
    </div>
</section>

{{-- ===== CONTACT ===== --}}
<section class="contact section" id="contact">
    <div class="container">
        <div class="section-header">
            <span class="section-label">Get in Touch</span>
            <h2>Contact Us</h2>
            <p>Have questions about U-LITS? We are here to help.</p>
        </div>
        <div class="contact-grid">
            <div class="contact-info-list">
                <div class="contact-info-item animate-on-scroll">
                    <div class="contact-info-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="contact-info-text">
                        <h4>Office Address</h4>
                        <p>8J25+VG5, Barnabas Rd<br>Kampala, Uganda</p>
                    </div>
                </div>
                <div class="contact-info-item animate-on-scroll">
                    <div class="contact-info-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div class="contact-info-text">
                        <h4>Phone</h4>
                        <p><a href="tel:+256775679505">+256 775 679505</a></p>
                    </div>
                </div>
                <div class="contact-info-item animate-on-scroll">
                    <div class="contact-info-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="contact-info-text">
                        <h4>Email</h4>
                        <p><a href="mailto:info@u-lits.com">info@u-lits.com</a></p>
                    </div>
                </div>
                <div class="contact-info-item animate-on-scroll">
                    <div class="contact-info-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="contact-info-text">
                        <h4>Working Hours</h4>
                        <p>Monday - Friday: 8:00 AM - 5:00 PM<br>Saturday: 9:00 AM - 1:00 PM</p>
                    </div>
                </div>
            </div>

            <div class="contact-form">
                <form id="contactForm">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" placeholder="Your name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="you@example.com" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="+256 7XX XXX XXX">
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="5" placeholder="How can we help you?" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

@include('landing.partials.footer')

<script src="{{ asset('js/landing.js') }}"></script>
</body>
</html>

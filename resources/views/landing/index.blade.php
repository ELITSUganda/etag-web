<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>U-LITS – Uganda Livestock Information Tracking System</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

    <!-- Meta Tags -->
    <meta name="description" content="Uganda Livestock Information Tracking System - Complete livestock lifecycle management from farm to market.">
    <meta name="theme-color" content="#2d5016">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="U-LITS – Uganda Livestock Information Tracking System">
    <meta property="og:description" content="Complete livestock lifecycle management from farm to market.">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">
    <meta property="og:url" content="{{ url('/') }}">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>
    @include('landing.partials.header')

    <!-- Hero Section -->
    <section class="hero" id="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Uganda Livestock Information Tracking System</h1>
                <p class="hero-subtitle">Complete, transparent traceability from farm to market. Empower farmers. Protect consumers.</p>
                <div class="hero-cta">
                    <a href="#learn-more" class="btn btn-primary">Learn More</a>
                    <a href="#features" class="btn btn-secondary">Explore Features</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="{{ asset('images/hero-livestock.svg') }}" alt="Livestock Tracking">
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="learn-more">
        <div class="container">
            <div class="section-header">
                <h2>What is U-LITS?</h2>
                <p class="section-subtitle">A comprehensive livestock management ecosystem for Uganda</p>
            </div>

            <div class="about-grid">
                <div class="about-card">
                    <div class="icon-box">
                        <span class="icon">📍</span>
                    </div>
                    <h3>Complete Registration</h3>
                    <p>Register farms using GPS coordinates and holding codes. Maintain accurate records of all livestock locations across Uganda.</p>
                </div>
                <div class="about-card">
                    <div class="icon-box">
                        <span class="icon">🏷️</span>
                    </div>
                    <h3>Unique Identification</h3>
                    <p>Each animal receives a unique electronic ID (E-ID) enabling complete traceability throughout its lifecycle.</p>
                </div>
                <div class="about-card">
                    <div class="icon-box">
                        <span class="icon">📊</span>
                    </div>
                    <h3>Real-time Data</h3>
                    <p>Collect production, health, and movement data in real-time. Monitor milk production, vaccinations, treatments, and animal health.</p>
                </div>
                <div class="about-card">
                    <div class="icon-box">
                        <span class="icon">🔗</span>
                    </div>
                    <h3>Full Traceability</h3>
                    <p>Track animals from birth through production to slaughter. Complete transparency for export compliance and consumer confidence.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- The 4 Registers Section -->
    <section class="registers" id="registers">
        <div class="container">
            <div class="section-header">
                <h2>The Four Regulatory Registers</h2>
                <p class="section-subtitle">The foundation of Uganda's livestock information system</p>
            </div>

            <div class="registers-flow">
                <!-- Vertical Tree Visualization -->
                <div class="register-tree">
                    <div class="tree-level">
                        <div class="register-node">
                            <div class="register-card register-1">
                                <div class="register-number">1</div>
                                <h3>Livestock Holdings Register</h3>
                                <p>Foundation of the system</p>
                                <div class="register-details">
                                    <strong>Records:</strong> Farm locations, ownership, holding codes, GPS coordinates
                                </div>
                            </div>
                            <div class="tree-connector"></div>
                        </div>
                    </div>

                    <div class="tree-level">
                        <div class="register-node">
                            <div class="register-card register-2">
                                <div class="register-number">2</div>
                                <h3>Livestock Data Register</h3>
                                <p>Individual animal information</p>
                                <div class="register-details">
                                    <strong>Records:</strong> Animal IDs (E-ID), species, breed, age, health status, ownership
                                </div>
                            </div>
                            <div class="tree-connector"></div>
                        </div>
                    </div>

                    <div class="tree-level">
                        <div class="register-node">
                            <div class="register-card register-3">
                                <div class="register-number">3</div>
                                <h3>Livestock Events Register</h3>
                                <p>Activities affecting animals</p>
                                <div class="register-details">
                                    <strong>Records:</strong> Vaccinations, treatments, milk production, weight checks, births, deaths, disease incidents
                                </div>
                            </div>
                            <div class="tree-connector"></div>
                        </div>
                    </div>

                    <div class="tree-level">
                        <div class="register-node">
                            <div class="register-card register-4">
                                <div class="register-number">4</div>
                                <h3>Livestock Movement Register</h3>
                                <p>Enables disease surveillance</p>
                                <div class="register-details">
                                    <strong>Records:</strong> Animal movements between farms, market transactions, slaughter records
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Register Details -->
            <div class="registers-detail">
                <div class="detail-tabs">
                    <button class="detail-tab active" data-tab="register-1-detail">Livestock Holdings</button>
                    <button class="detail-tab" data-tab="register-2-detail">Livestock Data</button>
                    <button class="detail-tab" data-tab="register-3-detail">Livestock Events</button>
                    <button class="detail-tab" data-tab="register-4-detail">Livestock Movement</button>
                </div>

                <div id="register-1-detail" class="detail-content active">
                    <h4>Livestock Holdings Register</h4>
                    <p>The foundational register that establishes every livestock farm in the system. Contains:</p>
                    <ul>
                        <li>Farm location (GPS coordinates)</li>
                        <li>Farm name and unique holding code</li>
                        <li>Farm owner information</li>
                        <li>Administrative location (District, Sub-county)</li>
                        <li>Farm size and species managed</li>
                        <li>Contact information</li>
                    </ul>
                </div>

                <div id="register-2-detail" class="detail-content">
                    <h4>Livestock Data Register</h4>
                    <p>Contains detailed records for each individual animal within registered holdings:</p>
                    <ul>
                        <li>Electronic ID (E-ID) – unique identifier for each animal</li>
                        <li>Visual ID (V-ID) and physical description</li>
                        <li>Species and breed information</li>
                        <li>Date of birth and age</li>
                        <li>Current health status</li>
                        <li>Production capability and history</li>
                    </ul>
                </div>

                <div id="register-3-detail" class="detail-content">
                    <h4>Livestock Events Register</h4>
                    <p>Records all significant events affecting animals during their lifetime:</p>
                    <ul>
                        <li>Production events (milk production records)</li>
                        <li>Health events (vaccinations, disease incidents, treatments)</li>
                        <li>Reproduction events (births, breeding records)</li>
                        <li>Weight measurements and growth tracking</li>
                        <li>Presence verification (roll calls)</li>
                        <li>Any disease notifications or test results</li>
                    </ul>
                </div>

                <div id="register-4-detail" class="detail-content">
                    <h4>Livestock Movement Register</h4>
                    <p>Tracks all movements of animals between locations, essential for disease surveillance:</p>
                    <ul>
                        <li>Animal movements from one holding to another</li>
                        <li>Market transactions and sales</li>
                        <li>Slaughter records and butchery movements</li>
                        <li>Movement dates and destinations</li>
                        <li>Purpose of movement (sale, breeding, slaughter)</li>
                        <li>Enables disease outbreak tracking and control</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header">
                <h2>Platform Features</h2>
                <p class="section-subtitle">Comprehensive tools for modern livestock management</p>
            </div>

            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">🏠</div>
                    <h3>Farm Management</h3>
                    <p>Register farms with GPS coordinates, manage holding codes, track multiple species and herds efficiently.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🐄</div>
                    <h3>Animal Tracking</h3>
                    <p>Monitor individual animals throughout their lifecycle with unique electronic IDs and detailed health records.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">💉</div>
                    <h3>Health & Vaccination</h3>
                    <p>Record vaccinations, disease incidents, treatments, and health status with automated alerts for critical events.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🥛</div>
                    <h3>Production Tracking</h3>
                    <p>Log milk production, weight checks, and other production metrics for data-driven herd management.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📍</div>
                    <h3>Movement Tracking</h3>
                    <p>Record all animal movements between farms and to markets with complete documentation for compliance.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🔐</div>
                    <h3>Data Security</h3>
                    <p>End-to-end encryption, role-based access control, and audit trails ensure data integrity and privacy.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📱</div>
                    <h3>Mobile First</h3>
                    <p>Access the system from anywhere with offline-first technology and automatic synchronization.</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📊</div>
                    <h3>Analytics Dashboard</h3>
                    <p>Visualize herd statistics, production trends, health metrics, and identify improvement opportunities.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Use Cases Section -->
    <section class="use-cases">
        <div class="container">
            <div class="section-header">
                <h2>Who Benefits</h2>
                <p class="section-subtitle">U-LITS serves multiple stakeholders across the livestock value chain</p>
            </div>

            <div class="use-cases-grid">
                <div class="use-case-card">
                    <div class="use-case-icon">👨‍🌾</div>
                    <h3>Farmers</h3>
                    <p>Optimize production, track animal health, improve breeding decisions, and increase profitability with data-driven insights.</p>
                </div>
                <div class="use-case-card">
                    <div class="use-case-icon">⚕️</div>
                    <h3>Veterinary Officers</h3>
                    <p>Monitor disease prevalence, respond quickly to outbreaks, and provide targeted interventions for herd health.</p>
                </div>
                <div class="use-case-card">
                    <div class="use-case-icon">🏛️</div>
                    <h3>Regulatory Bodies</h3>
                    <p>Ensure compliance with livestock regulations, monitor disease surveillance, and support evidence-based policy decisions.</p>
                </div>
                <div class="use-case-card">
                    <div class="use-case-icon">🏪</div>
                    <h3>Traders & Buchers</h3>
                    <p>Access complete animal history, verify health status, ensure quality assurance, and meet export market requirements.</p>
                </div>
                <div class="use-case-card">
                    <div class="use-case-icon">🌍</div>
                    <h3>Export Markets</h3>
                    <p>Verify traceability, confirm health and vaccination status, and build consumer confidence in livestock products.</p>
                </div>
                <div class="use-case-card">
                    <div class="use-case-icon">👥</div>
                    <h3>Consumers</h3>
                    <p>Access complete product traceability, verify health standards, and make informed purchasing decisions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Impact Section -->
    <section class="impact">
        <div class="container">
            <div class="section-header">
                <h2>The Impact</h2>
                <p class="section-subtitle">Supporting Uganda's Vision 2040 for agricultural modernization</p>
            </div>

            <div class="impact-grid">
                <div class="impact-card">
                    <div class="impact-number">📈</div>
                    <h3>Increased Productivity</h3>
                    <p>Data-driven herd management improves milk production, faster animal growth, and better breeding outcomes.</p>
                </div>
                <div class="impact-card">
                    <div class="impact-number">🛡️</div>
                    <h3>Disease Control</h3>
                    <p>Early detection of disease incidents enables rapid response and prevents costly outbreak spread.</p>
                </div>
                <div class="impact-card">
                    <div class="impact-number">🌐</div>
                    <h3>Export Compliance</h3>
                    <p>Complete traceability and health documentation meets international market requirements for livestock exports.</p>
                </div>
                <div class="impact-card">
                    <div class="impact-number">💰</div>
                    <h3>Economic Growth</h3>
                    <p>Improved herd quality and market access increase farmer incomes and boost the national livestock sector.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Join the Revolution?</h2>
                <p>Become part of Uganda's modern livestock information system today.</p>
                <div class="cta-buttons">
                    <a href="#contact" class="btn btn-primary-large">Get Started</a>
                    <a href="#contact" class="btn btn-secondary-large">Contact Us</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="container">
            <div class="section-header">
                <h2>Get in Touch</h2>
                <p class="section-subtitle">Have questions? We're here to help</p>
            </div>

            <div class="contact-grid">
                <div class="contact-form">
                    <form id="contactForm" class="form">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>

                <div class="contact-info">
                    <div class="info-item">
                        <h4>📍 Address</h4>
                        <p>U-LITS Office<br>Uganda</p>
                    </div>
                    <div class="info-item">
                        <h4>📧 Email</h4>
                        <p>info@u-lits.com</p>
                    </div>
                    <div class="info-item">
                        <h4>📞 Phone</h4>
                        <p>+256 XXX XXX XXX</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('landing.partials.footer')

    <script src="{{ asset('js/landing.js') }}"></script>
</body>
</html>

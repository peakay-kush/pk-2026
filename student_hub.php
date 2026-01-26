<?php
$page_title = 'Student Hub';
$page_description = 'Empowering tech students with components, mentorship, and project prototyping support. The ultimate resource for engineering and computer science students.';
require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="hero-section">
    <div class="container">
        <h1><i class="fas fa-graduation-cap"></i> Student Hub</h1>
        <p class="lead">Your complete support system for academic engineering projects</p>
    </div>
</section>

<!-- Introduction Section -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto text-center">
                <div class="position-relative mb-4 d-inline-block">
                    <h2 class="display-5 fw-bold mb-3"
                        style="font-family: 'Outfit', sans-serif; color: var(--text-dark);">We're Here to Support Your
                        Success</h2>
                    <div class="mx-auto"
                        style="width: 120px; height: 4px; background: var(--primary-grad); border-radius: 2px;"></div>
                </div>
                <div style="font-family: 'Plus Jakarta Sans', sans-serif; color: #4a5568;">
                    <p style="font-size: 1.1rem; line-height: 1.8;">
                        At TechElectronics, we understand that student projects can be challenging. That's why we've
                        created the Student Hub - a comprehensive support system designed specifically for engineering
                        students. Whether you're working on your final year project, a class assignment, or a personal
                        learning project, we have the resources and expertise to help you succeed.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="section-padding bg-light">
    <div class="container">

        <!-- Project Consultation -->
        <div class="hub-section">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <div class="icon">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>
                <div class="col-md-7">
                    <h3>Project Consultation</h3>
                    <p>Get one-on-one guidance from experienced engineers. We help you:</p>
                    <ul>
                        <li>Validate and refine your project ideas</li>
                        <li>Create detailed project plans and timelines</li>
                        <li>Select appropriate components and technologies</li>
                        <li>Troubleshoot technical challenges</li>
                        <li>Prepare project documentation and presentations</li>
                    </ul>
                </div>
                <div class="col-md-3 text-center">
                    <a href="contact.php?service=Project Consultation" class="btn btn-accent btn-lg">Book
                        Consultation</a>
                </div>
            </div>
        </div>

        <!-- DIY Tutorials -->
        <div class="hub-section">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <div class="icon">
                        <i class="fas fa-book-reader"></i>
                    </div>
                </div>
                <div class="col-md-7">
                    <h3>DIY Tutorials & Code Samples</h3>
                    <p>Learn at your own pace with our extensive tutorial library:</p>
                    <ul>
                        <li>Arduino programming tutorials from beginner to advanced</li>
                        <li>IoT projects with ESP32, Raspberry Pi, and cloud integration</li>
                        <li>Sensor interfacing and data acquisition projects</li>
                        <li>Robotics and motor control applications</li>
                        <li>Downloadable code samples and circuit diagrams</li>
                    </ul>
                </div>
                <div class="col-md-3 text-center">
                    <a href="tutorials.php" class="btn btn-accent btn-lg">Browse Tutorials</a>
                </div>
            </div>
        </div>

        <!-- Simulation Support -->
        <div class="hub-section">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <div class="icon">
                        <i class="fas fa-microchip"></i>
                    </div>
                </div>
                <div class="col-md-7">
                    <h3>Simulation Support</h3>
                    <p>Master circuit simulation with expert assistance:</p>
                    <ul>
                        <li>Proteus simulation for microcontroller projects</li>
                        <li>Multisim circuit design and analysis</li>
                        <li>MATLAB/Simulink for control systems</li>
                        <li>PCB design with Eagle and KiCAD</li>
                        <li>Circuit troubleshooting and optimization</li>
                    </ul>
                </div>
                <div class="col-md-3 text-center">
                    <a href="contact.php?service=Simulation Support" class="btn btn-accent btn-lg">Get Help</a>
                </div>
            </div>
        </div>

        <!-- AutoCAD Assistance -->
        <div class="hub-section">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <div class="icon">
                        <i class="fas fa-drafting-compass"></i>
                    </div>
                </div>
                <div class="col-md-7">
                    <h3>AutoCAD Assistance</h3>
                    <p>Professional drawing services for your technical projects:</p>
                    <ul>
                        <li>Electrical layout and wiring diagrams</li>
                        <li>Mechanical part drawings and assemblies</li>
                        <li>Floor plans for building projects</li>
                        <li>3D modeling for visualization</li>
                        <li>Technical drawing standards and conventions</li>
                    </ul>
                </div>
                <div class="col-md-3 text-center">
                    <a href="contact.php?service=AutoCAD Assistance" class="btn btn-accent btn-lg">Request Drawing</a>
                </div>
            </div>
        </div>

        <!-- Custom Student Kits -->
        <div class="hub-section">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <div class="icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                </div>
                <div class="col-md-7">
                    <h3>Custom Student Kits</h3>
                    <p>Get all the components you need in one package:</p>
                    <ul>
                        <li>Pre-packaged kits for common project types</li>
                        <li>Custom kits tailored to your specific project</li>
                        <li>Verified components with datasheets included</li>
                        <li>Student-friendly pricing with discounts</li>
                        <li>Fast delivery to your campus or home</li>
                    </ul>
                </div>
                <div class="col-md-3 text-center">
                    <a href="contact.php?service=Custom Project Kit" class="btn btn-accent btn-lg">Order Kit</a>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Popular Student Projects -->
<section class="section-padding">
    <div class="container">
        <div class="section-title">
            <h2>Popular Student Projects We Support</h2>
            <p>Ideas and inspiration for your next project</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-home text-gradient" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3">Home Automation</h5>
                        <p class="card-text">Smart home systems using IoT, mobile apps, and voice control</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-robot text-gradient" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3">Robotics</h5>
                        <p class="card-text">Line followers, obstacle avoiders, robotic arms, and drones</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-heartbeat text-gradient" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3">Biomedical Devices</h5>
                        <p class="card-text">Health monitoring, patient tracking, and medical equipment</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-charging-station text-gradient" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3">Renewable Energy</h5>
                        <p class="card-text">Solar tracking, energy monitoring, and power management</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-shield-alt text-gradient" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3">Security Systems</h5>
                        <p class="card-text">RFID access control, surveillance, and alarm systems</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-car text-gradient" style="font-size: 3rem;"></i>
                        <h5 class="card-title mt-3">Automotive</h5>
                        <p class="card-text">Vehicle tracking, parking systems, and automotive diagnostics</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials from Students -->
<section class="section-padding testimonial-section-bg" style="padding: 4rem 0;">
    <div class="container">
        <div class="section-title" style="margin-bottom: 3rem;">
            <h2>Success Stories</h2>
            <p>Hear from students we've helped</p>
        </div>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card testimonial-card-premium"
                    style="border: 1px solid #e0e0e0; border-radius: 12px; padding: 1.5rem;">
                    <div class="mb-3">
                        <img src="assets/images/logo 2.png" alt="Logo"
                            style="width: 60px; height: auto; object-fit: contain;"
                            onerror="this.style.display='none';">
                    </div>
                    <h5 style="font-size: 1.1rem; font-weight: 700; color: #0B63CE; margin-bottom: 0.25rem;">John
                        Kipchoge</h5>
                    <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 1rem;">Electrical Engineer</p>
                    <div style="color: var(--accent-color); font-size: 1rem; margin-bottom: 1rem;">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text"
                        style="font-style: italic; font-size: 0.95rem; line-height: 1.6; margin: 0;">
                        "PK Automations helped us complete our automation project on time with excellent quality. Highly
                        recommended!"
                    </p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card testimonial-card-premium"
                    style="border: 1px solid #e0e0e0; border-radius: 12px; padding: 1.5rem;">
                    <div class="mb-3">
                        <img src="assets/images/logo 2.png" alt="Logo"
                            style="width: 60px; height: auto; object-fit: contain;"
                            onerror="this.style.display='none';">
                    </div>
                    <h5 style="font-size: 1.1rem; font-weight: 700; color: #0B63CE; margin-bottom: 0.25rem;">Grace
                        Murugi</h5>
                    <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 1rem;">Biomedical Student</p>
                    <div style="color: var(--accent-color); font-size: 1rem; margin-bottom: 1rem;">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text"
                        style="font-style: italic; font-size: 0.95rem; line-height: 1.6; margin: 0;">
                        "Their student project assistance was invaluable. They helped me understand complex concepts
                        easily."
                    </p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card testimonial-card-premium"
                    style="border: 1px solid #e0e0e0; border-radius: 12px; padding: 1.5rem;">
                    <div class="mb-3">
                        <img src="assets/images/logo 2.png" alt="Logo"
                            style="width: 60px; height: auto; object-fit: contain;"
                            onerror="this.style.display='none';">
                    </div>
                    <h5 style="font-size: 1.1rem; font-weight: 700; color: #0B63CE; margin-bottom: 0.25rem;">James
                        Okonkwo</h5>
                    <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 1rem;">Business Owner</p>
                    <div style="color: var(--accent-color); font-size: 1rem; margin-bottom: 1rem;">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text"
                        style="font-style: italic; font-size: 0.95rem; line-height: 1.6; margin: 0;">
                        "The web development team created an amazing website for our business. Professional and
                        responsive!"
                    </p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card testimonial-card-premium"
                    style="border: 1px solid #e0e0e0; border-radius: 12px; padding: 1.5rem;">
                    <div class="mb-3">
                        <img src="assets/images/logo 2.png" alt="Logo"
                            style="width: 60px; height: auto; object-fit: contain;"
                            onerror="this.style.display='none';">
                    </div>
                    <h5 style="font-size: 1.1rem; font-weight: 700; color: #0B63CE; margin-bottom: 0.25rem;">Patricia
                        Wanjiru</h5>
                    <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 1rem;">Industrial Manager</p>
                    <div style="color: var(--accent-color); font-size: 1rem; margin-bottom: 1rem;">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text"
                        style="font-style: italic; font-size: 0.95rem; line-height: 1.6; margin: 0;">
                        "Their IoT solutions increased our production efficiency by 40%. Great technical support
                        throughout."
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container text-center">
        <h2>Ready to Excel in Your Project?</h2>
        <p class="lead mb-4">Let's work together to bring your ideas to life</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="contact.php" class="btn btn-lg btn-primary" style="border-radius: 50px; padding: 0.8rem 2rem;">
                <i class="fas fa-envelope me-2"></i> Contact Us
            </a>
            <a href="shop.php" class="btn btn-lg btn-outline-light" style="border-radius: 50px; padding: 0.8rem 2rem;">
                <i class="fas fa-shopping-bag me-2"></i> Shop Components
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
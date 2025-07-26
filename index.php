<?php
// Personal Portfolio Website Configuration
$site_title = "Muwanguzi David -  A web developer";
$name = "Muwanguzi David";
$profession = "Web developer";
$world = "The World Of Technology";
$year = "Year 8 of experience";
$email = "davidmuwanguzi411@gmail.com";
$phone = "+256745091671, 0706702667";
$location = "Jinja, Uganda";

// Skills array
$skills = [
    "HTML/CSS" => 99,
    "JavaScript" => 99,
    "PHP" => 100,
    "Python" => 80,
    "MySQL" => 90,
    "Git/GitHub" => 70,
    "Bootstrap" => 85,
    "React" => 85
];

// Projects array
$projects = [
    [
        "title" => "Student Management System",
        "description" => "A web-based application for managing student records with PHP and MySQL",
        "tech" => ["PHP", "MySQL", "Bootstrap", "JavaScript"],
        "image" => "project1.jpg",
        "link" => "#"
    ],
    [
        "title" => "E-Commerce Website",
        "description" => "Responsive online store with shopping cart functionality",
        "tech" => ["HTML", "CSS", "JavaScript", "PHP"],
        "image" => "project2.jpg",
        "link" => "#"
    ],
    [
        "title" => "Weather App",
        "description" => "Real-time weather application using API integration",
        "tech" => ["JavaScript", "API", "CSS", "HTML"],
        "image" => "project3.jpg",
        "link" => "#"
    ]
];

// Get current page
$current_page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?></title>

    <!-- CSS Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #dd66ea;
            --secondary-color: #764ba2;
            --accent-color: #f093fb;
            --text-color: #333;
            --text-light: #666;
            --bg-color: #ffffff;
            --white: #ffffff;
            --shadow: 0 10px 30px rgba(0,0,0,0.1);
            --gradient: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            --accent-gradient: linear-gradient(135deg, var(--accent-color) 0%, var(--primary-color) 100%);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--bg-color);
            overflow-x: hidden;
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            z-index: 1000;
            padding: 1rem 0;
            transition: all 0.3s ease;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-menu li a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-menu li a:hover,
        .nav-menu li a.active {
            color: var(--primary-color);
        }

        .nav-menu li a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background: var(--gradient);
            transition: width 0.3s ease;
        }

        .nav-menu li a:hover::after,
        .nav-menu li a.active::after {
            width: 100%;
        }

        .mobile-menu {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }

        .mobile-menu span {
            width: 25px;
            height: 3px;
            background: var(--text-color);
            margin: 3px 0;
            transition: 0.3s;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="%23ffffff08" points="0,1000 1000,0 1000,1000"/></svg>');
            animation: float 6s ease-in-out infinite;
        }

        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 1rem;
            opacity: 0;
            animation: slideInLeft 1s ease 0.5s forwards;
        }

        .hero-content .subtitle {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            opacity: 0;
            animation: slideInLeft 1s ease 0.7s forwards;
        }

        .hero-content .bio {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
            line-height: 1.8;
            opacity: 0;
            animation: slideInLeft 1s ease 0.9s forwards;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            opacity: 0;
            animation: slideInLeft 1s ease 1.1s forwards;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-block;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: var(--white);
            color: var(--primary-color);
        }

        .btn-secondary {
            background: transparent;
            color: var(--white);
            border: 2px solid var(--white);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .hero-image {
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            animation: slideInRight 1s ease 0.5s forwards;
        }

        .profile-pic {
            width: 350px;
            height: 350px;
            border-radius: 50%;
            border: 5px solid rgba(255, 255, 255, 0.2);
            object-fit: cover;
            animation: float 3s ease-in-out infinite;
            background: linear-gradient(45deg, #f093fb, #f5576c);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 6rem;
            color: white;
        }

        /* Sections */
        .section {
            padding: 5rem 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* About Section */
        .about-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 4rem;
            align-items: center;
        }

        .about-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-light);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-top: 2rem;
        }

        .stat-item {
            text-align: center;
            padding: 2rem;
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-label {
            color: var(--text-light);
            margin-top: 0.5rem;
        }

        /* Skills Section */
        .skills-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .skill-item {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .skill-item:hover {
            transform: translateY(-5px);
        }

        .skill-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .skill-name {
            font-weight: 600;
            color: var(--text-color);
        }

        .skill-percentage {
            color: var(--primary-color);
            font-weight: 600;
        }

        .skill-bar {
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }

        .skill-progress {
            height: 100%;
            background: var(--gradient);
            border-radius: 4px;
            transition: width 2s ease;
            animation: progressLoad 2s ease forwards;
        }

        /* Projects Section */
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .project-card {
            background: var(--white);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .project-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .project-image {
            height: 200px;
            background: var(--gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .project-content {
            padding: 2rem;
        }

        .project-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .project-description {
            color: var(--text-light);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .project-tech {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .tech-tag {
            background: var(--accent-gradient);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .project-link {
            display: inline-block;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .project-link:hover {
            color: var(--secondary-color);
        }

        /* Contact Section */
        .contact-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: var(--white);
            border-radius: 15px;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .contact-item:hover {
            transform: translateX(10px);
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .contact-form {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        /* Footer */
        .footer {
            background: var(--text-color);
            color: white;
            padding: 2rem 0;
            text-align: center;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .social-link:hover {
            transform: translateY(-3px);
        }

        /* Animations */
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes progressLoad {
            from {
                width: 0;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-menu {
                position: fixed;
                left: -100%;
                top: 70px;
                flex-direction: column;
                background-color: var(--white);
                width: 100%;
                text-align: center;
                transition: 0.3s;
                box-shadow: var(--shadow);
                padding: 2rem 0;
            }

            .nav-menu.active {
                left: 0;
            }

            .mobile-menu {
                display: flex;
            }

            .hero-container {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 2rem;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .profile-pic {
                width: 250px;
                height: 250px;
            }

            .about-content,
            .contact-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .section-title {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 1rem;
            }

            .hero-content h1 {
                font-size: 2rem;
            }

            .projects-grid,
            .skills-grid {
                grid-template-columns: 1fr;
            }
        }

    </style>
</head>
<body>
<!-- Navigation -->
<nav class="navbar">
    <div class="nav-container">
        <div class="logo"><?php echo explode(' ', $name)[0]; ?></div>
        <ul class="nav-menu">
            <li><a href="#home" class="nav-link active">Home</a></li>
            <li><a href="#about" class="nav-link">About</a></li>
            <li><a href="#skills" class="nav-link">Skills</a></li>
            <li><a href="#projects" class="nav-link">Projects</a></li>
            <li><a href="#contact" class="nav-link">Contact</a></li>
        </ul>
        <div class="mobile-menu">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section id="home" class="hero">
    <div class="hero-container">
        <div class="hero-content">
            <h1>Hi, I'm <?php echo $name; ?></h1>
            <p class="subtitle"><?php echo $profession; ?></p>
            <p class="bio">
                I offer web solutions in <?php echo $world; ?>,
                passionate about creating innovative web solutions and learning cutting-edge technologies.
                I love turning complex problems into simple, beautiful designs.
            </p>
            <div class="cta-buttons">
                <a href="#projects" class="btn btn-primary">View My Work</a>
                <a href="#contact" class="btn btn-secondary">Get In Touch</a>
            </div>
        </div>
        <div class="hero-image">
            <div class="profile-pic">👨‍💻</div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="section">
    <div class="container">
        <h2 class="section-title">About Me</h2>
        <div class="about-content">
            <div class="about-text">
                <p>
                    I'm a dedicated web developer in my <?php echo $year; ?> in <?php echo $world; ?>.
                    My journey in technology started with curiosity about how things work behind the scenes of websites and applications.
                </p>
                <p>
                    I specialize in web development, with experience in both frontend and backend technologies.
                    I enjoy creating user-friendly interfaces and robust backend systems that solve real-world problems.
                </p>
                <p>
                    When I'm not coding, you can find me exploring new technologies, contributing to open-source projects,
                    or helping fellow students with their programming challenges.
                </p>
            </div>
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number">20+</div>
                    <div class="stat-label">Projects Completed</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">3+</div>
                    <div class="stat-label">Programming Languages</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">100+</div>
                    <div class="stat-label">Hours Coding</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">8</div>
                    <div class="stat-label">Year Experience</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Skills Section -->
<section id="skills" class="section" style="background: var(--bg-color);">
    <div class="container">
        <h2 class="section-title">My Skills</h2>
        <div class="skills-grid">
            <?php foreach($skills as $skill => $percentage): ?>
                <div class="skill-item">
                    <div class="skill-header">
                        <span class="skill-name"><?php echo $skill; ?></span>
                        <span class="skill-percentage"><?php echo $percentage; ?>%</span>
                    </div>
                    <div class="skill-bar">
                        <div class="skill-progress" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Projects Section -->
<section id="projects" class="section">
    <div class="container">
        <h2 class="section-title">My Projects</h2>
        <div class="projects-grid">
            <?php foreach($projects as $index => $project): ?>
                <div class="project-card">
                    <div class="project-image">🚀</div>
                    <div class="project-content">
                        <h3 class="project-title"><?php echo $project['title']; ?></h3>
                        <p class="project-description"><?php echo $project['description']; ?></p>
                        <div class="project-tech">
                            <?php foreach($project['tech'] as $tech): ?>
                                <span class="tech-tag"><?php echo $tech; ?></span>
                            <?php endforeach; ?>
                        </div>
                        <a href="<?php echo $project['link']; ?>" class="project-link">View Project →</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="section" style="background: var(--bg-color);">
    <div class="container">
        <h2 class="section-title">Get In Touch</h2>
        <div class="contact-content">
            <div class="contact-info">
                <div class="contact-item">
                    <div class="contact-icon">📧</div>
                    <div>
                        <h4>Email</h4>
                        <p><?php echo $email; ?></p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">📱</div>
                    <div>
                        <h4>Phone</h4>
                        <p><?php echo $phone; ?></p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">📍</div>
                    <div>
                        <h4>Location</h4>
                        <p><?php echo $location; ?></p>
                    </div>
                </div>
            </div>

            <form class="contact-form" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $name_form = isset($_POST['name']) ? $_POST['name'] : '';
                    $email_form = isset($_POST['email']) ? $_POST['email'] : '';
                    $message = isset($_POST['message']) ? $_POST['message'] : '';

                    if (!empty($name_form) && !empty($email_form) && !empty($message)) {
                        echo '<div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
                                    Thank you for your message! I\'ll get back to you soon.
                                  </div>';
                    }
                }
                ?>
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="social-links">
            <a href="#" class="social-link">📧</a>
            <a href="#" class="social-link">💼</a>
            <a href="#" class="social-link">🐙</a>
            <a href="#" class="social-link">🐦</a>
        </div>
        <p>&copy; <?php echo date('Y'); ?> <?php echo $name; ?>. All rights reserved.</p>
    </div>
</footer>

<!-- JavaScript -->
<script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenu = document.querySelector('.mobile-menu');
        const navMenu = document.querySelector('.nav-menu');

        mobileMenu.addEventListener('click', function() {
            navMenu.classList.toggle('active');

            // Animate hamburger menu
            const spans = mobileMenu.querySelectorAll('span');
            spans.forEach((span, index) => {
                span.style.transform = navMenu.classList.contains('active')
                    ? `rotate(${index === 0 ? 45 : index === 1 ? 0 : -45}deg) translate(${index === 1 ? '10px' : '0'}, ${index === 0 ? '6px' : index === 2 ? '-6px' : '0'})`
                    : 'none';
                span.style.opacity = index === 1 && navMenu.classList.contains('active') ? '0' : '1';
            });
        });

        // Smooth scrolling for navigation links
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetSection = document.querySelector(targetId);

                if (targetSection) {
                    targetSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });

                    // Update active nav link
                    navLinks.forEach(navLink => navLink.classList.remove('active'));
                    this.classList.add('active');

                    // Close mobile menu if open
                    navMenu.classList.remove('active');
                    const spans = mobileMenu.querySelectorAll('span');
                    spans.forEach(span => {
                        span.style.transform = 'none';
                        span.style.opacity = '1';
                    });
                }
            });
        });

        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            }
        });

        // Intersection Observer for section highlighting
        const sections = document.querySelectorAll('section[id]');
        const observerOptions = {
            root: null,
            rootMargin: '-50px 0px -50px 0px',
            threshold: 0.3
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.getAttribute('id');
                    const activeLink = document.querySelector(`.nav-link[href="#${id}"]`);
                    if (activeLink) {
                        navLinks.forEach(link => link.classList.remove('active'));
                        activeLink.classList.add('active');
                    }
                }
            });
        }, observerOptions);

        sections.forEach(section => observer.observe(section));

        // Animate skill bars on scroll
        const skillItems = document.querySelectorAll('.skill-item');
        const skillObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const progressBar = entry.target.querySelector('.skill-progress');
                    const percentage = progressBar.style.width;
                    progressBar.style.width = '0%';
                    setTimeout(() => {
                        progressBar.style.width = percentage;
                    }, 200);
                }
            });
        }, { threshold: 0.5 });

        skillItems.forEach(item => skillObserver.observe(item));

        // Animate elements on scroll
        const animateOnScroll = () => {
            const elements = document.querySelectorAll('.stat-item, .skill-item, .project-card, .contact-item');
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;

                if (elementTop < window.innerHeight - elementVisible) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        };

        // Set initial state for animated elements
        const animatedElements = document.querySelectorAll('.stat-item, .skill-item, .project-card, .contact-item');
        animatedElements.forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(30px)';
            element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        });

        window.addEventListener('scroll', animateOnScroll);
        animateOnScroll(); // Initial check

        // Form validation and enhancement
        const contactForm = document.querySelector('.contact-form');
        const formInputs = contactForm.querySelectorAll('input, textarea');

        formInputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.style.borderColor = 'var(--primary-color)';
                this.style.boxShadow = '0 0 0 3px rgba(102, 126, 234, 0.1)';
            });

            input.addEventListener('blur', function() {
                this.style.borderColor = '#e2e8f0';
                this.style.boxShadow = 'none';
            });
        });

        // Add typing animation to hero text
        const heroTitle = document.querySelector('.hero-content h1');
        const originalText = heroTitle.textContent;
        heroTitle.textContent = '';

        let i = 0;
        const typeWriter = () => {
            if (i < originalText.length) {
                heroTitle.textContent += originalText.charAt(i);
                i++;
                setTimeout(typeWriter, 100);
            }
        };

        setTimeout(typeWriter, 1500);

        // Add parallax effect to hero section
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.hero');
            const rate = scrolled * -0.5;
            hero.style.transform = `translateY(${rate}px)`;
        });

        // Add hover effects to project cards
        const projectCards = document.querySelectorAll('.project-card');
        projectCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-15px) scale(1.02)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Add loading animation
        window.addEventListener('load', function() {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });

        // Enhanced contact form submission
        contactForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;

            submitBtn.textContent = 'Sending...';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.7';

            // Re-enable button after form submission (simulated delay)
            setTimeout(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
            }, 2000);
        });

        // Add smooth reveal animation for sections
        const revealSections = document.querySelectorAll('.section');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        revealSections.forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(50px)';
            section.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
            revealObserver.observe(section);
        });

        // Add dynamic copyright year
        const currentYear = new Date().getFullYear();
        const copyrightElement = document.querySelector('.footer p');
        if (copyrightElement) {
            copyrightElement.innerHTML = copyrightElement.innerHTML.replace(/\d{4}/, currentYear);
        }

        // Add keyboard navigation support
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                const spans = mobileMenu.querySelectorAll('span');
                spans.forEach(span => {
                    span.style.transform = 'none';
                    span.style.opacity = '1';
                });
            }
        });

        // Performance optimization: Debounce scroll events
        let scrollTimeout;
        const debouncedScroll = () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                animateOnScroll();
            }, 10);
        };

        window.removeEventListener('scroll', animateOnScroll);
        window.addEventListener('scroll', debouncedScroll);
    });

    // Add resize event listener for responsive adjustments
    window.addEventListener('resize', function() {
        const navMenu = document.querySelector('.nav-menu');
        if (window.innerWidth > 768 && navMenu.classList.contains('active')) {
            navMenu.classList.remove('active');
            const mobileMenu = document.querySelector('.mobile-menu');
            const spans = mobileMenu.querySelectorAll('span');
            spans.forEach(span => {
                span.style.transform = 'none';
                span.style.opacity = '1';
            });
        }
    });

    // Add custom cursor effect for interactive elements
    const interactiveElements = document.querySelectorAll('a, button, .project-card, .skill-item, .contact-item');
    interactiveElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            document.body.style.cursor = 'pointer';
        });

        element.addEventListener('mouseleave', function() {
            document.body.style.cursor = 'default';
        });
    });
</script>
</body>
</html>
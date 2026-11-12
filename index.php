<!doctype html>
<html lang="en">
  <head>
    <?php require_once __DIR__ . '/feedback.php'; ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#f5f0e7">
    <meta name="description" content="Portfolio of Dencil Montales featuring a student enrollment system.">
    <title>
    Dencil Montales — Project Portfolio
    </title>
    <link rel="stylesheet" href="styles.css">
  </head>
  <body>
    <header class="wrap header">
      <a class="signature" href="#top">
      <b>
      DM
      </b>
      Dencil Montales
      </a>
      <nav aria-label="Main navigation">
        <a href="#project">
        Project
        </a>
        <a href="#feedback-enrollment">
        Feedback
        </a>
      </nav>
    </header>
    <main id="top">
      <section class="wrap hero">
        <div class="kicker">
          A project portfolio
        </div>
        <h1>
        Making the enrollment process
        <i>
        easier to follow.
        </i>
        </h1>
        <p>
        I’m Dencil Montales. This portfolio focuses on one student service project: an online enrollment system that brings applications and status updates into one place.
        </p>
        <a class="hero-link" href="#project">
        Explore the project
        <span aria-hidden="true">
        ↓
        </span>
        </a>
      </section>
      <section class="wrap project-section" id="project">
        <div class="section-top">
          <div>
            <div class="label">
              Selected work · 01
            </div>
            <h2>
            Student services
            </h2>
          </div>
          <p>
          A focused case study in collecting student details and keeping application decisions visible.
          </p>
        </div>
        <article class="project">
          <div class="visual" aria-hidden="true">
            <div class="form-preview">
              <small>
              BlueWave Academy
              </small>
              <h3>
              Enrollment form
              </h3>
              <div class="field-line">
                Student name
                <span>
                ________________
                </span>
              </div>
              <div class="field-line">
                Program
                <span>
                Choose one
                </span>
              </div>
              <div class="field-line">
                Contact details
                <span>
                ________________
                </span>
              </div>
              <div class="preview-button">
                Submit application
              </div>
            </div>
          </div>
          <div class="project-copy">
            <div class="label">
              PHP · MySQL · Web application
            </div>
            <h3>
            Student Enrollment Portal
            </h3>
            <div class="project-meta">
              <span class="tag">
              PHP
              </span>
              <span class="tag">
              MySQL
              </span>
              <span class="tag">
              HTML
              </span>
              <span class="tag">
              CSS
              </span>
            </div>
            <p>
            The portal gives students a place to submit enrollment information and follow the status of an application. Staff can review submissions and record an accepted or declined decision.
            </p>
            <ul class="feature-list">
              <li>
                Student application and profile details
              </li>
              <li>
                Status view for pending and decided applications
              </li>
              <li>
                Staff review and application decisions
              </li>
            </ul>
            <?php render_feedback('enrollment', 'Dencil'); ?>
          </div>
        </article>
      </section>
    </main>
    <footer class="footer">
      <div class="wrap footer-inner">
        <span>
        Dencil Montales · Project Portfolio
        </span>
        <span>
        Student Enrollment Portal
        </span>
      </div>
    </footer>
    <script src="script.js" defer>
    </script>
  </body>
</html>


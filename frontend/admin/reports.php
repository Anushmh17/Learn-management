<?php
// =====================================================
// LEARN Management - Admin: Reports & Analytics
// =====================================================
define('PAGE_TITLE', 'Reports & Analytics');
require_once dirname(__DIR__, 2) . '/backend/config.php';
require_once dirname(__DIR__, 2) . '/backend/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

requireRole(ROLE_ADMIN);

// Monthly Revenue (last 6 months)
$monthlyRevenue = $pdo->query("
    SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount_paid) as total
    FROM student_payments
    WHERE status = 'paid'
    GROUP BY month
    ORDER BY month DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Top courses by enrollment
$courseEnrollments = $pdo->query("
    SELECT c.course_name as title, COUNT(e.id) as cnt
    FROM courses c
    LEFT JOIN enrollments e ON e.course_id = c.id
    GROUP BY c.id
    ORDER BY cnt DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Quick counts
$totalStudents  = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='active'")->fetchColumn();
$totalLecturers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='lecturer' AND status='active'")->fetchColumn();
$totalCourses   = $pdo->query("SELECT COUNT(*) FROM courses WHERE status='active'")->fetchColumn();
$totalRevenue   = $pdo->query("SELECT COALESCE(SUM(amount_paid),0) FROM student_payments WHERE status='paid'")->fetchColumn();
$totalEnrollments = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
$totalNotices   = $pdo->query("SELECT COUNT(*) FROM notices")->fetchColumn();

// Max monthly revenue for bar chart scaling
$maxRevenue = !empty($monthlyRevenue) ? max($monthlyRevenue) : 1;
// Max enrollment for progress bar scaling
$maxEnrollment = !empty($courseEnrollments) ? max($courseEnrollments) : 1;

require_once dirname(__DIR__, 2) . '/includes/header.php';
require_once dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<div id="page-content">

  <!-- Page Header -->
  <div class="page-header">
    <div class="page-header-left">
      <h1>Reports &amp; Analytics</h1>
      <div class="breadcrumb-custom"><i class="fas fa-home"></i> Admin &rsaquo; <span>Reports</span></div>
    </div>
    <button onclick="window.print()" class="btn-lms btn-outline"><i class="fas fa-print"></i> Print Report</button>
  </div>

  <div id="report-printable">

    <!-- ===================== DESKTOP: original layout ===================== -->
    <div class="reports-desktop">
      <div class="row g-4">
        <!-- Highlight Cards -->
        <div class="col-md-3">
          <div class="card-lms p-4 text-center">
            <h3 style="color:var(--primary);margin:0;font-size:28px;font-weight:700;"><?= $totalStudents ?></h3>
            <div class="text-muted" style="font-size:13px;margin-top:5px;font-weight:600;text-transform:uppercase;">Total Students</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card-lms p-4 text-center">
            <h3 style="color:var(--accent);margin:0;font-size:28px;font-weight:700;"><?= $totalLecturers ?></h3>
            <div class="text-muted" style="font-size:13px;margin-top:5px;font-weight:600;text-transform:uppercase;">Total Lecturers</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card-lms p-4 text-center">
            <h3 style="color:var(--info);margin:0;font-size:28px;font-weight:700;"><?= $totalCourses ?></h3>
            <div class="text-muted" style="font-size:13px;margin-top:5px;font-weight:600;text-transform:uppercase;">Total Courses</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card-lms p-4 text-center">
            <h3 style="color:var(--warning);margin:0;font-size:28px;font-weight:700;">Rs.<?= number_format($totalRevenue,0) ?></h3>
            <div class="text-muted" style="font-size:13px;margin-top:5px;font-weight:600;text-transform:uppercase;">Total Revenue</div>
          </div>
        </div>

        <!-- Monthly Revenue Table -->
        <div class="col-md-6">
          <div class="card-lms h-100">
            <div class="card-lms-header"><div class="card-lms-title"><i class="fas fa-chart-bar"></i> Monthly Revenue (Last 6 Months)</div></div>
            <div class="card-lms-body p-0">
              <table class="table-lms">
                <thead><tr><th>Month</th><th class="text-end">Revenue</th></tr></thead>
                <tbody>
                  <?php if(empty($monthlyRevenue)): ?>
                    <tr><td colspan="2" class="text-center text-muted">No revenue data available.</td></tr>
                  <?php else: foreach($monthlyRevenue as $month => $total): ?>
                    <tr>
                      <td><div class="fw-600"><?= date('F Y', strtotime($month.'-01')) ?></div></td>
                      <td class="text-end fw-700" style="color:var(--accent);">Rs.<?= number_format($total,2) ?></td>
                    </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Enrollments per Course Table -->
        <div class="col-md-6">
          <div class="card-lms h-100">
            <div class="card-lms-header"><div class="card-lms-title"><i class="fas fa-chart-pie"></i> Top 5 Courses by Enrollment</div></div>
            <div class="card-lms-body p-0">
              <table class="table-lms">
                <thead><tr><th>Course Name</th><th class="text-center">Students Enrolled</th></tr></thead>
                <tbody>
                  <?php if(empty($courseEnrollments)): ?>
                    <tr><td colspan="2" class="text-center text-muted">No enrollment data available.</td></tr>
                  <?php else: foreach($courseEnrollments as $course => $cnt): ?>
                    <tr>
                      <td><div class="fw-600"><?= htmlspecialchars($course) ?></div></td>
                      <td class="text-center"><span class="badge-lms primary"><?= $cnt ?> Students</span></td>
                    </tr>
                  <?php endforeach; endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div><!-- /row -->
    </div><!-- /reports-desktop -->


    <!-- ===================== MOBILE: redesigned layout ===================== -->
    <div class="reports-mobile">

      <!-- KPI strip -->
      <div class="rpt-kpi-strip">
        <div class="rpt-kpi">
          <div class="rpt-kpi-icon" style="background:linear-gradient(135deg,#5B4EFA,#7c6fff);">
            <i class="fas fa-user-graduate"></i>
          </div>
          <div class="rpt-kpi-val"><?= $totalStudents ?></div>
          <div class="rpt-kpi-lbl">Students</div>
        </div>
        <div class="rpt-kpi">
          <div class="rpt-kpi-icon" style="background:linear-gradient(135deg,#00C9A7,#00a386);">
            <i class="fas fa-chalkboard-user"></i>
          </div>
          <div class="rpt-kpi-val"><?= $totalLecturers ?></div>
          <div class="rpt-kpi-lbl">Lecturers</div>
        </div>
        <div class="rpt-kpi">
          <div class="rpt-kpi-icon" style="background:linear-gradient(135deg,#4CC9F0,#1da1db);">
            <i class="fas fa-book-open"></i>
          </div>
          <div class="rpt-kpi-val"><?= $totalCourses ?></div>
          <div class="rpt-kpi-lbl">Courses</div>
        </div>
        <div class="rpt-kpi">
          <div class="rpt-kpi-icon" style="background:linear-gradient(135deg,#FF9F43,#e08800);">
            <i class="fas fa-list-check"></i>
          </div>
          <div class="rpt-kpi-val"><?= $totalEnrollments ?></div>
          <div class="rpt-kpi-lbl">Enrolled</div>
        </div>
      </div>

      <!-- Revenue Hero Card -->
      <div class="rpt-hero-card">
        <div class="rpt-hero-bg"></div>
        <div class="rpt-hero-label"><i class="fas fa-coins"></i> Total Revenue Collected</div>
        <div class="rpt-hero-value">Rs.<?= number_format($totalRevenue, 0) ?></div>
        <div class="rpt-hero-sub">
          <span><i class="fas fa-arrow-trend-up"></i> From <?= count($monthlyRevenue) ?> active months</span>
          <span><?= $totalEnrollments ?> total enrollments</span>
        </div>
      </div>

      <!-- Monthly Revenue Bar Chart -->
      <div class="rpt-card">
        <div class="rpt-card-header">
          <div class="rpt-card-title"><i class="fas fa-chart-bar"></i> Monthly Revenue</div>
          <span class="rpt-card-badge">Last 6 Months</span>
        </div>
        <div class="rpt-card-body">
          <?php if(empty($monthlyRevenue)): ?>
            <div class="rpt-empty"><i class="fas fa-chart-bar"></i><p>No revenue data yet.</p></div>
          <?php else: ?>
            <div class="rpt-bar-chart">
              <?php foreach(array_reverse($monthlyRevenue, true) as $month => $total):
                $pct = $maxRevenue > 0 ? round(($total / $maxRevenue) * 100) : 0;
                $label = date('M y', strtotime($month.'-01'));
              ?>
              <div class="rpt-bar-row">
                <div class="rpt-bar-label"><?= $label ?></div>
                <div class="rpt-bar-track">
                  <div class="rpt-bar-fill" style="width:<?= $pct ?>%;" data-pct="<?= $pct ?>"></div>
                </div>
                <div class="rpt-bar-amount">Rs.<?= number_format($total,0) ?></div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Top Courses by Enrollment -->
      <div class="rpt-card">
        <div class="rpt-card-header">
          <div class="rpt-card-title"><i class="fas fa-ranking-star"></i> Top Courses</div>
          <span class="rpt-card-badge">By Enrollment</span>
        </div>
        <div class="rpt-card-body">
          <?php if(empty($courseEnrollments)): ?>
            <div class="rpt-empty"><i class="fas fa-book"></i><p>No enrollment data yet.</p></div>
          <?php else:
            $colors = ['#5B4EFA','#00C9A7','#4CC9F0','#FF9F43','#FF6B6B'];
            $i = 0;
            foreach($courseEnrollments as $course => $cnt):
              $pct = $maxEnrollment > 0 ? round(($cnt / $maxEnrollment) * 100) : 0;
              $color = $colors[$i % count($colors)];
          ?>
          <div class="rpt-course-row">
            <div class="rpt-course-top">
              <div class="rpt-course-rank" style="background:<?= $color ?>1a;color:<?= $color ?>;"><?= $i+1 ?></div>
              <div class="rpt-course-name"><?= htmlspecialchars($course) ?></div>
              <div class="rpt-course-count" style="color:<?= $color ?>;"><?= $cnt ?> <span>students</span></div>
            </div>
            <div class="rpt-progress-track">
              <div class="rpt-progress-fill" style="width:<?= $pct ?>%;background:<?= $color ?>;"></div>
            </div>
          </div>
          <?php $i++; endforeach; endif; ?>
        </div>
      </div>

      <!-- Quick Stats Pills -->
      <div class="rpt-card">
        <div class="rpt-card-header">
          <div class="rpt-card-title"><i class="fas fa-bolt"></i> Quick Insights</div>
        </div>
        <div class="rpt-card-body" style="padding-top:10px;">
          <div class="rpt-insight-grid">
            <div class="rpt-insight">
              <i class="fas fa-bell" style="color:#FF9F43;"></i>
              <div class="rpt-insight-val"><?= $totalNotices ?></div>
              <div class="rpt-insight-lbl">Notices Posted</div>
            </div>
            <div class="rpt-insight">
              <i class="fas fa-dollar-sign" style="color:#00C9A7;"></i>
              <div class="rpt-insight-val"><?= !empty($monthlyRevenue) ? 'Rs.'.number_format(array_values($monthlyRevenue)[0],0) : '—' ?></div>
              <div class="rpt-insight-lbl">This Month</div>
            </div>
            <div class="rpt-insight">
              <i class="fas fa-trophy" style="color:#5B4EFA;"></i>
              <div class="rpt-insight-val"><?= !empty($courseEnrollments) ? array_key_first($courseEnrollments) : '—' ?></div>
              <div class="rpt-insight-lbl">Top Course</div>
            </div>
            <div class="rpt-insight">
              <i class="fas fa-chart-line" style="color:#FF6B6B;"></i>
              <div class="rpt-insight-val"><?= $totalEnrollments > 0 && $totalStudents > 0 ? round($totalEnrollments/$totalStudents,1) : 0 ?>x</div>
              <div class="rpt-insight-lbl">Avg Courses/Student</div>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /reports-mobile -->

  </div><!-- /report-printable -->
</div>

<style>
/* ============================================================
   REPORTS PAGE — MOBILE-ONLY REDESIGN (≤768px)
   Desktop is completely untouched above this block
   ============================================================ */

/* Hide mobile layout on desktop, hide desktop layout on mobile */
.reports-mobile { display: none; }
.reports-desktop { display: block; }

@media (max-width: 768px) {
  .reports-desktop { display: none !important; }
  .reports-mobile  { display: block; }

  /* ── KPI Strip ── */
  .rpt-kpi-strip {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 16px;
  }
  .rpt-kpi {
    background: #fff;
    border-radius: 14px;
    padding: 14px 8px 12px;
    text-align: center;
    border: 1.5px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    transition: transform 0.2s;
  }
  .rpt-kpi:active { transform: scale(0.95); }
  .rpt-kpi-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #fff;
    margin-bottom: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.12);
  }
  .rpt-kpi-val {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-main);
    line-height: 1;
  }
  .rpt-kpi-lbl {
    font-size: 9px;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 4px;
  }

  /* ── Revenue Hero Card ── */
  .rpt-hero-card {
    position: relative;
    background: linear-gradient(135deg, #5B4EFA 0%, #4338e0 50%, #00C9A7 100%);
    border-radius: 20px;
    padding: 24px 20px 20px;
    margin-bottom: 16px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(91,78,250,0.35);
  }
  .rpt-hero-bg {
    position: absolute;
    top: -40px; right: -40px;
    width: 140px; height: 140px;
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
  }
  .rpt-hero-bg::after {
    content: '';
    position: absolute;
    top: 30px; left: 30px;
    width: 80px; height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,0.06);
  }
  .rpt-hero-label {
    font-size: 11px;
    font-weight: 600;
    color: rgba(255,255,255,0.75);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .rpt-hero-value {
    font-size: 34px;
    font-weight: 800;
    color: #fff;
    font-family: 'Poppins', sans-serif;
    line-height: 1.1;
    margin-bottom: 12px;
  }
  .rpt-hero-sub {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: rgba(255,255,255,0.65);
    font-weight: 500;
  }
  .rpt-hero-sub span { display: flex; align-items: center; gap: 4px; }

  /* ── Generic Card ── */
  .rpt-card {
    background: #fff;
    border-radius: 18px;
    border: 1.5px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    margin-bottom: 16px;
    overflow: hidden;
  }
  .rpt-card-header {
    padding: 16px 18px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .rpt-card-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-main);
    font-family: 'Poppins', sans-serif;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .rpt-card-title i { color: var(--primary); }
  .rpt-card-badge {
    font-size: 10px;
    font-weight: 700;
    background: var(--primary-light);
    color: var(--primary);
    padding: 3px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
  }
  .rpt-card-body {
    padding: 16px 18px 18px;
  }

  /* ── Revenue Bar Chart ── */
  .rpt-bar-chart { display: flex; flex-direction: column; gap: 14px; }
  .rpt-bar-row { display: flex; align-items: center; gap: 10px; }
  .rpt-bar-label {
    width: 46px;
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    flex-shrink: 0;
    text-align: right;
  }
  .rpt-bar-track {
    flex: 1;
    height: 10px;
    background: var(--border-color);
    border-radius: 99px;
    overflow: hidden;
  }
  .rpt-bar-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, var(--primary), var(--accent));
    width: 0;
    transition: width 0.9s cubic-bezier(0.4,0,0.2,1);
  }
  .rpt-bar-amount {
    font-size: 11px;
    font-weight: 700;
    color: var(--accent);
    white-space: nowrap;
    width: 70px;
    text-align: right;
    flex-shrink: 0;
  }

  /* ── Course Progress Rows ── */
  .rpt-course-row { margin-bottom: 18px; }
  .rpt-course-row:last-child { margin-bottom: 0; }
  .rpt-course-top {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 7px;
  }
  .rpt-course-rank {
    width: 26px; height: 26px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px;
    font-weight: 800;
    flex-shrink: 0;
  }
  .rpt-course-name {
    flex: 1;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-main);
    line-height: 1.3;
  }
  .rpt-course-count {
    font-size: 13px;
    font-weight: 800;
    white-space: nowrap;
    flex-shrink: 0;
  }
  .rpt-course-count span {
    font-size: 10px;
    font-weight: 500;
    color: var(--text-muted);
  }
  .rpt-progress-track {
    height: 6px;
    background: var(--border-color);
    border-radius: 99px;
    overflow: hidden;
  }
  .rpt-progress-fill {
    height: 100%;
    border-radius: 99px;
    width: 0;
    transition: width 1s cubic-bezier(0.4,0,0.2,1) 0.2s;
    opacity: 0.85;
  }

  /* ── Quick Insights Grid ── */
  .rpt-insight-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
  .rpt-insight {
    background: var(--bg-page);
    border-radius: 14px;
    padding: 16px 14px;
    text-align: center;
    border: 1.5px solid var(--border-color);
  }
  .rpt-insight i { font-size: 22px; margin-bottom: 8px; display: block; }
  .rpt-insight-val {
    font-size: 15px;
    font-weight: 800;
    color: var(--text-main);
    word-break: break-word;
    line-height: 1.2;
    margin-bottom: 4px;
  }
  .rpt-insight-lbl {
    font-size: 10px;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
  }

  /* ── Empty State ── */
  .rpt-empty {
    text-align: center;
    padding: 30px 20px;
    color: var(--text-muted);
  }
  .rpt-empty i { font-size: 36px; margin-bottom: 10px; opacity: 0.3; display: block; }
  .rpt-empty p { font-size: 13px; margin: 0; }
}

/* ── Print Styles ── */
@media print {
  #sidebar, #top-navbar, .btn-lms, .breadcrumb-custom { display: none !important; }
  #main-content { margin-left: 0 !important; }
  body { background: white !important; }
  .card-lms { border: none !important; box-shadow: none !important; }
  .reports-mobile { display: none !important; }
  .reports-desktop { display: block !important; }
}
</style>

<script>
// Animate bars after page load
document.addEventListener('DOMContentLoaded', function() {
  // Trigger bar chart animation
  setTimeout(function() {
    document.querySelectorAll('.rpt-bar-fill, .rpt-progress-fill').forEach(function(el) {
      var target = el.style.width;
      el.style.width = '0';
      requestAnimationFrame(function() {
        requestAnimationFrame(function() {
          el.style.width = target;
        });
      });
    });
  }, 200);
});
</script>

<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>

<?php
/**
 * LEARN Management - Admin Dashboard
 * Enhanced Soft UI / Glassmorphism with Extended Content
 */
define('PAGE_TITLE', 'Dashboard');
require_once dirname(__DIR__, 2) . '/backend/config.php';
require_once dirname(__DIR__, 2) . '/backend/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

requireRole(ROLE_ADMIN);

// =====================================================
// BACKEND DATA (Expanded)
// =====================================================
$total_students     = 1450;
$active_students    = 980;
$pending_payments   = 42;
$monthly_revenue    = 580000;

$recent_students = [
    ['name' => 'Alice Cooper', 'batch' => 'WD-02', 'status' => 'Completed', 'pay' => 'Paid', 'docs' => 'Complete'],
    ['name' => 'Bob Marley', 'batch' => 'DS-01', 'status' => 'Pending', 'pay' => 'Overdue', 'docs' => 'Incomplete'],
    ['name' => 'Charlie Day', 'batch' => 'WD-02', 'status' => 'Pending', 'pay' => 'Paid', 'docs' => 'Pending'],
];

$upcoming_schedule = [
    ['time' => '09:00 AM', 'title' => 'Web Development 101', 'lecturer' => 'Dr. Smith', 'room' => 'Lab 1'],
    ['time' => '11:30 AM', 'title' => 'Data Science Seminar', 'lecturer' => 'Prof. Xavier', 'room' => 'Hall A'],
    ['time' => '02:00 PM', 'title' => 'UX Design Workshop', 'lecturer' => 'Jane Doe', 'room' => 'Online'],
];

$top_courses = [
    ['name' => 'Fullstack Web Dev', 'students' => 450, 'growth' => '+12%'],
    ['name' => 'Python for Data Science', 'students' => 380, 'growth' => '+8%'],
    ['name' => 'UI/UX Advanced', 'students' => 210, 'growth' => '+15%'],
];

$system_health = [
    ['label' => 'Server Load', 'value' => '24%', 'status' => 'Healthy'],
    ['label' => 'DB Latency', 'value' => '12ms', 'status' => 'Optimal'],
];

require_once dirname(__DIR__, 2) . '/includes/header.php';
require_once dirname(__DIR__, 2) . '/includes/sidebar.php';
?>

<style>
  :root {
    --glass-bg: rgba(255, 255, 255, 0.7);
    --glass-border: rgba(255, 255, 255, 0.3);
    --primary-glow: rgba(37, 117, 252, 0.4);
    --success-glow: rgba(56, 239, 125, 0.4);
    --danger-glow: rgba(239, 68, 68, 0.4);
  }

  body { 
    background: #f0f4f9 !important;
    overflow-x: hidden;
  }

  /* --- Decorative Blobs --- */
  .blob {
    position: fixed;
    width: 300px; height: 300px;
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    filter: blur(80px);
    opacity: 0.15;
    z-index: -1;
    border-radius: 50%;
  }
  .blob-1 { top: -100px; right: -100px; background: #2575fc; }
  .blob-2 { bottom: -100px; left: -100px; background: #38ef7d; }

  .dashboard-wrapper { padding: 40px; position: relative; }

  .glass-card {
    background: var(--glass-bg);
    backdrop-filter: blur(15px);
    border: 1px solid var(--glass-border);
    border-radius: 24px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    padding: 28px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    height: 100%;
  }
  .glass-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
  }

  /* --- Summary Cards --- */
  .summary-card {
    color: #fff;
    border: none;
    position: relative;
    overflow: hidden;
  }
  .summary-card::before {
    content: '';
    position: absolute;
    top: -50%; left: -50%;
    width: 200%; height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    opacity: 0; transition: 0.5s;
  }
  .summary-card:hover::before { opacity: 1; }

  .summary-card.blue { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); box-shadow: 0 15px 35px var(--primary-glow); }
  .summary-card.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); box-shadow: 0 15px 35px var(--success-glow); }
  .summary-card.orange { background: linear-gradient(135deg, #ff9966 0%, #ff5e62 100%); box-shadow: 0 15px 35px rgba(255, 94, 98, 0.4); }
  .summary-card.purple { background: linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%); box-shadow: 0 15px 35px rgba(74, 0, 224, 0.4); }

  .summary-icon {
    width: 54px; height: 54px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; margin-bottom: 20px;
  }

  /* --- Schedule List --- */
  .schedule-item {
    display: flex; gap: 20px;
    padding: 15px;
    border-radius: 16px;
    background: rgba(255,255,255,0.4);
    margin-bottom: 15px;
    transition: 0.3s;
    border: 1px solid transparent;
  }
  .schedule-item:hover { background: #fff; border-color: #e2e8f0; transform: translateX(5px); }
  .schedule-time { min-width: 70px; font-weight: 800; color: var(--primary); font-size: 13px; }
  .schedule-dot { width: 10px; height: 10px; border-radius: 50%; background: #2575fc; margin-top: 5px; }

  /* --- Table --- */
  .table-modern { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
  .table-modern tbody tr { background: rgba(255,255,255,0.5); transition: 0.3s; }
  .table-modern tbody tr:hover { background: #fff; transform: scale(1.01); box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
  .table-modern td { padding: 18px 20px; border: none; }
  .table-modern td:first-child { border-radius: 16px 0 0 16px; }
  .table-modern td:last-child { border-radius: 0 16px 16px 0; }

  /* --- Badges --- */
  .badge-glass {
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .bg-success-glass { background: rgba(56, 239, 125, 0.15); color: #065f46; }
  .bg-warning-glass { background: rgba(245, 158, 11, 0.15); color: #92400e; }

  /* --- Quick Action Buttons --- */
  .btn-glass {
    padding: 14px 28px;
    border-radius: 50px;
    font-weight: 800;
    color: #fff;
    border: none;
    display: flex; align-items: center; gap: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
  }
  .btn-glass:hover { transform: translateY(-3px) scale(1.05); box-shadow: 0 15px 30px rgba(0,0,0,0.2); }

  .section-title { font-size: 18px; font-weight: 800; color: #1e293b; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
  .mb-60 { margin-bottom: 60px !important; }
</style>

<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="dashboard-wrapper">

  <!-- SECTION: QUICK ACTIONS (TOP) -->
  <div class="d-flex flex-wrap gap-3 mb-60">
    <a href="<?= BASE_URL ?>/admin/students/add.php" class="btn-glass" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
      <i class="fas fa-plus-circle"></i> Add New Student
    </a>
    <a href="<?= BASE_URL ?>/admin/payments/add.php" class="btn-glass" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
      <i class="fas fa-hand-holding-usd"></i> Record Payment
    </a>
    <a href="<?= BASE_URL ?>/admin/courses/add.php" class="btn-glass" style="background: linear-gradient(135deg, #ff9966 0%, #ff5e62 100%);">
      <i class="fas fa-graduation-cap"></i> Create Course
    </a>
  </div>

  <!-- SECTION: SUMMARY CARDS -->
  <div class="row g-4 mb-60">
    <div class="col-xl-3 col-md-6">
      <div class="glass-card summary-card blue">
        <div class="summary-icon"><i class="fas fa-users-viewfinder"></i></div>
        <div class="h1 fw-900 mb-1"><?= number_format($total_students) ?></div>
        <div class="small fw-700 opacity-80 uppercase tracking-wider">Total Students</div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="glass-card summary-card green">
        <div class="summary-icon"><i class="fas fa-award"></i></div>
        <div class="h1 fw-900 mb-1"><?= number_format($active_students) ?></div>
        <div class="small fw-700 opacity-80">Active Learners</div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="glass-card summary-card orange">
        <div class="summary-icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <div class="h1 fw-900 mb-1"><?= $pending_payments ?></div>
        <div class="small fw-700 opacity-80">Payment Alerts</div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="glass-card summary-card purple">
        <div class="summary-icon"><i class="fas fa-chart-line"></i></div>
        <div class="h1 fw-900 mb-1">Rs. <?= number_format($monthly_revenue/1000, 0) ?>k</div>
        <div class="small fw-700 opacity-80">Monthly Revenue</div>
      </div>
    </div>
  </div>

  <div class="row g-4 mb-60">
    <!-- SECTION: MAIN TABLE (RECENT ACTIVITY) -->
    <div class="col-lg-8">
      <div class="glass-card">
        <div class="section-title"><i class="fas fa-history text-primary"></i> Recent Enrollment Activity</div>
        <div class="table-responsive">
          <table class="table-modern">
            <thead>
              <tr class="text-muted small fw-800 uppercase">
                <th class="ps-4">Student</th>
                <th>Course Batch</th>
                <th>Status</th>
                <th>Documents</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($recent_students as $s): ?>
              <tr>
                <td class="ps-4">
                  <div class="d-flex align-items-center gap-3">
                    <div style="width:36px; height:36px; background:#eef2ff; color:#2575fc; border-radius:12px; display:flex; align-items:center; justify-content:center; font-weight:900;">
                        <?= strtoupper(substr($s['name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-800 text-main" style="font-size:14px;"><?= htmlspecialchars($s['name']) ?></div>
                        <div class="text-muted" style="font-size:11px;">Joined 2h ago</div>
                    </div>
                  </div>
                </td>
                <td><div class="fw-700 text-muted small"><?= $s['batch'] ?></div></td>
                <td><span class="badge-glass <?= $s['status']==='Completed'?'bg-success-glass':'bg-warning-glass' ?>"><?= $s['status'] ?></span></td>
                <td><div class="fw-700 small"><?= $s['docs'] ?></div></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- SECTION: UPCOMING SCHEDULE -->
    <div class="col-lg-4">
      <div class="glass-card">
        <div class="section-title"><i class="fas fa-calendar-check text-success"></i> Today's Schedule</div>
        <div class="schedule-list">
          <?php foreach($upcoming_schedule as $item): ?>
          <div class="schedule-item">
            <div class="schedule-time"><?= $item['time'] ?></div>
            <div class="schedule-dot"></div>
            <div class="flex-grow-1">
              <div class="fw-800 text-main small mb-1"><?= htmlspecialchars($item['title']) ?></div>
              <div class="text-muted" style="font-size:11px;">
                <i class="fas fa-user-tie me-1"></i> <?= $item['lecturer'] ?> &nbsp; 
                <i class="fas fa-map-marker-alt ms-2 me-1"></i> <?= $item['room'] ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <button class="btn btn-light w-100 mt-4 rounded-pill fw-800 small py-2 border">View Full Calendar</button>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- SECTION: PRIORITY FOLLOW-UPS -->
    <div class="col-lg-4">
      <div class="glass-card">
        <div class="section-title"><i class="fas fa-phone-volume text-primary"></i> Priority Follow-ups</div>
        <div class="d-flex flex-column gap-3">
          <?php foreach([['name'=>'John Wick', 'phone'=>'0771234567'], ['name'=>'Peter Parker', 'phone'=>'0719876543']] as $f): ?>
          <div class="d-flex justify-content-between align-items-center p-3 rounded-4 bg-white border">
             <div class="d-flex align-items-center gap-3">
                <div class="bg-primary-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px;"><i class="fas fa-user"></i></div>
                <div>
                   <div class="fw-800 text-main small"><?= $f['name'] ?></div>
                   <div class="text-muted" style="font-size:10px;">Due Today</div>
                </div>
             </div>
             <a href="tel:<?= $f['phone'] ?>" class="btn btn-primary rounded-pill px-3 py-1 fw-800 small" style="font-size:10px;">CALL</a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- SECTION: MISSING DOCUMENTS TRACKER -->
    <div class="col-lg-4">
      <div class="glass-card">
        <div class="section-title"><i class="fas fa-file-circle-exclamation text-danger"></i> Missing Documents</div>
        <div class="d-flex flex-column gap-3">
           <?php foreach([['name'=>'Bob Marley', 'docs'=>'NIC, Photo'], ['name'=>'David Gandy', 'docs'=>'Results']] as $d): ?>
           <div class="p-3 rounded-4 bg-danger-subtle border-0">
              <div class="fw-800 text-main small mb-1"><?= $d['name'] ?></div>
              <div class="text-danger fw-700" style="font-size:11px;"><i class="fas fa-times-circle me-1"></i> Missing: <?= $d['docs'] ?></div>
           </div>
           <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- SECTION: LATEST ANNOUNCEMENTS -->
    <div class="col-lg-4">
      <div class="glass-card">
         <div class="section-title"><i class="fas fa-bullhorn text-warning"></i> Latest Notices</div>
         <div class="d-flex flex-column gap-3">
            <?php foreach([['title'=>'Holiday Notice', 'date'=>'Apr 28'], ['title'=>'Exam Schedule Out', 'date'=>'Apr 25']] as $n): ?>
            <div class="d-flex justify-content-between align-items-center p-3 rounded-4 bg-white border">
               <div>
                  <div class="fw-800 text-main small"><?= $n['title'] ?></div>
                  <div class="text-muted" style="font-size:10px;">Posted by Admin</div>
               </div>
               <span class="badge bg-light text-dark rounded-pill px-3 fw-800" style="font-size:10px;"><?= $n['date'] ?></span>
            </div>
            <?php endforeach; ?>
         </div>
         <a href="<?= BASE_URL ?>/frontend/admin/notices.php" class="btn btn-light w-100 mt-3 rounded-pill fw-800 small border">View All</a>
      </div>
    </div>
  </div>

</div>

<?php require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
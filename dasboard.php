<?php
session_start();
require_once __DIR__ . '/config/database.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';
$user_role = strtolower($_SESSION['role'] ?? 'student');
if($user_role == 'admin'){
    $total = $pdo->query("SELECT COUNT(*) FROM ccms_complaints")->fetchColumn();
    $pending = $pdo->query("SELECT COUNT(*) FROM ccms_complaints WHERE status='Pending'")->fetchColumn();
    $inprogress = $pdo->query("SELECT COUNT(*) FROM ccms_complaints WHERE status='In Progress'")->fetchColumn();
    $resolved = $pdo->query("SELECT COUNT(*) FROM ccms_complaints WHERE status='Resolved'")->fetchColumn();
    $all = $pdo->query("SELECT * FROM ccms_complaints ORDER BY created_at DESC")->fetchAll();
} else {
    $s = $pdo->prepare("SELECT COUNT(*) FROM ccms_complaints WHERE user_id=?"); $s->execute([$user_id]); $total=$s->fetchColumn();
    $s = $pdo->prepare("SELECT COUNT(*) FROM ccms_complaints WHERE user_id=? AND status='Pending'"); $s->execute([$user_id]); $pending=$s->fetchColumn();
    $s = $pdo->prepare("SELECT COUNT(*) FROM ccms_complaints WHERE user_id=? AND status='In Progress'"); $s->execute([$user_id]); $inprogress=$s->fetchColumn();
    $s = $pdo->prepare("SELECT COUNT(*) FROM ccms_complaints WHERE user_id=? AND status='Resolved'"); $s->execute([$user_id]); $resolved=$s->fetchColumn();
}
?>
<html><head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>.desc-row{display:none; background:#f8f9fa;} .desc-box{padding:15px;}</style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark"><div class="container"><span class="navbar-brand">CCMS - <?= strtoupper($user_role) ?></span><a href="logout.php" class="btn btn-sm btn-light">Logout</a></div></nav>
<div class="container mt-4">
<div class="row g-2"><div class="col-3"><div class="card p-2 text-center"><h4><?= $total ?></h4>Total</div></div><div class="col-3"><div class="card p-2 text-center"><h4><?= $pending ?></h4>Pending</div></div><div class="col-3"><div class="card p-2 text-center"><h4><?= $inprogress ?></h4>In Progress</div></div><div class="col-3"><div class="card p-2 text-center"><h4><?= $resolved ?></h4>Resolved</div></div></div>

<?php if($user_role=='admin'){ ?>
<h4 class="mt-4">All Complaints - Admin Only</h4>
<table class="table table-bordered mt-3"><tr><th>ID</th><th>Subject</th><th>Status</th><th>View</th></tr>
<?php foreach($all as $r){ ?>
<tr>
<td><?= $r['id'] ?></td>
<td><?= $r['subject'] ?></td>
<td><span class="badge bg-warning text-dark"><?= $r['status'] ?></span></td>
<td><button onclick="toggleDesc(<?= $r['id'] ?>)" class="btn btn-sm btn-primary">View</button></td>
</tr>
<tr id="d-<?= $r['id'] ?>" class="desc-row"><td colspan="4"><div class="desc-box">
<b>ID #<?= $r['id'] ?> - <?= $r['subject'] ?></b><br>
<b>Category:</b> <?= $r['category'] ?? 'N/A' ?> | <b>Priority:</b> <?= $r['priority'] ?? 'N/A' ?><br><br>
<b>Full Description:</b><br><?= nl2br(htmlspecialchars($r['description'] ?? '')) ?><br><br>
<button onclick="toggleDesc(<?= $r['id'] ?>)" class="btn btn-sm btn-secondary">Close</button>
</div></td></tr>
<?php } ?></table>
<?php } else { ?>
<h4 class="mt-4">Submit Complaint - Student Only</h4>
<form method="POST" action="submit_complaint.php" class="card p-3">
  <div class="row">
    <div class="col-6 mb-2"><label>Category</label><select name="category" class="form-control" required><option value="">Select</option><option>Hostel</option><option>Food</option><option>Transport</option><option>Academics</option><option>Fees</option><option>Others</option></select></div>
    <div class="col-6 mb-2"><label>Priority</label><select name="priority" class="form-control"><option>Medium</option><option>High</option><option>Low</option></select></div>
  </div>
  <label>Subject</label><input name="subject" class="form-control mb-2" required>
  <label>Description</label><textarea name="description" class="form-control mb-2" rows="4" required></textarea>
  <button class="btn btn-primary w-100">Submit</button>
</form>
<h5 class="mt-4">My Complaints</h5>
<table class="table table-bordered"><tr><th>ID</th><th>Subject</th><th>Status</th></tr>
<?php $my = $pdo->prepare("SELECT * FROM ccms_complaints WHERE user_id=? ORDER BY created_at DESC"); $my->execute([$user_id]); foreach($my->fetchAll() as $m){ ?>
<tr><td><?= $m['id'] ?></td><td><?= $m['subject'] ?></td><td><?= $m['status'] ?></td></tr><?php } ?></table>
<?php } ?>
</div>
<script>
function toggleDesc(id){
  var el = document.getElementById('d-'+id);
  el.style.display = el.style.display === 'table-row' ? 'none' : 'table-row';
}
</script>
</body></html>
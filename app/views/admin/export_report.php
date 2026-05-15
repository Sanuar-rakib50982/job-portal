<?php
require_once "../../helpers/auth.php";
requireRole('admin');

require_once "../../config/database.php";
require_once "../../controllers/AdminController.php";

$admin = new AdminController($conn);
$summary = $admin->getReportSummary();

$filename = "monthly_platform_report_" . date("Y_m_d") . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen("php://output", "w");

fputcsv($output, ["Job Portal Monthly Platform Summary"]);
fputcsv($output, ["Generated At", date("Y-m-d H:i:s")]);
fputcsv($output, []);

fputcsv($output, ["Metric", "Value"]);
fputcsv($output, ["Total Users", $summary['total_users']]);
fputcsv($output, ["Total Jobs", $summary['total_jobs']]);
fputcsv($output, ["Active Jobs", $summary['active_jobs']]);
fputcsv($output, ["Total Applications", $summary['total_applications']]);
fputcsv($output, ["Total Complaints", $summary['total_complaints']]);
fputcsv($output, ["Resolved Complaints", $summary['resolved_complaints']]);
fputcsv($output, ["Pending Verifications", $summary['pending_verifications']]);

fclose($output);
exit;
<?php

$file = __DIR__ . '/../login/dashboard.php';
$content = file_get_contents($file);

// 1. Add array defaults
$defaults = <<<'CODE'
$memberStatusDist = [];
$memberTrend      = [];
$recentMembers    = [];
CODE;
$content = preg_replace('/(\$recentTasks\s*=\s*\[\];)/', "$1\n$defaults", $content);

// 2. Add KPI queries
$kpis = <<<'CODE'
// Members Stats
    $stmtFetchMemberTotal = $db->prepare("SELECT COUNT(*) AS total FROM members");
    $stmtFetchMemberTotal->execute();
    $memberTotalCount = $stmtFetchMemberTotal->get_result()->fetch_array(MYSQLI_ASSOC);
    
    $stmtFetchActiveMemberReal = $db->prepare("SELECT COUNT(*) AS total FROM members WHERE status = '1'");
    $stmtFetchActiveMemberReal->execute();
    $activeMemberRealCount = $stmtFetchActiveMemberReal->get_result()->fetch_array(MYSQLI_ASSOC);

    $stmtFetchInactiveMember = $db->prepare("SELECT COUNT(*) AS total FROM members WHERE status != '1'");
    $stmtFetchInactiveMember->execute();
    $inactiveMemberCount = $stmtFetchInactiveMember->get_result()->fetch_array(MYSQLI_ASSOC);

    $stmtFetchNewMember = $db->prepare("
        SELECT COUNT(*) AS total 
        FROM members 
        WHERE STR_TO_DATE(created_date, '%Y-%m-%d %h:%i:%s %p') >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ");
    $stmtFetchNewMember->execute();
    $newMemberCount = $stmtFetchNewMember->get_result()->fetch_array(MYSQLI_ASSOC);

    // Employee Stats
CODE;
$content = str_replace('// Employee Stats', $kpis, $content);

// 3. Add Charts/Tables queries
$chartsQueries = <<<'CODE'
// Member Status Distribution
    $stmtMemberStatusDist = $db->prepare("
        SELECT
            CASE
                WHEN status = '1' THEN 'Active'
                ELSE 'Inactive'
            END AS member_status,
            COUNT(*) AS total
        FROM members
        GROUP BY
            CASE
                WHEN status = '1' THEN 'Active'
                ELSE 'Inactive'
            END
    ");
    $stmtMemberStatusDist->execute();
    $memberStatusDist = $stmtMemberStatusDist->get_result()->fetch_all(MYSQLI_ASSOC);

    // Member Registration Trend
    $stmtMemberTrend = $db->prepare("
        SELECT
            DATE_FORMAT(
                STR_TO_DATE(created_date, '%Y-%m-%d %h:%i:%s %p'),
                '%Y-%m'
            ) AS month,
            COUNT(*) AS total
        FROM members
        WHERE STR_TO_DATE(created_date, '%Y-%m-%d %h:%i:%s %p') IS NOT NULL
        GROUP BY
            DATE_FORMAT(
                STR_TO_DATE(created_date, '%Y-%m-%d %h:%i:%s %p'),
                '%Y-%m'
            )
        ORDER BY month ASC
        LIMIT 12
    ");
    $stmtMemberTrend->execute();
    $memberTrend = $stmtMemberTrend->get_result()->fetch_all(MYSQLI_ASSOC);

    // Recent Members
    $stmtRecentMembers = $db->prepare("
        SELECT member_id, name, firm_name, mobile, email_id, city_state, state_code, status, created_date
        FROM members
        ORDER BY member_id DESC
        LIMIT 10
    ");
    $stmtRecentMembers->execute();
    $recentMembers = $stmtRecentMembers->get_result()->fetch_all(MYSQLI_ASSOC);

    // Recent Tender Activity
CODE;
$content = str_replace('// Recent Tender Activity', $chartsQueries, $content);

// 4. Add Member KPIs UI
$memberKPIs = <<<'CODE'
<!-- ROW 1.5: Members Summary -->
            <?php if ($isAdmin || hasPermission('Dashboard Registered Members Count', $privileges, $roleData['role_name'])): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Members Overview</h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="row text-center">
                                <div class="col">
                                    <h3 class="mb-1 text-primary"><?php echo (int)($memberTotalCount['total'] ?? 0); ?></h3>
                                    <span class="text-muted">Total Members</span>
                                </div>
                                <div class="col">
                                    <h3 class="mb-1 text-success"><?php echo (int)($activeMemberRealCount['total'] ?? 0); ?></h3>
                                    <span class="text-muted">Active Members</span>
                                </div>
                                <div class="col">
                                    <h3 class="mb-1 text-danger"><?php echo (int)($inactiveMemberCount['total'] ?? 0); ?></h3>
                                    <span class="text-muted">Inactive Members</span>
                                </div>
                                <div class="col">
                                    <h3 class="mb-1 text-info"><?php echo (int)($newMemberCount['total'] ?? 0); ?></h3>
                                    <span class="text-muted">New This Month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ROW 2: Employee + Task Summary -->
CODE;
$content = str_replace('<!-- ROW 2: Employee + Task Summary -->', $memberKPIs, $content);

// 5. Add Member Charts UI
$memberCharts = <<<'CODE'
<!-- ROW 3.5: Member Charts -->
            <?php if ($isAdmin || hasPermission('Dashboard Registered Members Count', $privileges, $roleData['role_name'])): ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Member Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="memberStatusChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Member Registration Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="memberTrendChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- ROW 4 & 5: Tables -->
CODE;
$content = str_replace('<!-- ROW 4 & 5: Tables -->', $memberCharts, $content);

// 6. Add Recent Members Table
$recentMembersTable = <<<'CODE'
<!-- Recent Members -->
                <?php if ($isAdmin || hasPermission('Dashboard Registered Members Count', $privileges, $roleData['role_name'])): ?>
                <div class="col-md-12 mt-3">
                    <div class="card table-card">
                        <div class="card-header">
                            <h5>Recent Members</h5>
                        </div>
                        <div class="card-body px-0 py-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Member</th>
                                            <th>Firm</th>
                                            <th>Mobile</th>
                                            <th>State</th>
                                            <th>Status</th>
                                            <th>Registered</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentMembers as $mem): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($mem['name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($mem['firm_name'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($mem['mobile'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($mem['state_code'] ?? ''); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo ($mem['status'] == '1') ? 'success' : 'danger'; ?>">
                                                    <?php echo ($mem['status'] == '1') ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($mem['created_date'] ?? ''); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($recentMembers)): ?>
                                        <tr><td colspan="6" class="text-center text-muted">No recent members found</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </section>
CODE;
$content = preg_replace('/(\s*<\/div>\s*<\/div>\s*<\/section>)/', "\n" . $recentMembersTable, $content);

// 7. Add Chart JS
$chartJs = <<<'CODE'
});

            // Member Status Chart
            var memberStatusCtx = document.getElementById('memberStatusChart');
            if (memberStatusCtx) {
                var memberStatusLabels = <?php echo json_encode(array_column(is_array($memberStatusDist) ? $memberStatusDist : [], 'member_status')); ?>;
                var memberStatusData = <?php echo json_encode(array_column(is_array($memberStatusDist) ? $memberStatusDist : [], 'total')); ?>;
                new Chart(memberStatusCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: memberStatusLabels,
                        datasets: [{
                            data: memberStatusData,
                            backgroundColor: ['#26c975', '#ff5252']
                        }]
                    },
                    options: { maintainAspectRatio: false }
                });
            }

            // Member Trend Chart
            var memberTrendCtx = document.getElementById('memberTrendChart');
            if (memberTrendCtx) {
                var memberTrendLabels = <?php echo json_encode(array_column(is_array($memberTrend) ? $memberTrend : [], 'month')); ?>;
                var memberTrendData = <?php echo json_encode(array_column(is_array($memberTrend) ? $memberTrend : [], 'total')); ?>;
                new Chart(memberTrendCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: memberTrendLabels,
                        datasets: [{
                            label: 'New Members',
                            data: memberTrendData,
                            borderColor: '#ffba56',
                            fill: false,
                            tension: 0.1
                        }]
                    },
                    options: { maintainAspectRatio: false }
                });
            }
        });
CODE;
$content = preg_replace('/(options:\s*{\s*maintainAspectRatio:\s*false\s*}\s*}\);\s*\}\);)/', "$1", $content); 
// Need to do this properly
$content = str_replace("options: { maintainAspectRatio: false }\n            });\n        });", "options: { maintainAspectRatio: false }\n            });\n" . substr($chartJs, 6), $content);

file_put_contents($file, $content);
echo "Patched successfully!";
